<?php

namespace App\Controllers;

use Flight;

class DashboardController {
    
    public function index() {
        $db = Flight::db();
        
        // Récupérer les données du dashboard depuis la vue SQL
        $query = "SELECT * FROM v_dashboard 
                  WHERE besoin_total > 0 OR don_recu > 0
                  ORDER BY nom_ville, categorie, nom_type";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $dashboard_data = $stmt->fetchAll();
        
        // Organiser les données par ville
        $villes_data = [];
        foreach($dashboard_data as $row) {
            $id_ville = $row['id_ville'];
            if(!isset($villes_data[$id_ville])) {
                $villes_data[$id_ville] = [
                    'nom_ville' => $row['nom_ville'],
                    'region' => $row['region'],
                    'besoins' => []
                ];
            }
            $villes_data[$id_ville]['besoins'][] = $row;
        }
        
        // Récupérer les statistiques globales
        $stats = $this->getStatistiquesGlobales();
        
        // Rendre la vue
        Flight::render('dashboard/index', [
            'villes_data' => $villes_data,
            'stats' => $stats
        ]);
    }
    
    private function getStatistiquesGlobales() {
        $db = Flight::db();
        $stats = [];
        
        // Nombre de villes
        $query = "SELECT COUNT(*) as total FROM ville";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['nb_villes'] = $stmt->fetch()['total'];
        
        // Valeur totale des besoins
        $query = "SELECT SUM(b.quantite * t.prix_unitaire) as total 
                  FROM besoin b 
                  JOIN type_besoin t ON b.id_type = t.id_type";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['valeur_besoins'] = $stmt->fetch()['total'] ?? 0;
        
        // Valeur totale des dons
        $query = "SELECT SUM(d.quantite * t.prix_unitaire) as total 
                  FROM don d 
                  JOIN type_besoin t ON d.id_type = t.id_type";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['valeur_dons'] = $stmt->fetch()['total'] ?? 0;
        
        // Valeur totale distribuée
        $query = "SELECT SUM(dist.quantite_distribuee * t.prix_unitaire) as total 
                  FROM distribution dist 
                  JOIN don d ON dist.id_don = d.id_don
                  JOIN type_besoin t ON d.id_type = t.id_type";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $stats['valeur_distribuee'] = $stmt->fetch()['total'] ?? 0;
        
        // Taux de couverture
        if($stats['valeur_besoins'] > 0) {
            $stats['taux_couverture'] = ($stats['valeur_distribuee'] / $stats['valeur_besoins']) * 100;
        } else {
            $stats['taux_couverture'] = 0;
        }
        
        return $stats;
    }
}
