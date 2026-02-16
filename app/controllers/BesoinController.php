<?php

namespace App\Controllers;

use App\Models\Besoin;
use App\Models\Ville;
use App\Models\TypeBesoin;
use Flight;

class BesoinController {
    
    private $besoinModel;
    private $villeModel;
    private $typeBesoinModel;
    
    public function __construct() {
        $this->besoinModel = new Besoin();
        $this->villeModel = new Ville();
        $this->typeBesoinModel = new TypeBesoin();
    }
    
    public function liste() {
        $besoins = $this->besoinModel->getAllWithDetails();
        
        Flight::render('besoin/liste', [
            'besoins' => $besoins
        ]);
    }
    
    public function form() {
        $besoin_data = null;
        
        if(isset($_GET['id'])) {
            $besoin_data = $this->besoinModel->getWithDetails($_GET['id']);
        }
        
        $villes = $this->villeModel->all('nom_ville', 'ASC');
        $types_besoins = $this->typeBesoinModel->all('categorie, nom_type', 'ASC');
        
        Flight::render('besoin/form', [
            'besoin_data' => $besoin_data,
            'villes' => $villes,
            'types_besoins' => $types_besoins
        ]);
    }
    
    public function enregistrer() {
        if(Flight::request()->method == 'POST') {
            $data = [
                'id_ville' => Flight::request()->data->id_ville,
                'id_type' => Flight::request()->data->id_type,
                'quantite' => Flight::request()->data->quantite
            ];
            
            if(!empty(Flight::request()->data->id_besoin)) {
                // Mise à jour
                $this->besoinModel->update(Flight::request()->data->id_besoin, $data);
                Flight::redirect('/besoins?success=update');
            } else {
                // Création
                $this->besoinModel->create($data);
                Flight::redirect('/besoins?success=create');
            }
        }
    }
    
    public function supprimer() {
        if(isset($_GET['id'])) {
            $this->besoinModel->delete($_GET['id']);
            Flight::redirect('/besoins?success=delete');
        }
    }
}
