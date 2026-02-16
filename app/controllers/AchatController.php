<?php

namespace App\Controllers;

use App\Models\Achat;
use App\Models\Besoin;
use App\Models\Ville;
use App\Models\TypeBesoin;
use App\Models\Configuration;
use Flight;

class AchatController {
    
    private $achatModel;
    private $besoinModel;
    private $villeModel;
    private $typeBesoinModel;
    private $configModel;
    
    public function __construct() {
        $this->achatModel = new Achat();
        $this->besoinModel = new Besoin();
        $this->villeModel = new Ville();
        $this->typeBesoinModel = new TypeBesoin();
        $this->configModel = new Configuration();
    }
    
    /**
     * Liste des achats avec filtre par ville
     */
    public function liste() {
        $id_ville = Flight::request()->query->id_ville ?? null;
        
        if ($id_ville) {
            $achats = $this->achatModel->getByVille($id_ville);
        } else {
            $achats = $this->achatModel->getAllWithDetails();
        }
        
        $villes = $this->villeModel->all('nom_ville', 'ASC');
        
        Flight::render('achat/liste', [
            'achats' => $achats,
            'villes' => $villes,
            'id_ville_filtre' => $id_ville
        ]);
    }
    
    /**
     * Formulaire d'achat basé sur les besoins restants
     */
    public function form() {
        // Récupérer les besoins restants
        $sql = "SELECT b.id_besoin, b.id_ville, b.id_type, v.nom_ville, v.region,
                t.nom_type, t.categorie, t.unite, t.prix_unitaire,
                b.quantite as besoin_total,
                COALESCE(SUM(dist.quantite_distribuee), 0) as quantite_distribuee,
                (b.quantite - COALESCE(SUM(dist.quantite_distribuee), 0)) as quantite_restante,
                ((b.quantite - COALESCE(SUM(dist.quantite_distribuee), 0)) * t.prix_unitaire) as valeur_restante
                FROM besoin b
                JOIN ville v ON b.id_ville = v.id_ville
                JOIN type_besoin t ON b.id_type = t.id_type
                LEFT JOIN distribution dist ON b.id_ville = dist.id_ville 
                    AND EXISTS (SELECT 1 FROM don d WHERE d.id_don = dist.id_don AND d.id_type = b.id_type)
                WHERE t.categorie IN ('nature', 'materiau')
                GROUP BY b.id_besoin, b.id_ville, b.id_type, v.nom_ville, v.region, 
                         t.nom_type, t.categorie, t.unite, t.prix_unitaire, b.quantite
                HAVING quantite_restante > 0
                ORDER BY v.nom_ville, t.categorie, t.nom_type";
        
        $besoins_restants = $this->achatModel->query($sql);
        
        // Récupérer le pourcentage de frais d'achat depuis la configuration
        // Retourne 10% par défaut si la configuration n'existe pas encore en base
        $frais_pourcentage = $this->configModel->getValeur('frais_achat_pourcentage', 10);
        
        // Récupérer les dons d'argent disponibles
        $sql_argent = "SELECT SUM(quantite_restante) as total_argent
                       FROM don d
                       JOIN type_besoin t ON d.id_type = t.id_type
                       WHERE t.categorie = 'argent' AND d.quantite_restante > 0";
        $result = $this->achatModel->query($sql_argent);
        $argent_disponible = $result[0]['total_argent'] ?? 0;
        
        Flight::render('achat/form', [
            'besoins_restants' => $besoins_restants,
            'frais_pourcentage' => $frais_pourcentage,
            'argent_disponible' => $argent_disponible
        ]);
    }
    
    /**
     * Enregistrer un achat
     */
    public function enregistrer() {
        if(Flight::request()->method == 'POST') {
            $id_ville = Flight::request()->data->id_ville;
            $id_type = Flight::request()->data->id_type;
            $quantite = Flight::request()->data->quantite;
            $prix_unitaire = Flight::request()->data->prix_unitaire;
            $mode = Flight::request()->data->mode ?? 'simule'; // 'simule' ou 'valide'
            
            // Récupérer le pourcentage de frais d'achat depuis la configuration
            // Retourne 10% par défaut si la configuration n'existe pas encore en base
            $frais_pourcentage = $this->configModel->getValeur('frais_achat_pourcentage', 10);
            
            // Calculer les montants
            $montant_base = $quantite * $prix_unitaire;
            $montant_total = $this->achatModel->calculerMontantTotal($montant_base, $frais_pourcentage);
            
            // Vérifier la disponibilité des dons d'argent
            $sql_argent = "SELECT SUM(quantite_restante) as total_argent
                           FROM don d
                           JOIN type_besoin t ON d.id_type = t.id_type
                           WHERE t.categorie = 'argent' AND d.quantite_restante > 0";
            $result = $this->achatModel->query($sql_argent);
            $argent_disponible = $result[0]['total_argent'] ?? 0;
            
            if ($argent_disponible < $montant_total) {
                Flight::redirect('/achats/nouveau?error=insufficient_funds&required=' . $montant_total . '&available=' . $argent_disponible);
                return;
            }
            
            // Vérifier si l'achat existe dans les dons restants (pour nature/materiau)
            if ($this->achatModel->verifierDisponibiliteDon($id_type, $quantite)) {
                Flight::redirect('/achats/nouveau?error=already_available&id_type=' . $id_type);
                return;
            }
            
            // Créer l'achat
            $data = [
                'id_ville' => $id_ville,
                'id_type' => $id_type,
                'quantite' => $quantite,
                'prix_unitaire' => $prix_unitaire,
                'frais_achat' => $frais_pourcentage,
                'montant_total' => $montant_total,
                'statut' => $mode
            ];
            
            $id_achat = $this->achatModel->create($data);
            
            // Si mode valide, créer un don correspondant et le distribuer
            if ($mode == 'valide') {
                $this->validerAchat($id_achat);
            }
            
            Flight::redirect('/achats?success=create&mode=' . $mode);
        }
    }
    
