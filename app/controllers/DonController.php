<?php

namespace App\Controllers;

use App\Models\Don;
use App\Models\TypeBesoin;
use Flight;

class DonController {
    
    private $donModel;
    private $typeBesoinModel;
    
    public function __construct() {
        $this->donModel = new Don();
        $this->typeBesoinModel = new TypeBesoin();
    }
    
    public function liste() {
        $dons = $this->donModel->getAllWithDetails();
        
        Flight::render('don/liste', [
            'dons' => $dons
        ]);
    }
    
    public function form() {
        $types_besoins = $this->typeBesoinModel->all('categorie, nom_type', 'ASC');
        
        Flight::render('don/form', [
            'types_besoins' => $types_besoins
        ]);
    }
    
    public function enregistrer() {
        if(Flight::request()->method == 'POST') {
            $data = [
                'id_type' => Flight::request()->data->id_type,
                'quantite' => Flight::request()->data->quantite,
                'quantite_restante' => Flight::request()->data->quantite,
                'donateur' => Flight::request()->data->donateur
            ];
            
            $this->donModel->create($data);
            Flight::redirect('/dons?success=create');
        }
    }
}
