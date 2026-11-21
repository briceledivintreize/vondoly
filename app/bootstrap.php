<?php
/**
 * Socle « Plume » – amorçage commun (site public, administration, scripts).
 *
 * Tout ce qui est propre à un auteur vit dans la base (data/site.sqlite) :
 * le code reste identique d'un site d'auteur à l'autre.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('PUB', ROOT . '/public');
define('DATA', ROOT . '/data');
define('DB_FILE', DATA . '/site.sqlite');
define('PLUME_VERSION', '1.0.0');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Africa/Lome');

if (is_file(ROOT . '/vendor/autoload.php')) {
    require ROOT . '/vendor/autoload.php';
}

require APP . '/lib/db.php';
require APP . '/lib/helpers.php';
require APP . '/lib/texts.php';
require APP . '/lib/content.php';
require APP . '/lib/images.php';
require APP . '/lib/auth.php';
require APP . '/lib/stats.php';

// Affichage des erreurs uniquement en développement (serveur PHP intégré)
$isDev = PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli';
ini_set('display_errors', $isDev ? '1' : '0');
error_reporting(E_ALL);
