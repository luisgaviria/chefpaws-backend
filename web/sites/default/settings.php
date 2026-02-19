<?php

/**
 * @file
 * Drupal 11 configuration for ChefPaws on Railway.
 */

/**
 * 1. THE PROTOCOL FORCER
 * Because Railway terminates SSL at the edge, we must tell PHP
 * that the request is secure even though it arrives on port 80/8080.
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['SERVER_PORT'] = 443;
}

/**
 * 2. REVERSE PROXY CONFIGURATION
 * Tells Drupal to trust the 'X-Forwarded-For' header from Railway.
 */
$settings['reverse_proxy'] = TRUE;
$settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR'] ?? ''] + explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');

/**
 * 3. TRUSTED HOST PATTERN
 * Allows your Railway domain to be recognized as valid.
 */
$settings['trusted_host_patterns'] = [
  '^.*\.railway\.app$',
  '^localhost$',
];

/**
 * 4. DATABASE CONFIGURATION
 * Dynamically pulls from your Railway MySQL instance.
 */
if (getenv('MYSQLHOST')) {
  $databases['default']['default'] = [
    'database' => getenv('MYSQLDATABASE'),
    'username' => getenv('MYSQLUSER'),
    'password' => getenv('MYSQLPASSWORD'),
    'host'     => getenv('MYSQLHOST'),
    'port'     => getenv('MYSQLPORT'),
    'driver'   => 'mysql',
    'prefix'   => '',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'autoload' => 'core/modules/mysql/src/Driver/Database/mysql',
  ];
}

/**
 * 5. SALT & PATHS
 */
$settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: 'fixed-salt-for-chefpaws-v1';
$settings['config_sync_directory'] = 'sites/default/files/sync';
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

/**
 * 6. INSTALLER OVERRIDE
 * Ensures Drupal doesn't try to "correct" its own path during setup.
 */
if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], 'install.php')) {
  $_SERVER['SCRIPT_NAME'] = '/core/install.php';
}

/**
 * 7. LOCAL DEVELOPMENT
 * Keeps your DDEV or local environment working.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}