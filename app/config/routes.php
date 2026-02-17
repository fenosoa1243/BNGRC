<?php
/**
 * Fichier de routage pour le projet BNGRC
 * Gestion des routes de l'application
 */

use App\Controllers\DashboardController;
use App\Controllers\VilleController;
use App\Controllers\BesoinController;
use App\Controllers\DonController;
use App\Controllers\DistributionController;
use App\Controllers\AchatController;
use App\Controllers\RecapController;

// Route par défaut
Flight::route('/', function () {
    Flight::redirect(BASE_URL . '/dashboard');
});

// DASHBOARD
Flight::route('GET /dashboard', function() {
    $controller = new DashboardController();
    $controller->index();
});

// VILLES
Flight::route('GET /villes', function() {
    $controller = new VilleController();
    $controller->liste();
});

Flight::route('GET /villes/nouveau', function() {
    $controller = new VilleController();
    $controller->form();
});

Flight::route('GET /villes/modifier/@id', function($id) {
    $controller = new VilleController();
    $controller->form($id);
});

Flight::route('POST /villes/enregistrer', function() {
    $controller = new VilleController();
    $controller->enregistrer();
});

Flight::route('GET /villes/supprimer/@id', function($id) {
    $controller = new VilleController();
    $controller->supprimer($id);
});

// BESOINS
Flight::route('GET /besoins', function() {
    $controller = new BesoinController();
    $controller->liste();
});

Flight::route('GET /besoins/nouveau', function() {
    $controller = new BesoinController();
    $controller->form();
});

Flight::route('GET /besoins/modifier/@id', function($id) {
    $controller = new BesoinController();
    $controller->form($id);
});

Flight::route('POST /besoins/enregistrer', function() {
    $controller = new BesoinController();
    $controller->enregistrer();
});

Flight::route('GET /besoins/supprimer/@id', function($id) {
    $controller = new BesoinController();
    $controller->supprimer($id);
});

// DONS
Flight::route('GET /dons', function() {
    $controller = new DonController();
    $controller->liste();
});

Flight::route('GET /dons/nouveau', function() {
    $controller = new DonController();
    $controller->form();
});

Flight::route('POST /dons/enregistrer', function() {
    $controller = new DonController();
    $controller->enregistrer();
});

// DISTRIBUTIONS
Flight::route('GET /distributions', function() {
    $controller = new DistributionController();
    $controller->liste();
});

Flight::route('GET /distributions/simuler', function() {
    $controller = new DistributionController();
    $controller->simuler();
});

Flight::route('POST /distributions/preview', function() {
    $controller = new DistributionController();
    $controller->previewDispatch();
});

Flight::route('POST /distributions/executer', function() {
    $controller = new DistributionController();
    $controller->executer();
});

// ACHATS
Flight::route('GET /achats', function() {
    $controller = new AchatController();
    $controller->index();
});

Flight::route('POST /achats/acheter', function() {
    $controller = new AchatController();
    $controller->acheter();
});

Flight::route('POST /achats/configurer-frais', function() {
    $controller = new AchatController();
    $controller->configurerFrais();
});

// RÉCAPITULATION
Flight::route('GET /recap', function() {
    $controller = new RecapController();
    $controller->index();
});

Flight::route('GET /recap/stats', function() {
    $controller = new RecapController();
    $controller->getStats();
});
