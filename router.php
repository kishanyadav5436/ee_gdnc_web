<?php
/**
 * PHP built-in web server router for Drupal.
 * Run: php -S 127.0.0.1:8080 router.php
 *
 * This router:
 *  1. Serves existing static files (CSS, JS, images) directly.
 *  2. Routes everything else through Drupal's index.php.
 */

$docroot = __DIR__ . '/ee-gndec-website/web';

// Requested URI (strip query string)
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve real static files directly (CSS, JS, images, fonts, etc.)
$file = $docroot . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
  return false; // Let built-in server handle it
}

// Route everything else through Drupal's index.php
chdir($docroot);
$_SERVER['SCRIPT_FILENAME'] = $docroot . '/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';
include $docroot . '/index.php';