    /**
     * Simuler les achats
     */
    public function simuler() {
        // Récupérer les achats simulés
        $achats_simules = $this->achatModel->where('statut', 'simule', 'date_achat', 'DESC');
        
        // Calculer les totaux
        $total_montant = 0;
        $total_quantite = [];
        
        foreach ($achats_simules as $achat) {
            $total_montant += $achat['montant_total'];
            
            $type_key = $achat['id_type'];
            if (!isset($total_quantite[$type_key])) {
                $total_quantite[$type_key] = [
                    'nom_type' => '',
                    'unite' => '',
                    'quantite' => 0
                ];
            }
            $total_quantite[$type_key]['quantite'] += $achat['quantite'];
        }
        
        // Récupérer les dons d'argent disponibles
        $sql_argent = "SELECT SUM(quantite_restante) as total_argent
                       FROM don d
                       JOIN type_besoin t ON d.id_type = t.id_type
                       WHERE t.categorie = 'argent' AND d.quantite_restante > 0";
        $result = $this->achatModel->query($sql_argent);
        $argent_disponible = $result[0]['total_argent'] ?? 0;
        
        Flight::render('achat/simuler', [
            'achats_simules' => $achats_simules,
            'total_montant' => $total_montant,
            'argent_disponible' => $argent_disponible
        ]);
    }
    
    /**
     * Valider les achats simulés
     */
    public function valider() {
        try {
            // Récupérer les achats simulés
            $achats_simules = $this->achatModel->where('statut', 'simule');
            
            if (empty($achats_simules)) {
                Flight::redirect('/achats?error=no_simulated');
                return;
            }
            
            // Valider chaque achat
            foreach ($achats_simules as $achat) {
                $this->validerAchat($achat['id_achat']);
            }
            
            // Marquer les achats comme validés
            $this->achatModel->validerAchatsSimules();
            
            Flight::redirect('/achats?success=validation&nb=' . count($achats_simules));
        } catch(\Exception $e) {
            Flight::redirect('/achats/simuler?error=validation&message=' . urlencode($e->getMessage()));
        }
    }
    
    /**
     * Valider un achat individuel (créer don et distribution)
     */
    private function validerAchat($id_achat) {
        $achat = $this->achatModel->find($id_achat);
        
        if (!$achat) {
            throw new \Exception("Achat non trouvé");
        }
        
        // Commencer une transaction
        $this->achatModel->db->beginTransaction();
        
        try {
            // 1. Déduire le montant des dons d'argent disponibles
            $this->deduireDonsArgent($achat['montant_total']);
            
            // 2. Créer un don du type acheté
            $sql = "INSERT INTO don (id_type, quantite, quantite_restante, donateur, statut) 
                    VALUES (?, ?, ?, ?, 'disponible')";
            $stmt = $this->achatModel->db->prepare($sql);
            $stmt->execute([
                $achat['id_type'],
                $achat['quantite'],
                $achat['quantite'],
                'Achat via dons argent'
            ]);
            $id_don = $this->achatModel->db->lastInsertId();
            
            // 3. Créer une distribution vers la ville
            $sql = "INSERT INTO distribution (id_don, id_ville, quantite_distribuee) 
                    VALUES (?, ?, ?)";
            $stmt = $this->achatModel->db->prepare($sql);
            $stmt->execute([
                $id_don,
                $achat['id_ville'],
                $achat['quantite']
            ]);
            
            // 4. Mettre à jour le don comme distribué
            $sql = "UPDATE don SET quantite_restante = 0, statut = 'distribue' WHERE id_don = ?";
            $stmt = $this->achatModel->db->prepare($sql);
            $stmt->execute([$id_don]);
            
            $this->achatModel->db->commit();
            
        } catch(\Exception $e) {
            $this->achatModel->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Déduire le montant des dons d'argent disponibles
     */
    private function deduireDonsArgent($montant) {
        // Récupérer les dons d'argent disponibles (FIFO)
        $sql = "SELECT d.id_don, d.quantite_restante
                FROM don d
                JOIN type_besoin t ON d.id_type = t.id_type
                WHERE t.categorie = 'argent' AND d.quantite_restante > 0
                ORDER BY d.date_saisie ASC";
        $stmt = $this->achatModel->db->query($sql);
        $dons_argent = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $montant_restant = $montant;
        
        foreach ($dons_argent as $don) {
            if ($montant_restant <= 0) break;
            
            $a_deduire = min($don['quantite_restante'], $montant_restant);
            
            // Mettre à jour le don
            $sql = "UPDATE don 
                    SET quantite_restante = quantite_restante - ?,
                        statut = CASE 
                            WHEN (quantite_restante - ?) = 0 THEN 'distribue'
                            WHEN (quantite_restante - ?) < quantite THEN 'partiel'
                            ELSE 'disponible'
                        END
                    WHERE id_don = ?";
            $stmt = $this->achatModel->db->prepare($sql);
            $stmt->execute([$a_deduire, $a_deduire, $a_deduire, $don['id_don']]);
            
            $montant_restant -= $a_deduire;
        }
        
        if ($montant_restant > 0) {
            throw new \Exception("Fonds insuffisants pour cet achat");
        }
    }
    
    /**
     * Annuler la simulation
     */
    public function annulerSimulation() {
        $this->achatModel->supprimerAchatsSimules();
        Flight::redirect('/achats?success=cancelled');
    }
}
