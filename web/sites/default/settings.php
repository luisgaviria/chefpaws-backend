<?php

// phpcs:ignoreFile

/**
 * @file
 * Drupal site-specific configuration file.
 */

$databases = [];

/**
 * Salt for one-time login links, cancel links, form tokens, etc.
 */
$settings['hash_salt'] = '';

/**
 * Access control for update.php script.
 */
$settings['update_free_access'] = FALSE;

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

/**
 * The default list of directories that will be ignored by Drupal's file API.
 */
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

/**
 * The default number of entities to update in a batch process.
 */
$settings['entity_update_batch_size'] = 50;

/**
 * Entity update backup.
 */
$settings['entity_update_backup'] = TRUE;

/**
 * State caching.
 */
$settings['state_cache'] = TRUE;

/**
 * Node migration type.
 */
$settings['migrate_node_migrate_type_classic'] = FALSE;

// Automatically generated include for settings managed by ddev.
if (getenv('IS_DDEV_PROJECT') == 'true' && file_exists(__DIR__ . '/settings.ddev.php')) {
  include __DIR__ . '/settings.ddev.php';
}

/**
 * Railway Cloud Configuration
 * This block only triggers when running on Railway.
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
  ];

  // Set hash salt from Railway environment variable for security
  $settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: 'a-temporary-salt-for-initial-deploy';

  // Allow Railway domains to access the site
  $settings['trusted_host_patterns'] = [
    '^.*\.railway\.app$',
    '^localhost$',
  ];

  // Set the sync directory to be within the persistent volume
  $settings['config_sync_directory'] = 'sites/default/files/sync';
  
  // Disable DDEV error level if you prefer production defaults
  $config['system.logging']['error_level'] = 'hide';
}

/**
 * Load local development override configuration, if available.
 */
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}