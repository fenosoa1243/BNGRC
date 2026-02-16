<?php

namespace App\Controllers;

use Flight;

class RecapController {
    
    /**
     * Page de récapitulation
     */
    public function index() {
        Flight::render('recap/index', []);
    }
    
    /**
     * API pour récupérer les données de récapitulation (Ajax)
     */
    public function getData() {
        $db = Flight::db();
        
        try {
            // Besoins totaux en montant
            $query_besoins = "SELECT COALESCE(SUM(b.quantite * t.prix_unitaire), 0) as total
                             FROM besoin b
                             JOIN type_besoin t ON b.id_type = t.id_type";
            $stmt = $db->query($query_besoins);
            $besoins_total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Besoins satisfaits par les distributions
            $query_distribue = "SELECT COALESCE(SUM(dist.quantite_distribuee * t.prix_unitaire), 0) as total
                               FROM distribution dist
                               JOIN don d ON dist.id_don = d.id_don
                               JOIN type_besoin t ON d.id_type = t.id_type";
            $stmt = $db->query($query_distribue);
            $montant_distribue = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Besoins satisfaits par les achats validés
            $query_achats = "SELECT COALESCE(SUM(a.quantite * t.prix_unitaire), 0) as total
                            FROM achat a
                            JOIN type_besoin t ON a.id_type = t.id_type
                            WHERE a.statut = 'valide'";
            $stmt = $db->query($query_achats);
            $montant_achete = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            $besoins_satisfaits = $montant_distribue + $montant_achete;
            $besoins_restants = $besoins_total - $besoins_satisfaits;
            
            // Pourcentage de satisfaction
            $pourcentage_satisfaction = ($besoins_total > 0) ? ($besoins_satisfaits / $besoins_total) * 100 : 0;
            
            // Détails par catégorie
            $query_details = "SELECT 
                t.categorie,
                COALESCE(SUM(b.quantite * t.prix_unitaire), 0) as besoin_total,
                COALESCE((
                    SELECT SUM(dist.quantite_distribuee * t2.prix_unitaire)
                    FROM distribution dist
                    JOIN don d ON dist.id_don = d.id_don
                    JOIN type_besoin t2 ON d.id_type = t2.id_type
                    WHERE t2.categorie = t.categorie
                ), 0) as montant_distribue,
                COALESCE((
                    SELECT SUM(a.quantite * t3.prix_unitaire)
                    FROM achat a
                    JOIN type_besoin t3 ON a.id_type = t3.id_type
                    WHERE a.statut = 'valide' AND t3.categorie = t.categorie
                ), 0) as montant_achete
                FROM type_besoin t
                LEFT JOIN besoin b ON t.id_type = b.id_type
                GROUP BY t.categorie
                ORDER BY t.categorie";
            $stmt = $db->query($query_details);
            $details_categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach($details_categories as &$cat) {
                $cat['montant_satisfait'] = $cat['montant_distribue'] + $cat['montant_achete'];
                $cat['montant_restant'] = $cat['besoin_total'] - $cat['montant_satisfait'];
                $cat['pourcentage'] = ($cat['besoin_total'] > 0) ? ($cat['montant_satisfait'] / $cat['besoin_total']) * 100 : 0;
            }
            
            // Détails par ville
            $query_villes = "SELECT 
                v.id_ville,
                v.nom_ville,
                v.region,
                COALESCE(SUM(b.quantite * t.prix_unitaire), 0) as besoin_total,
                COALESCE((
                    SELECT SUM(dist.quantite_distribuee * tt.prix_unitaire)
                    FROM distribution dist
                    JOIN don dd ON dist.id_don = dd.id_don
                    JOIN type_besoin tt ON dd.id_type = tt.id_type
                    WHERE dist.id_ville = v.id_ville
                ), 0) as montant_distribue,
                COALESCE((
                    SELECT SUM(a.quantite * tt.prix_unitaire)
                    FROM achat a
                    JOIN type_besoin tt ON a.id_type = tt.id_type
                    WHERE a.id_ville = v.id_ville AND a.statut = 'valide'
                ), 0) as montant_achete
                FROM ville v
                LEFT JOIN besoin b ON v.id_ville = b.id_ville
                LEFT JOIN type_besoin t ON b.id_type = t.id_type
                GROUP BY v.id_ville, v.nom_ville, v.region
                HAVING besoin_total > 0
                ORDER BY v.nom_ville";
            $stmt = $db->query($query_villes);
            $details_villes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach($details_villes as &$ville) {
                $ville['montant_satisfait'] = $ville['montant_distribue'] + $ville['montant_achete'];
                $ville['montant_restant'] = $ville['besoin_total'] - $ville['montant_satisfait'];
                $ville['pourcentage'] = ($ville['besoin_total'] > 0) ? ($ville['montant_satisfait'] / $ville['besoin_total']) * 100 : 0;
            }
            
            Flight::json([
                'success' => true,
                'data' => [
                    'besoins_total' => $besoins_total,
                    'besoins_satisfaits' => $besoins_satisfaits,
                    'montant_distribue' => $montant_distribue,
                    'montant_achete' => $montant_achete,
                    'besoins_restants' => $besoins_restants,
                    'pourcentage_satisfaction' => $pourcentage_satisfaction,
                    'details_categories' => $details_categories,
                    'details_villes' => $details_villes
                ]
            ]);
            
        } catch(\Exception $e) {
            Flight::json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
