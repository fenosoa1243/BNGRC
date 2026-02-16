<?php

namespace App\Controllers;

use App\Models\Ville;
use Flight;

class VilleController {
    
    private $villeModel;
    
    public function __construct() {
        $this->villeModel = new Ville();
    }
    
    public function liste() {
        $villes = $this->villeModel->all('nom_ville', 'ASC');
        
        Flight::render('ville/liste', [
            'villes' => $villes
        ]);
    }
    
    public function form() {
        $ville_data = null;
        
        if(isset($_GET['id'])) {
            $ville_data = $this->villeModel->find($_GET['id']);
        }
        
        Flight::render('ville/form', [
            'ville_data' => $ville_data
        ]);
    }
    
    public function enregistrer() {
        if(Flight::request()->method == 'POST') {
            $data = [
                'nom_ville' => Flight::request()->data->nom_ville,
                'region' => Flight::request()->data->region
            ];
            
            if(!empty(Flight::request()->data->id_ville)) {
                // Mise à jour
                $this->villeModel->update(Flight::request()->data->id_ville, $data);
                Flight::redirect('/villes?success=update');
            } else {
                // Création
                $this->villeModel->create($data);
                Flight::redirect('/villes?success=create');
            }
        }
    }
    
    public function supprimer() {
        if(isset($_GET['id'])) {
            $this->villeModel->delete($_GET['id']);
            Flight::redirect('/villes?success=delete');
        }
    }
}
