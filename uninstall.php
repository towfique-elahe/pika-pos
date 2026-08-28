<?php
/**
 * Uninstall: remove what the plugin stored, on explicit request only.
 *
 * @package Pika_POS
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'pika_pos_version' );
