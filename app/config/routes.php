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

// ========================================
// ROUTE PAR DÉFAUT
// ========================================
Flight::route('/', function () {
    Flight::redirect(BASE_URL . '/dashboard');
});

// ========================================
// DASHBOARD
// ========================================
Flight::route('GET /dashboard', function() {
    $controller = new DashboardController();
    $controller->index();
});

// ========================================
// VILLES
// ========================================
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

// ========================================
// BESOINS
// ========================================
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

// ========================================
// DONS
// ========================================
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

// ========================================
// DISTRIBUTIONS
// ========================================
Flight::route('GET /distributions', function() {
    $controller = new DistributionController();
    $controller->liste();
});

Flight::route('GET /distributions/simuler', function() {
    $controller = new DistributionController();
    $controller->simuler();
});

Flight::route('POST /distributions/executer', function() {
    $controller = new DistributionController();
    $controller->executer();
});

// ========================================
// ACHATS
// ========================================
Flight::route('GET /achats', function() {
    $controller = new AchatController();
    $controller->liste();
});

Flight::route('GET /achats/besoins-restants', function() {
    $controller = new AchatController();
    $controller->besoinsRestants();
});

Flight::route('POST /achats/simuler', function() {
    $controller = new AchatController();
    $controller->simuler();
});

Flight::route('POST /achats/valider', function() {
    $controller = new AchatController();
    $controller->valider();
});

Flight::route('POST /achats/annuler-simulation', function() {
    $controller = new AchatController();
    $controller->annulerSimulation();
});

// ========================================
// RÉCAPITULATIF
// ========================================
Flight::route('GET /recap', function() {
    $controller = new RecapController();
    $controller->index();
});

Flight::route('GET /recap/data', function() {
    $controller = new RecapController();
    $controller->getData();
});

// ========================================
// ROUTES API (JSON)
// ========================================
Flight::route('GET /api/villes', function() {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/models/Ville.php';
    
    $database = new Database();
    $db = $database->getConnection();
    $ville = new Ville($db);
    
    $stmt = $ville->lire();
    $villes = $stmt->fetchAll();
    
    Flight::json($villes);
});

Flight::route('GET /api/types-besoins', function() {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/models/TypeBesoin.php';
    
    $database = new Database();
    $db = $database->getConnection();
    $typeBesoin = new TypeBesoin($db);
    
    $stmt = $typeBesoin->lire();
    $types = $stmt->fetchAll();
    
    Flight::json($types);
});

Flight::route('GET /api/dashboard/stats', function() {
    require_once __DIR__ . '/config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Statistiques globales
    $stats = [];
    
    $query = "SELECT COUNT(*) as total FROM ville";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['nb_villes'] = $stmt->fetch()['total'];
    
    $query = "SELECT SUM(b.quantite * t.prix_unitaire) as total 
              FROM besoin b JOIN type_besoin t ON b.id_type = t.id_type";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['valeur_besoins'] = $stmt->fetch()['total'] ?? 0;
    
    $query = "SELECT SUM(d.quantite * t.prix_unitaire) as total 
              FROM don d JOIN type_besoin t ON d.id_type = t.id_type";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats['valeur_dons'] = $stmt->fetch()['total'] ?? 0;
    
    Flight::json($stats);
});

Flight::route('GET /api/dispatch/preview', function() {
    $database = new \App\Models\Model();
    $db = Flight::db();
    
    try {
        // Compter les dons disponibles
        $query_dons = "SELECT COUNT(*) as total, 
                      SUM(d.quantite_restante * t.prix_unitaire) as valeur 
                      FROM don d 
                      JOIN type_besoin t ON d.id_type = t.id_type 
                      WHERE d.quantite_restante > 0";
        $stmt = $db->query($query_dons);
        $dons_stats = $stmt->fetch();
        
        // Compter les besoins non satisfaits
        $query_besoins = "SELECT COUNT(DISTINCT b.id_besoin) as total
                         FROM besoin b
                         LEFT JOIN (
                             SELECT dist.id_ville, dd.id_type, SUM(dist.quantite_distribuee) as distribue
                             FROM distribution dist
                             JOIN don dd ON dist.id_don = dd.id_don
                             GROUP BY dist.id_ville, dd.id_type
                         ) d ON b.id_ville = d.id_ville AND b.id_type = d.id_type
                         WHERE (b.quantite - COALESCE(d.distribue, 0)) > 0";
        $stmt = $db->query($query_besoins);
        $besoins_stats = $stmt->fetch();
        
        // Aperçu par type
        $query_preview = "SELECT t.nom_type, t.categorie, t.unite,
                         COALESCE(SUM(d.quantite_restante), 0) as quantite_disponible,
                         COALESCE((
                             SELECT SUM(b.quantite) - COALESCE(SUM(dist.quantite_distribuee), 0)
                             FROM besoin b
                             LEFT JOIN distribution dist ON b.id_ville = dist.id_ville
                             LEFT JOIN don dd ON dist.id_don = dd.id_don AND dd.id_type = b.id_type
                             WHERE b.id_type = t.id_type
                             GROUP BY b.id_type
                         ), 0) as quantite_besoin,
                         COALESCE((
                             SELECT COUNT(DISTINCT b.id_ville)
                             FROM besoin b
                             WHERE b.id_type = t.id_type
                         ), 0) as nb_villes
                         FROM type_besoin t
                         LEFT JOIN don d ON t.id_type = d.id_type AND d.quantite_restante > 0
                         GROUP BY t.id_type, t.nom_type, t.categorie, t.unite
                         HAVING quantite_disponible > 0 OR quantite_besoin > 0
                         ORDER BY t.categorie, t.nom_type";
        $stmt = $db->query($query_preview);
        $preview = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        Flight::json([
            'success' => true,
            'stats' => [
                'nb_dons_disponibles' => $dons_stats['total'] ?? 0,
                'valeur_disponible' => $dons_stats['valeur'] ?? 0,
                'nb_besoins_restants' => $besoins_stats['total'] ?? 0
            ],
            'preview' => $preview
        ]);
        
    } catch(\Exception $e) {
        Flight::json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

?>