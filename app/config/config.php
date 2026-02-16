<?php
/**********************************************
 * FlightPHP Configuration for MySQL
 **********************************************/
define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

date_default_timezone_set('UTC');
error_reporting(E_ALL);
if (function_exists('mb_internal_encoding') === true) {
    mb_internal_encoding('UTF-8');
}
if (function_exists('setlocale') === true) {
    setlocale(LC_ALL, 'en_US.UTF-8');
}

// Get the Flight app instance
if (empty($app) === true) {
    $app = Flight::app();
}

// Autoload app directory
$app->path(__DIR__ . $ds . '..' . $ds . '..');

// Core Flight settings
$app->set('flight.base_url', BASE_URL);
$app->set('flight.case_sensitive', false);
$app->set('flight.log_errors', true);
$app->set('flight.handle_errors', false);
$app->set('flight.views.path', __DIR__ . $ds . '..' . $ds . 'views');
$app->set('flight.views.extension', '.php');
$app->set('flight.content_length', false);

// CSP nonce
$nonce = bin2hex(random_bytes(16));
$app->set('csp_nonce', $nonce);

// MySQL Database config
return [
    // 'database' => [
    //     'host'     => 'localhost',
    //     'port'     => '3306',                  // Port par défaut MySQL
    //     'dbname'   => 'livraison_db',              // Nom de votre base MySQL
    //     'user'     => 'root',                  // À changer selon votre utilisateur MySQL
    //     'password' => '',                      // À changer selon votre mot de passe
    //     'charset'  => 'utf8mb4',               // Recommandé pour un bon support UTF-8
    // ],

    'database' => [
        'host'     => 'localhost',
        'port'     => '3306',                  // Port par défaut MySQL
        'dbname'   => 'bngrc_dons',              // Nom de votre base MySQL
        'user'     => 'root',                  // À changer selon votre utilisateur MySQL
        'password' => '',                      // À changer selon votre mot de passe
        'charset'  => 'utf8mb4',               // Recommandé pour un bon support UTF-8
    ],
];