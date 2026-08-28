<?php
/**
 * Test bootstrap.
 *
 * Tests here are integration tests: they boot the real local WordPress Studio
 * site and exercise the plugin against real WooCommerce and a real database.
 * That is a deliberate trade — a POS is almost entirely integration, and mocking
 * WooCommerce would test the mocks instead of the plugin.
 *
 * Consequence: tests write to the local site's database. Anything a test creates,
 * it must remove again in tearDown().
 *
 * @package Pika_POS
 */

define( 'PIKA_POS_TESTS_SITE_ROOT', dirname( __DIR__, 4 ) );

if ( ! file_exists( PIKA_POS_TESTS_SITE_ROOT . '/wp-load.php' ) ) {
	fwrite(
		STDERR,
		'Could not find WordPress at ' . PIKA_POS_TESTS_SITE_ROOT . ".\n" .
		"These tests expect the plugin to live in wp-content/plugins of a WordPress install.\n"
	);
	exit( 1 );
}

require_once __DIR__ . '/../../vendor/autoload.php';

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- WP_USE_THEMES is a WordPress core constant, not one this plugin defines.
define( 'WP_USE_THEMES', false );

require_once PIKA_POS_TESTS_SITE_ROOT . '/wp-load.php';

if ( ! class_exists( 'WooCommerce' ) ) {
	fwrite( STDERR, "WooCommerce is not active on the test site.\n" );
	exit( 1 );
}

if ( ! defined( 'PIKA_POS_VERSION' ) ) {
	fwrite( STDERR, "Pika POS is not active on the test site. Run: studio wp plugin activate pika-pos\n" );
	exit( 1 );
}

// Tests run as the site administrator; capability behaviour is asserted explicitly.
wp_set_current_user( 1 );
