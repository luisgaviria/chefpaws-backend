<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

/**
 * THE ABSOLUTE PATH & PROTOCOL FORCING
 * We hard-code these to stop the Railway Edge from looping.
 */
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;

if (str_contains($_SERVER['REQUEST_URI'] ?? '', 'install.php')) {
  $_SERVER['SCRIPT_NAME'] = '/core/install.php';
  $_SERVER['PHP_SELF'] = '/core/install.php';
  $_SERVER['REQUEST_URI'] = '/core/install.php';
  $_SERVER['SCRIPT_FILENAME'] = '/app/web/core/install.php';
}

$settings['reverse_proxy'] = TRUE;
// Trusting all potential Railway proxy hops.
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

  // Using a static salt temporarily to rule out environment variable lag.
  $settings['hash_salt'] = 'stable-salt-for-troubleshooting-123';

  $settings['trusted_host_patterns'] = ['.*']; // Trust all hosts temporarily to break the loop.

  $settings['config_sync_directory'] = 'sites/default/files/sync';
  $config['system.logging']['error_level'] = 'verbose'; // Set to verbose to see actual errors if it loads.
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