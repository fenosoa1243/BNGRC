<?php

namespace App\Controllers;

use App\Models\Besoin;
use App\Models\Don;
use Flight;

class AchatController {
    
    /**
     * Page d'achat - Liste des besoins restants filtrables par ville
     */
    public function index() {
        $db = Flight::db();
        $ville_id = Flight::request()->query->ville ?? null;
        
        // Récupérer le frais d'achat configuré
        $frais_achat = $this->getFraisAchat();
        
        // Récupérer les besoins restants
        $query = "SELECT 
                    v.id_ville,
                    v.nom_ville,
                    v.region,
                    tb.id_type,
                    tb.nom_type,
                    tb.categorie,
                    tb.unite,
                    tb.prix_unitaire,
                    COALESCE(SUM(b.quantite), 0) as besoin_total,
                    COALESCE((SELECT SUM(dist.quantite_distribuee) 
                              FROM distribution dist 
                              JOIN don d ON dist.id_don = d.id_don 
                              WHERE dist.id_ville = v.id_ville 
                              AND d.id_type = tb.id_type), 0) as deja_recu
                FROM ville v
                CROSS JOIN type_besoin tb
                LEFT JOIN besoin b ON v.id_ville = b.id_ville AND tb.id_type = b.id_type
                WHERE 1=1";
        
        if($ville_id) {
            $query .= " AND v.id_ville = :ville_id";
        }
        
        $query .= " GROUP BY v.id_ville, v.nom_ville, v.region, tb.id_type, tb.nom_type, tb.categorie, tb.unite, tb.prix_unitaire
                    HAVING (besoin_total - deja_recu) > 0
                    ORDER BY v.nom_ville, tb.categorie, tb.nom_type";
        
        $stmt = $db->prepare($query);
        if($ville_id) {
            $stmt->execute(['ville_id' => $ville_id]);
        } else {
            $stmt->execute();
        }
        $besoins_restants = $stmt->fetchAll();
        
        // Récupérer liste des villes pour le filtre
        $villes = $db->query("SELECT * FROM ville ORDER BY nom_ville")->fetchAll();
        
        Flight::render('achat/index', [
            'besoins_restants' => $besoins_restants,
            'villes' => $villes,
            'ville_selectionnee' => $ville_id,
            'frais_achat' => $frais_achat
        ]);
    }
    
    /**
     * Traiter l'achat de dons
     */
    public function acheter() {
        if(Flight::request()->method != 'POST') {
            Flight::redirect('/achats');
            return;
        }
        
        $db = Flight::db();
        $achats = json_decode(Flight::request()->data->achats_json ?? '[]', true);
        $frais_achat = $this->getFraisAchat();
        
        if(empty($achats)) {
            Flight::json(['success' => false, 'message' => 'Aucun achat sélectionné']);
            return;
        }
        
        try {
            $db->beginTransaction();
            
            $total_achete = 0;
            $dons_crees = [];
            
            foreach($achats as $achat) {
                $quantite = floatval($achat['quantite']);
                if($quantite <= 0) continue;
                
                // Vérifier que le besoin existe encore
                $query = "SELECT 
                            tb.id_type, tb.prix_unitaire, tb.nom_type, v.nom_ville,
                            COALESCE(SUM(b.quantite), 0) as besoin_total,
                            COALESCE((SELECT SUM(dist.quantite_distribuee) 
                                      FROM distribution dist 
                                      JOIN don d ON dist.id_don = d.id_don 
                                      WHERE dist.id_ville = :ville_id 
                                      AND d.id_type = :type_id), 0) as deja_recu
                          FROM type_besoin tb
                          CROSS JOIN ville v
                          LEFT JOIN besoin b ON b.id_type = tb.id_type AND b.id_ville = v.id_ville
                          WHERE tb.id_type = :type_id2 AND v.id_ville = :ville_id2
                          GROUP BY tb.id_type, tb.prix_unitaire, tb.nom_type, v.nom_ville";
                
                $stmt = $db->prepare($query);
                $stmt->execute([
                    'ville_id' => $achat['id_ville'],
                    'type_id' => $achat['id_type'],
                    'ville_id2' => $achat['id_ville'],
                    'type_id2' => $achat['id_type']
                ]);
                $besoin = $stmt->fetch();
                
                if(!$besoin) continue;
                
                $besoin_restant = $besoin['besoin_total'] - $besoin['deja_recu'];
                
                // Vérifier que la quantité n'excède pas le besoin
                if($quantite > $besoin_restant) {
                    throw new \Exception("La quantité pour {$besoin['nom_type']} à {$besoin['nom_ville']} dépasse le besoin restant ({$besoin_restant})");
                }
                
                $prix_unitaire = floatval($besoin['prix_unitaire']);
                $montant_base = $quantite * $prix_unitaire;
                $montant_avec_frais = $montant_base * (1 + $frais_achat / 100);
                
                // Créer le don
                $stmt = $db->prepare("INSERT INTO don (id_type, quantite, quantite_restante, donateur) 
                                     VALUES (?, ?, ?, 'Achat BNGRC')");
                $stmt->execute([$achat['id_type'], $quantite, $quantite]);
                
                $total_achete += $montant_avec_frais;
                $dons_crees[] = [
                    'type' => $besoin['nom_type'],
                    'ville' => $besoin['nom_ville'],
                    'quantite' => $quantite,
                    'montant' => $montant_avec_frais
                ];
            }
            
            $db->commit();
            
            Flight::json([
                'success' => true,
                'message' => count($dons_crees) . ' don(s) créé(s)',
                'total' => $total_achete,
                'dons' => $dons_crees
            ]);
            
        } catch(\Exception $e) {
            $db->rollBack();
            Flight::json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Récupérer le taux de frais
     */
    private function getFraisAchat() {
        $db = Flight::db();
        try {
            $stmt = $db->query("SELECT valeur FROM configuration WHERE cle = 'frais_achat' LIMIT 1");
            $result = $stmt->fetch();
            return $result ? floatval($result['valeur']) : 10.0;
        } catch(\Exception $e) {
            return 10.0;
        }
    }
    
    /**
     * Configurer le taux de frais
     */
    public function configurerFrais() {
        $frais = floatval(Flight::request()->data->frais ?? 10);
        $db = Flight::db();
        
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS configuration (
                id INT PRIMARY KEY AUTO_INCREMENT,
                cle VARCHAR(50) UNIQUE NOT NULL,
                valeur TEXT NOT NULL
            )");
            
            $stmt = $db->prepare("INSERT INTO configuration (cle, valeur) VALUES ('frais_achat', ?) 
                                 ON DUPLICATE KEY UPDATE valeur = ?");
            $stmt->execute([$frais, $frais]);
            
            Flight::json(['success' => true, 'frais' => $frais]);
        } catch(\Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
