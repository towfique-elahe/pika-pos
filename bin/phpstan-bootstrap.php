<?php
/**
 * Constants PHPStan needs to know about.
 *
 * They are defined in pika-pos.php at runtime, but static analysis of one file
 * cannot see a constant defined in another. Only the types matter here.
 *
 * @package Pika_POS
 */

define( 'PIKA_POS_VERSION', '0.1.0' );
define( 'PIKA_POS_FILE', '' );
define( 'PIKA_POS_PATH', '' );
define( 'PIKA_POS_URL', '' );
define( 'PIKA_POS_SLUG', 'pika-pos' );
define( 'PIKA_POS_MIN_WC_VERSION', '9.0' );
