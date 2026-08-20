<?php

/**
 * @file
 * Local development overrides for EE GNDEC website.
 *
 * IMPORTANT: Do NOT commit this file to version control.
 * It is already excluded by .gitignore.
 */

// Increase PHP execution time for Drupal's heavy bootstrap on slow machines.
ini_set('max_execution_time', 120);
ini_set('memory_limit', '256M');

// ── Trusted hosts (PHP built-in server on localhost) ──────────────────────
$settings['trusted_host_patterns'] = [
  '^localhost$',
  '^127\.0\.0\.1$',
];

// ── Development: Disable CSS/JS aggregation ──────────────────────────────
// This prevents 404s on /sites/default/files/css/*.css aggregated bundles
// during development when the aggregated files haven't been generated yet.
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

// ── Enable verbose error display ──────────────────────────────────────────
$config['system.logging']['error_level'] = 'verbose';

// ── Allow Drupal to run via PHP built-in server (no .htaccess processing) ─
// The router.php file at the project root handles URL routing.
