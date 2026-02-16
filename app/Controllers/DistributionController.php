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
    
    public function simuler() {
        Flight::render('distribution/simuler', []);
    }
    
    public function executer() {
        try {
            $nb_distributions = $this->distributionModel->simulerDispatch();
            Flight::redirect(BASE_URL . '/distributions?success=dispatch&nb=' . $nb_distributions);
        } catch(\Exception $e) {
            Flight::redirect(BASE_URL . '/distributions/simuler?error=dispatch&message=' . urlencode($e->getMessage()));
        }
    }
}
