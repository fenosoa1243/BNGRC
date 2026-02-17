<?php

namespace App\Controllers;

use App\Models\Distribution;
use Flight;

class DistributionController {
    
    private $distributionModel;
    
    public function __construct() {
        $this->distributionModel = new Distribution();
    }
    
    public function liste() {
        $distributions = $this->distributionModel->getAllWithDetails();
        
        Flight::render('distribution/liste', [
            'distributions' => $distributions
        ]);
    }
    
    /**
     * Page de simulation avec preview
     */
    public function simuler() {
        Flight::render('distribution/simuler');
    }
    
    /**
     * Simuler le dispatch (sans sauvegarder)
     */
    public function previewDispatch() {
        try {
            $resultat = $this->distributionModel->simulerDispatch(true); // mode preview
            
            Flight::json([
                'success' => true,
                'distributions' => $resultat['distributions'],
                'nb_distributions' => $resultat['nb_distributions'],
                'dons_traites' => $resultat['dons_traites']
            ]);
        } catch(\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Valider et exécuter le dispatch (sauvegarder en BDD)
     */
    public function executer() {
        try {
            $resultat = $this->distributionModel->simulerDispatch(false); // mode réel
            
            Flight::json([
                'success' => true,
                'message' => $resultat['nb_distributions'] . ' distribution(s) effectuée(s)',
                'nb_distributions' => $resultat['nb_distributions']
            ]);
        } catch(\Exception $e) {
            Flight::json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
