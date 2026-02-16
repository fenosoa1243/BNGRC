<?php

namespace App\Controllers;

use App\Models\Achat;
use App\Models\Besoin;
use App\Models\Ville;
use App\Models\Config;
use App\Models\Don;
use Flight;

class AchatController {
    
    private $achatModel;
    private $besoinModel;
    private $villeModel;
    private $configModel;
    private $donModel;
    
    public function __construct() {
        $this->achatModel = new Achat();
        $this->besoinModel = new Besoin();
        $this->villeModel = new Ville();
        $this->configModel = new Config();
        $this->donModel = new Don();
    }
    
    /**
     * Liste des achats avec filtre par ville
     */
    public function liste() {
        $ville_filtre = Flight::request()->query->ville ?? null;
        
        if($ville_filtre) {
            $achats = $this->achatModel->getByVille($ville_filtre);
        } else {
            $achats = $this->achatModel->getAllWithDetails();
        }
        
        $villes = $this->villeModel->all('nom_ville', 'ASC');
        $frais_pourcentage = $this->configModel->getFraisAchatPourcentage();
        
        Flight::render('achat/liste', [
            'achats' => $achats,
            'villes' => $villes,
            'ville_filtre' => $ville_filtre,
            'frais_pourcentage' => $frais_pourcentage
        ]);
    }
    
    /**
     * Page des besoins restants pour faire des achats
     */
    public function besoinsRestants() {
        $db = Flight::db();
        
        // Récupérer les besoins restants (non satisfaits)
        $query = "SELECT b.id_besoin, b.id_ville, b.id_type, b.quantite,
                  v.nom_ville, v.region,
                  t.nom_type, t.categorie, t.unite, t.prix_unitaire,
                  COALESCE((
                      SELECT SUM(dist.quantite_distribuee)
                      FROM distribution dist
                      JOIN don d ON dist.id_don = d.id_don
                      WHERE dist.id_ville = b.id_ville AND d.id_type = b.id_type
                  ), 0) as quantite_distribuee,
                  COALESCE((
                      SELECT SUM(a.quantite)
                      FROM achat a
                      WHERE a.id_besoin = b.id_besoin AND a.statut = 'valide'
                  ), 0) as quantite_achetee,
                  (b.quantite * t.prix_unitaire) as valeur_totale
                  FROM besoin b
                  JOIN ville v ON b.id_ville = v.id_ville
                  JOIN type_besoin t ON b.id_type = t.id_type
                  WHERE t.categorie IN ('nature', 'materiau')
                  ORDER BY v.nom_ville, t.categorie, t.nom_type";
        
        $stmt = $db->query($query);
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Calculer les quantités restantes
        foreach($besoins as &$besoin) {
            $besoin['quantite_restante'] = $besoin['quantite'] - $besoin['quantite_distribuee'] - $besoin['quantite_achetee'];
            $besoin['valeur_restante'] = $besoin['quantite_restante'] * $besoin['prix_unitaire'];
        }
        
        // Filtrer uniquement les besoins avec quantité restante > 0
        $besoins = array_filter($besoins, function($b) {
            return $b['quantite_restante'] > 0;
        });
        
        // Calculer le montant d'argent disponible
        $query_argent = "SELECT COALESCE(SUM(d.quantite_restante), 0) as argent_disponible
                         FROM don d
                         JOIN type_besoin t ON d.id_type = t.id_type
                         WHERE t.categorie = 'argent' AND d.quantite_restante > 0";
        $stmt = $db->query($query_argent);
        $argent_disponible = $stmt->fetch(\PDO::FETCH_ASSOC)['argent_disponible'];
        
        $frais_pourcentage = $this->configModel->getFraisAchatPourcentage();
        
        Flight::render('achat/besoins_restants', [
            'besoins' => $besoins,
            'argent_disponible' => $argent_disponible,
            'frais_pourcentage' => $frais_pourcentage
        ]);
    }
    
    /**
     * Simuler un achat
     */
    public function simuler() {
        if(Flight::request()->method == 'POST') {
            $id_besoin = Flight::request()->data->id_besoin;
            $quantite = (float) Flight::request()->data->quantite;
            
            // Récupérer les informations du besoin
            $db = Flight::db();
            $query = "SELECT b.*, v.nom_ville, t.nom_type, t.prix_unitaire, t.categorie
                      FROM besoin b
                      JOIN ville v ON b.id_ville = v.id_ville
                      JOIN type_besoin t ON b.id_type = t.id_type
                      WHERE b.id_besoin = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id_besoin]);
            $besoin = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if(!$besoin) {
                Flight::json([
                    'success' => false,
                    'error' => 'Besoin introuvable'
                ]);
                return;
            }
            
            // Vérifier que c'est un besoin en nature ou matériau
            if($besoin['categorie'] == 'argent') {
                Flight::json([
                    'success' => false,
                    'error' => 'Impossible d\'acheter un besoin en argent'
                ]);
                return;
            }
            
            // Vérifier l'argent disponible
            $query_argent = "SELECT COALESCE(SUM(d.quantite_restante), 0) as argent_disponible
                            FROM don d
                            JOIN type_besoin t ON d.id_type = t.id_type
                            WHERE t.categorie = 'argent' AND d.quantite_restante > 0";
            $stmt = $db->query($query_argent);
            $argent_disponible = $stmt->fetch(\PDO::FETCH_ASSOC)['argent_disponible'];
            
