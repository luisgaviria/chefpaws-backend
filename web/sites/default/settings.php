<?php

/**
 * @file
 * Drupal site-specific configuration file.
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
    // Vital for Drupal 11 on Railway's environment
    'autoload' => 'core/modules/mysql/src/Driver/Database/mysql',
  ];

  // Uses the environment variable from Railway; falls back to a temporary one for the first boot
  $settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: 'initial-deployment-salt-change-me-in-railway-vars';

  $settings['trusted_host_patterns'] = [
    '^.*\.railway\.app$',
    '^.*\.up\.railway\.app$',
    '^localhost$',
  ];

  // Pointing config sync to the persistent volume mount
  $settings['config_sync_directory'] = 'sites/default/files/sync';
  
  $config['system.logging']['error_level'] = 'hide';
}

/**
 * Load local development override configuration (DDEV), if available.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}

// Automatically generated include for settings managed by ddev
if (getenv('IS_DDEV_PROJECT') == 'true' && file_exists(__DIR__ . '/settings.ddev.php')) {
  include __DIR__ . '/settings.ddev.php';
}