<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

/**
 * THE FINAL LOOP BREAKER: Port-Aware
 * Intercepts the internal 8080 signal and forces 443.
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['SERVER_PORT'] = 443;
}

// If Railway is using 8080 internally, tell PHP to ignore it
if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 8080) {
  $_SERVER['SERVER_PORT'] = 443;
}

$settings['reverse_proxy'] = TRUE;
// Trust the proxy IP or the forwarded-for header.
$settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR'] ?? ''] + explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');

/**
 * SCRIPT IDENTITY: Stops the install.php loop
 */
if (str_contains($_SERVER['REQUEST_URI'], 'install.php')) {
  $_SERVER['REQUEST_URI'] = '/core/install.php';
  $_SERVER['SCRIPT_NAME'] = '/core/install.php';
  // Hard-codes the base URL for the installer to stop the 302 bounce.
  $base_url = 'https://chefpaws-backend-production.up.railway.app';
}

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

  $settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: 'initial-deployment-salt-change-me-in-railway-vars';

  $settings['trusted_host_patterns'] = [
    '^.*\.railway\.app$',
    '^.*\.up\.railway\.app$',
    '^localhost$',
  ];

  $settings['config_sync_directory'] = 'sites/default/files/sync';
  $config['system.logging']['error_level'] = 'hide';
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