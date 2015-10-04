<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Uninstall
 *
 * Does delete the created tables and all the plugin options
 * when uninstalling the plugin
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */

// check if the plugin really gets uninstalled 
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();
	
global $edd_options;

// check remove data on uninstall is checked, if yes then delete plugin data
if( edd_get_option( 'uninstall_on_delete' ) ) {		
	
	//delete custom main post data
	$queryargs = array( 'post_type' => 'eddpointslog', 'post_status' => 'any' , 'numberposts' => '-1' );
	$queryargsdata = get_posts( $queryargs );
	
	//delete all points log posts
	foreach ($queryargsdata as $post) {
		wp_delete_post($post->ID,true);
	}
	
	//get all user which meta key '_edd_userpoints' not equal to empty
	$all_user = get_users( array( 'meta_key' => '_edd_userpoints', 'meta_value'	=> '', 'meta_compare' => '!=' ) );
	
	foreach ( $all_user as $key => $value ){
		delete_user_meta( $value->ID, '_edd_userpoints' );
	}

	// Unset all option values from edd global array to delete it
	unset( $edd_options['edd_points_earn_conversion'] );
	unset( $edd_options['edd_points_redeem_conversion'] );
	unset( $edd_options['edd_points_buy_conversion'] );
	unset( $edd_options['edd_points_selling_conversion'] );
	unset( $edd_options['edd_points_cart_max_discount'] );
	unset( $edd_options['edd_points_max_discount'] );
	unset( $edd_options['edd_points_label'] );
	unset( $edd_options['edd_points_single_product_messages'] );
	unset( $edd_options['edd_points_cart_messages'] );
	unset( $edd_options['edd_points_reedem_cart_messages'] );				
	unset( $edd_options['edd_points_earn_guest_messages'] );
	unset( $edd_options['edd_points_bought_guest_messages'] );
	unset( $edd_options['edd_points_earned_account_signup'] );
	
	// update edd_settings option
	update_option( 'edd_settings', $edd_options );
}