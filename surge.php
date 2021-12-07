<?php
/**
 * Plugin Name: Surge
 * Plugin URI: https://github.com/kovshenin/surge
 * Description: A fast and simple page caching plugin for WordPress
 * Author: Konstantin Kovshenin
 * Author URI: https://konstantil.blog
 * Text Domain: surge
 * Domain Path: /languages
 * Version: 1.0.0
 *
 * @package Surge
 */

namespace Surge;

// Attempt to cache this request if cache is on.
if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
	include_once( __DIR__ . '/include/cache.php' );
}

// Load more files later when necessary.
add_action( 'plugins_loaded', function() {
	if ( false === get_option( 'surge_installed', false ) ) {
		if ( add_option( 'surge_installed', 0 ) ) {
			require_once( __DIR__ . '/include/install.php' );
		}
	}

	if ( wp_doing_cron() ) {
		include_once( __DIR__ . '/include/cron.php' );
	}

	include_once( __DIR__ . '/include/invalidate.php' );
} );

// Site Health events
add_filter( 'site_status_tests', function( $tests ) {
	include_once( __DIR__ . '/include/health.php' );

	$tests['direct']['surge'] = [
		'label' => 'Caching Test',
		'test' => '\Surge\health_test',
	];

	return $tests;
} );

// Schedule cron events.
add_action( 'shutdown', function() {
	if ( ! wp_next_scheduled( 'surge_delete_expired' ) ) {
		wp_schedule_event( time(), 'hourly', 'surge_delete_expired' );
	}
} );

// Re-install on activation
register_activation_hook( __FILE__, function() {
	delete_option( 'surge_installed' );
} );
