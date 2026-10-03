<?php
/**
 * Plugin Name: Paid Memberships Pro - TwittPay Gateway
 * Plugin URI: https://twittpay.com
 * Description: Accept bKash, Nagad, Rocket and Upay payments in Paid Memberships Pro with automatic verification.
 * Version: 2.6.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: TwittPay
 * Author URI: https://twittpay.com
 * Text Domain: pmpro-twittpay
 *
 * @package TwittPay\PMPro
 */

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

define( 'PMPRO_TWITTPAY_FILE', __FILE__ );
define( 'PMPRO_TWITTPAY_DIR', dirname( PMPRO_TWITTPAY_FILE ) );

require PMPRO_TWITTPAY_DIR . '/vendor/autoload.php';

if ( class_exists( \TwittPay\PMPro\Core\Plugin::class ) ) {
\TwittPay\PMPro\Core\Plugin::run();
}
