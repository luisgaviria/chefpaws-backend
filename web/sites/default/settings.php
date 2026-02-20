<?php

/**
 * @file
 * Drupal 11 configuration for ChefPaws.
 * Optimized for Railway and Heroku deployment.
 */

/**
 * 1. THE PROTOCOL FORCER
 * Handles SSL termination at the edge for PaaS providers.
 */
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
  $_SERVER['HTTPS'] = 'on';
  $_SERVER['SERVER_PORT'] = 443;
}

/**
 * 2. REVERSE PROXY CONFIGURATION
 * Tells Drupal to trust the 'X-Forwarded-For' header.
 */
$settings['reverse_proxy'] = TRUE;

// Trust the immediate proxy sending the request.
$settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR'] ?? ''];

// Specifically for Railway: iterate through the forwarded chain.
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
  $settings['reverse_proxy_addresses'] = array_merge(
    $settings['reverse_proxy_addresses'],
    array_map('trim', $forwarded_ips)
  );
}

/**
 * 3. TRUSTED HOST PATTERN
 * Validates domains for Railway, Heroku, and Local Dev.
 */
$settings['trusted_host_patterns'] = [
  '^.*\.railway\.app$',
  '^.*\.herokuapp\.com$',
  '^localhost$',
  '^127\.0\.0\.1$',
];

/**
 * 4. DATABASE CONFIGURATION
 * Supports Railway (MYSQLHOST) and Heroku (DATABASE_URL).
 */
if (getenv('DATABASE_URL')) {
  // Heroku / Universal URL format
  $db_url = parse_url(getenv('DATABASE_URL'));
  $databases['default']['default'] = [
    'database'  => substr($db_url['path'], 1),
    'username'  => $db_url['user'],
    'password'  => $db_url['pass'],
    'host'      => $db_url['host'],
    'port'      => $db_url['port'] ?? '',
    'driver'    => ($db_url['scheme'] === 'postgres' || $db_url['scheme'] === 'pgsql') ? 'pgsql' : 'mysql',
    'prefix'    => '',
    'namespace' => ($db_url['scheme'] === 'postgres' || $db_url['scheme'] === 'pgsql') 
                   ? 'Drupal\\Core\\Database\\Driver\\pgsql' 
                   : 'Drupal\\Core\\Database\\Driver\\mysql',
  ];
} elseif (getenv('MYSQLHOST')) {
  // Railway specific environment variables
  $databases['default']['default'] = [
    'database'  => getenv('MYSQLDATABASE'),
    'username'  => getenv('MYSQLUSER'),
    'password'  => getenv('MYSQLPASSWORD'),
    'host'      => getenv('MYSQLHOST'),
    'port'      => getenv('MYSQLPORT'),
    'driver'    => 'mysql',
    'prefix'    => '',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'autoload'  => 'core/modules/mysql/src/Driver/Database/mysql',
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
 * 6. INSTALLER & FILE SYSTEM
 */
$settings['file_public_path'] = 'sites/default/files';

if (isset($_SERVER['REQUEST_URI']) && str_contains($_SERVER['REQUEST_URI'], 'install.php')) {
  $_SERVER['SCRIPT_NAME'] = '/core/install.php';
}

/**
 * 7. LOCAL DEVELOPMENT
 */
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}