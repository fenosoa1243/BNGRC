<?php

namespace App\Controllers;

use Flight;

class RecapController {
    
    /**
     * Page de récapitulation avec mise à jour AJAX
     */
    public function index() {
        Flight::render('recap/index');
    }
    
    /**
     * API pour récupérer les statistiques en temps réel
     */
    public function getStats() {
        $db = Flight::db();
        
        // Besoins totaux
        $query = "SELECT 
                    SUM(b.quantite * t.prix_unitaire) as montant_total,
                    SUM(b.quantite) as quantite_totale
                  FROM besoin b
                  JOIN type_besoin t ON b.id_type = t.id_type";
        $stmt = $db->query($query);
        $besoins = $stmt->fetch();
        
        // Besoins satisfaits (distribués)
        $query = "SELECT 
                    SUM(dist.quantite_distribuee * t.prix_unitaire) as montant_satisfait,
                    SUM(dist.quantite_distribuee) as quantite_satisfaite
                  FROM distribution dist
                  JOIN don d ON dist.id_don = d.id_don
                  JOIN type_besoin t ON d.id_type = t.id_type";
        $stmt = $db->query($query);
        $satisfaits = $stmt->fetch();
        
        $montant_besoins = floatval($besoins['montant_total'] ?? 0);
        $montant_satisfait = floatval($satisfaits['montant_satisfait'] ?? 0);
        $montant_restant = $montant_besoins - $montant_satisfait;
        
        Flight::json([
            'besoins' => [
                'montant_total' => $montant_besoins,
                'quantite_totale' => floatval($besoins['quantite_totale'] ?? 0)
            ],
            'satisfaits' => [
                'montant' => $montant_satisfait,
                'quantite' => floatval($satisfaits['quantite_satisfaite'] ?? 0)
            ],
            'restants' => [
                'montant' => $montant_restant,
                'pourcentage' => $montant_besoins > 0 ? ($montant_restant / $montant_besoins) * 100 : 0
            ]
        ]);
    }
}
