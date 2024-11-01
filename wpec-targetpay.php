<?php
/*
Plugin Name: WPEC Targetpay for WP E-Commerce
Plugin URI: http://scotteuser.com/
Description: This plugin makes TargetPay iDeal payments possible by registering a new WP E-Commerce payment gateway
Version: 1.000
Author: Scott Euser
Author URI: http://scotteuser.com/
Depends: WP e-Commerce
License: GPL2
*/

/**
 * Load the wpsc_merchant class from WP e-Commerce core which our
 * new payment method must extend
 */
require_once plugin_dir_path( __FILE__ ) . '../wp-e-commerce/wpsc-includes/merchant.class.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/wpscTargetpayDebugger.class.php';
require_once plugin_dir_path( __FILE__ ) . 'classes/wpscTargetpay.class.php';

/**
 * Instantiate the wpscTargetpay class so the actions and filters
 * can be set for this plugin to respond to various events from
 * the visitor.
 */
$wpscTargetpay = new wpscTargetpay();

/**
 * In our register gateway, for newer versions of php, we can
 * use array( $this, 'wpscTargetpayForm' ) but for compatability with php 5.3
 * and lower we cannot, so let's use a wrapper function as a callback
 * and pass it back into the class.
 *
 * To remove support for php 5.3 and lower
 * set $gateway['form'] = array( $this, 'wpscTargetpayFormWrapper' );
 * as the callback instead and delete this function
 *
 * @return string the rendered wp e commerce payment type settings form
 */
function wpscTargetpayFormWrapper() {

	global $wpscTargetpay;
	if ( !is_object( $wpscTargetpay ) ) {
		$wpscTargetpay = new wpscTargetpay();
	}
	return $wpscTargetpay->wpscTargetpayForm();

}