            // Calculer le montant
            $frais_pourcentage = $this->configModel->getFraisAchatPourcentage();
            $montant_base = $quantite * $besoin['prix_unitaire'];
            $frais_achat = $montant_base * ($frais_pourcentage / 100);
            $montant_total = $montant_base + $frais_achat;
            
            if($montant_total > $argent_disponible) {
                Flight::json([
                    'success' => false,
                    'error' => 'Argent insuffisant pour cet achat'
                ]);
                return;
            }
            
            // Vérifier si l'achat existe déjà dans les dons restants
            $query_check = "SELECT COUNT(*) as count
                           FROM don d
                           JOIN type_besoin t ON d.id_type = t.id_type
                           WHERE d.id_type = ? AND d.quantite_restante >= ? AND t.categorie != 'argent'";
            $stmt = $db->prepare($query_check);
            $stmt->execute([$besoin['id_type'], $quantite]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if($result['count'] > 0) {
                Flight::json([
                    'success' => false,
                    'error' => 'Ce besoin existe déjà dans les dons disponibles. Veuillez utiliser la distribution automatique.'
                ]);
                return;
            }
            
            // Créer l'achat en simulation
            $data = [
                'id_besoin' => $id_besoin,
                'id_ville' => $besoin['id_ville'],
                'id_type' => $besoin['id_type'],
                'quantite' => $quantite,
                'montant_base' => $montant_base,
                'frais_achat' => $frais_achat,
                'montant_total' => $montant_total,
                'statut' => 'simulation'
            ];
            
            $this->achatModel->create($data);
            
            Flight::json([
                'success' => true,
                'message' => 'Achat simulé avec succès',
                'achat' => $data
            ]);
        }
    }
    
    /**
     * Valider les achats simulés
     */
    public function valider() {
        if(Flight::request()->method == 'POST') {
            $db = Flight::db();
            
            try {
                $db->beginTransaction();
                
                // Récupérer tous les achats en simulation
                $query = "SELECT * FROM achat WHERE statut = 'simulation'";
                $stmt = $db->query($query);
                $achats_simulation = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                if(empty($achats_simulation)) {
                    Flight::json([
                        'success' => false,
                        'error' => 'Aucun achat en simulation'
                    ]);
                    return;
                }
                
                // Vérifier l'argent disponible
                $query_argent = "SELECT COALESCE(SUM(d.quantite_restante), 0) as argent_disponible
                                FROM don d
                                JOIN type_besoin t ON d.id_type = t.id_type
                                WHERE t.categorie = 'argent' AND d.quantite_restante > 0";
                $stmt = $db->query($query_argent);
                $argent_disponible = $stmt->fetch(\PDO::FETCH_ASSOC)['argent_disponible'];
                
                $montant_total_requis = array_sum(array_column($achats_simulation, 'montant_total'));
                
                if($montant_total_requis > $argent_disponible) {
                    $db->rollBack();
                    Flight::json([
                        'success' => false,
                        'error' => 'Argent insuffisant pour valider tous les achats'
                    ]);
                    return;
                }
                
                // Pour chaque achat, créer un don correspondant et déduire l'argent
                foreach($achats_simulation as $achat) {
                    // Créer un don pour le besoin acheté
                    $query_don = "INSERT INTO don (id_type, quantite, quantite_restante, donateur, statut)
                                 VALUES (?, ?, ?, 'Achat avec argent', 'disponible')";
                    $stmt = $db->prepare($query_don);
                    $stmt->execute([
                        $achat['id_type'],
                        $achat['quantite'],
                        $achat['quantite']
                    ]);
                    
                    // Déduire l'argent des dons en argent (FIFO)
                    $montant_a_deduire = $achat['montant_total'];
                    
                    $query_dons_argent = "SELECT d.id_don, d.quantite_restante
                                         FROM don d
                                         JOIN type_besoin t ON d.id_type = t.id_type
                                         WHERE t.categorie = 'argent' AND d.quantite_restante > 0
                                         ORDER BY d.date_saisie ASC";
                    $stmt = $db->query($query_dons_argent);
                    $dons_argent = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    
                    foreach($dons_argent as $don_argent) {
                        if($montant_a_deduire <= 0) break;
                        
                        $deduction = min($montant_a_deduire, $don_argent['quantite_restante']);
                        
                        $query_update = "UPDATE don 
                                        SET quantite_restante = quantite_restante - ?,
                                            statut = CASE 
                                                WHEN (quantite_restante - ?) = 0 THEN 'distribue'
                                                WHEN (quantite_restante - ?) < quantite THEN 'partiel'
                                                ELSE 'disponible'
                                            END
                                        WHERE id_don = ?";
                        $stmt = $db->prepare($query_update);
                        $stmt->execute([$deduction, $deduction, $deduction, $don_argent['id_don']]);
                        
                        $montant_a_deduire -= $deduction;
                    }
                }
                
                // Valider les achats
                $ids_achats = array_column($achats_simulation, 'id_achat');
                $this->achatModel->validerSimulation($ids_achats);
                
                $db->commit();
                
                Flight::json([
                    'success' => true,
                    'message' => count($achats_simulation) . ' achat(s) validé(s) avec succès'
                ]);
                
            } catch(\Exception $e) {
                $db->rollBack();
                Flight::json([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * Annuler la simulation
     */
    public function annulerSimulation() {
        $nb_supprimes = $this->achatModel->supprimerSimulations();
        
        Flight::json([
            'success' => true,
            'message' => $nb_supprimes . ' achat(s) en simulation supprimé(s)'
        ]);
    }
}
