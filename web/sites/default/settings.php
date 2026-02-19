<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

/**
 * THE ULTIMATE CIRCUIT BREAKER
 * Forces the path and protocol to stop the 302 bounce.
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['SERVER_PORT'] = 443;
}

// Intercept the internal 8080 port revealed in your logs.
if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 8080) {
  $_SERVER['SERVER_PORT'] = 443;
}

/**
 * INTERNAL REDIRECT KILLER
 * Forces internal pathing to match what Drupal expects.
 */
if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], 'install.php')) {
  $_SERVER['SCRIPT_NAME'] = '/core/install.php';
  $_SERVER['PHP_SELF'] = '/core/install.php';
  $_SERVER['REQUEST_URI'] = '/core/install.php';
  // Explicitly tell PHP where the file is on the Railway disk.
  $_SERVER['SCRIPT_FILENAME'] = '/app/web/core/install.php';
}

$settings['reverse_proxy'] = TRUE;
// Trust the proxy IP provided by Railway.
$settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR'] ?? ''] + explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');

/**
 * Global Settings
 */
$databases = [];
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
$settings['file_scan_ignore_directories'] = ['node_modules', 'bower_components'];
$settings['entity_update_batch_size'] = 50;
$settings['entity_update_backup'] = TRUE;
$settings['state_cache'] = TRUE;
$settings['migrate_node_migrate_type_classic'] = FALSE;

/**
 * Railway Cloud Configuration
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

  // Using a stable salt to avoid potential environment variable issues during setup.
  $settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: 'stable-salt-for-chefpaws-backend';

  $settings['trusted_host_patterns'] = [
    '^.*\.railway\.app$',
    '^.*\.up\.railway\.app$',
    '^localhost$',
  ];

  $settings['config_sync_directory'] = 'sites/default/files/sync';
  $config['system.logging']['error_level'] = 'verbose';
}

/**
 * Load local development override configuration (DDEV), if available.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}

if (getenv('IS_DDEV_PROJECT') == 'true' && file_exists(__DIR__ . '/settings.ddev.php')) {
  include __DIR__ . '/settings.ddev.php';
}