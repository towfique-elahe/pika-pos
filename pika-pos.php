<?php
/**
 * Plugin Name:       Pika POS
 * Plugin URI:        https://github.com/towfique-elahe/pika-pos
 * Description:       Point of Sale for WooCommerce.
 * Version:           0.1.0
 * Author:            Towfique Elahe
 * Author URI:        https://towfiqueelahe.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pika-pos
 * Domain Path:       /languages
 * Requires at least: 6.6
 * Requires PHP:      8.0
 * Requires Plugins:  woocommerce
 * WC requires at least: 9.0
 * WC tested up to:   11.0
 *
 * @package Pika_POS
 */

defined( 'ABSPATH' ) || exit;

define( 'PIKA_POS_VERSION', '0.1.0' );
define( 'PIKA_POS_FILE', __FILE__ );
define( 'PIKA_POS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PIKA_POS_URL', plugin_dir_url( __FILE__ ) );
define( 'PIKA_POS_SLUG', 'pika-pos' );

/**
 * Oldest WooCommerce release this plugin supports.
 */
define( 'PIKA_POS_MIN_WC_VERSION', '9.0' );

/**
 * Whether WooCommerce is present and new enough to build against.
 *
 * @return bool
 */
function pika_pos_woocommerce_ready() {
	return class_exists( 'WooCommerce' )
		&& defined( 'WC_VERSION' )
		&& version_compare( WC_VERSION, PIKA_POS_MIN_WC_VERSION, '>=' );
}

/**
 * Tell WooCommerce this plugin speaks High-Performance Order Storage.
 *
 * Without this declaration WooCommerce disables HPOS while the plugin is active
 * and silently falls back to storing orders as posts.
 */
function pika_pos_declare_woocommerce_compatibility() {
	if ( ! class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		return;
	}

	\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', PIKA_POS_FILE, true );
}
add_action( 'before_woocommerce_init', 'pika_pos_declare_woocommerce_compatibility' );

/**
 * Load translations.
 */
function pika_pos_load_textdomain() {
	load_plugin_textdomain( 'pika-pos', false, dirname( plugin_basename( PIKA_POS_FILE ) ) . '/languages' );
}
add_action( 'init', 'pika_pos_load_textdomain' );

/**
 * Boot the plugin once every other plugin has loaded.
 *
 * Modules go in includes/ and get required here. Nothing is wired up yet.
 */
function pika_pos_boot() {
	if ( ! pika_pos_woocommerce_ready() ) {
		add_action( 'admin_notices', 'pika_pos_render_requirements_notice' );

		return;
	}
}
add_action( 'plugins_loaded', 'pika_pos_boot' );

/**
 * Explain, in the admin, why the plugin is sitting idle.
 */
function pika_pos_render_requirements_notice() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$message = class_exists( 'WooCommerce' )
		? sprintf(
			/* translators: %s: minimum supported WooCommerce version. */
			esc_html__( 'Pika POS needs WooCommerce %s or newer.', 'pika-pos' ),
			esc_html( PIKA_POS_MIN_WC_VERSION )
		)
		: esc_html__( 'Pika POS needs WooCommerce to be installed and active.', 'pika-pos' );

	printf( '<div class="notice notice-warning"><p>%s</p></div>', esc_html( $message ) );
}

/**
 * Activation.
 */
function pika_pos_activate() {
	update_option( 'pika_pos_version', PIKA_POS_VERSION );
}
register_activation_hook( __FILE__, 'pika_pos_activate' );

/**
 * Deactivation.
 */
function pika_pos_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pika_pos_deactivate' );
