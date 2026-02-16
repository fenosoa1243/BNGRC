<?php
use flight\Engine;
use flight\database\PdoWrapper;
use flight\debug\database\PdoQueryCapture;
use flight\debug\tracy\TracyExtensionLoader;
use Tracy\Debugger;

/*********************************************
 * FlightPHP Service Setup (MySQL)
 *********************************************/
Debugger::enable();
Debugger::$logDirectory = __DIR__ . $ds . '..' . $ds . 'log';
Debugger::$strictMode = true;

if (Debugger::$showBar === true && php_sapi_name() !== 'cli') {
    (new TracyExtensionLoader($app));
}

// MySQL DSN
$dsn = 'mysql:host=' . $config['database']['host'] .
       ';port=' . ($config['database']['port'] ?? '3306') .
       ';dbname=' . $config['database']['dbname'];

if (!empty($config['database']['charset'])) {
    $dsn .= ';charset=' . $config['database']['charset'];
}

// Register database service
$pdoClass = Debugger::$showBar === true ? PdoQueryCapture::class : PdoWrapper::class;

$app->register('db', $pdoClass, [
    $dsn,
    $config['database']['user'] ?? null,
    $config['database']['password'] ?? null,
    // Options PDO recommandées pour MySQL
    [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES   => false,
    ]
]);