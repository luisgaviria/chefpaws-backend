<?php


/**
 * @file
 * Drupal 11 site-specific configuration file for ChefPaws.
 */

/**
 * 1. THE PROTOCOL FORCER
 * This tells Drupal: "You are on HTTPS and Port 443. Period."
 * This kills the HTTP -> HTTPS redirect loop.
 */
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;

/**
 * 2. THE PROXY TRUST
 * Tells Drupal to trust the 'X-Forwarded-For' header from Railway.
 */
$settings['reverse_proxy'] = TRUE;
$settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR'] ?? ''] + explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');

/**
 * 3. THE INSTALLER PATH FIX
 * Forces the script identity so Drupal doesn't try to "correct" its own path.
 */
if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], 'install.php')) {
  $_SERVER['SCRIPT_NAME'] = '/core/install.php';
  $_SERVER['PHP_SELF'] = '/core/install.php';
  $_SERVER['REQUEST_URI'] = '/core/install.php';
  $_SERVER['SCRIPT_FILENAME'] = '/app/web/core/install.php';
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

  $settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: 'fixed-salt-for-chefpaws';

  // Trust all hosts temporarily to ensure the loop is broken.
  $settings['trusted_host_patterns'] = ['.*'];

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