<?php

namespace App\Controllers;

use App\Models\Besoin;
use App\Models\Distribution;
use Flight;

class RecapController {
    
    private $besoinModel;
    private $distributionModel;
    
    public function __construct() {
        $this->besoinModel = new Besoin();
        $this->distributionModel = new Distribution();
    }
    
    /**
     * Page de récapitulation
     */
    public function index() {
        Flight::render('recap/index', []);
    }
    
    /**
     * API pour récupérer les statistiques de récapitulation
     */
    public function getStats() {
        try {
            $db = Flight::db();
            
            // Calculer les besoins totaux en montant
            $sql = "SELECT 
                    SUM(b.quantite * t.prix_unitaire) as besoins_totaux_montant,
                    COUNT(DISTINCT b.id_besoin) as nombre_besoins
                    FROM besoin b
                    JOIN type_besoin t ON b.id_type = t.id_type";
            $stmt = $db->query($sql);
            $besoins = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Calculer les besoins satisfaits en montant
            $sql = "SELECT 
                    SUM(dist.quantite_distribuee * t.prix_unitaire) as besoins_satisfaits_montant,
                    COUNT(DISTINCT dist.id_distribution) as nombre_distributions
                    FROM distribution dist
                    JOIN don d ON dist.id_don = d.id_don
                    JOIN type_besoin t ON d.id_type = t.id_type";
            $stmt = $db->query($sql);
            $satisfaits = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Calculer les besoins restants
            $besoins_totaux = $besoins['besoins_totaux_montant'] ?? 0;
            $besoins_satisfaits = $satisfaits['besoins_satisfaits_montant'] ?? 0;
            $besoins_restants = $besoins_totaux - $besoins_satisfaits;
            
            // Calculer le pourcentage de satisfaction
            $pourcentage_satisfait = $besoins_totaux > 0 
                ? round(($besoins_satisfaits / $besoins_totaux) * 100, 2) 
                : 0;
            
            // Récupérer les statistiques par ville
            // Note: Le EXISTS dans le LEFT JOIN assure que nous ne comptons que les distributions
            // dont le don correspond au même type de besoin que celui enregistré pour la ville
            $sql = "SELECT 
                    v.nom_ville,
                    v.region,
                    SUM(b.quantite * t.prix_unitaire) as besoin_ville,
                    COALESCE(SUM(dist.quantite_distribuee * t.prix_unitaire), 0) as satisfait_ville,
                    (SUM(b.quantite * t.prix_unitaire) - COALESCE(SUM(dist.quantite_distribuee * t.prix_unitaire), 0)) as restant_ville
                    FROM ville v
                    JOIN besoin b ON v.id_ville = b.id_ville
                    JOIN type_besoin t ON b.id_type = t.id_type
                    LEFT JOIN distribution dist ON v.id_ville = dist.id_ville 
                        AND EXISTS (SELECT 1 FROM don d WHERE d.id_don = dist.id_don AND d.id_type = t.id_type)
                    GROUP BY v.id_ville, v.nom_ville, v.region
                    ORDER BY restant_ville DESC";
            $stmt = $db->query($sql);
            $stats_par_ville = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Récupérer les statistiques par catégorie
            // Note: Même logique que ci-dessus pour faire correspondre les types de dons et besoins
            $sql = "SELECT 
                    t.categorie,
                    t.nom_type,
                    t.unite,
                    SUM(b.quantite) as quantite_besoin,
                    COALESCE(SUM(dist.quantite_distribuee), 0) as quantite_satisfait,
                    (SUM(b.quantite) - COALESCE(SUM(dist.quantite_distribuee), 0)) as quantite_restante,
                    SUM(b.quantite * t.prix_unitaire) as valeur_besoin,
                    COALESCE(SUM(dist.quantite_distribuee * t.prix_unitaire), 0) as valeur_satisfait
                    FROM type_besoin t
                    JOIN besoin b ON t.id_type = b.id_type
                    LEFT JOIN distribution dist ON b.id_ville = dist.id_ville 
                        AND EXISTS (SELECT 1 FROM don d WHERE d.id_don = dist.id_don AND d.id_type = t.id_type)
                    GROUP BY t.id_type, t.categorie, t.nom_type, t.unite
                    ORDER BY t.categorie, t.nom_type";
            $stmt = $db->query($sql);
            $stats_par_type = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            Flight::json([
                'success' => true,
                'stats' => [
                    'besoins_totaux_montant' => round($besoins_totaux, 2),
                    'besoins_satisfaits_montant' => round($besoins_satisfaits, 2),
                    'besoins_restants_montant' => round($besoins_restants, 2),
                    'pourcentage_satisfait' => $pourcentage_satisfait,
                    'nombre_besoins' => $besoins['nombre_besoins'] ?? 0,
                    'nombre_distributions' => $satisfaits['nombre_distributions'] ?? 0
                ],
                'stats_par_ville' => $stats_par_ville,
                'stats_par_type' => $stats_par_type
            ]);
            
        } catch(\Exception $e) {
            Flight::json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
