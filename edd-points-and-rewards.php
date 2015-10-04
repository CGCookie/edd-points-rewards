<?php
/**
 * Plugin Name: Easy Digital Downloads - Points and Rewards
 * Plugin URI: https://easydigitaldownloads.com/extensions/points-rewards/
 * Description: With this extension you can reward customers for purchases and other actions with points which can be redeemed for discounts.
 * Version: 1.3.3
 * Author: WPWeb
 * Author URI: http://wpweb.co.in
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $wpdb;

/**
 * Basic plugin definitions
 * 
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */
if( !defined( 'EDD_POINTS_VERSION' ) ) {
	define( 'EDD_POINTS_VERSION', '1.3.3' ); // Plugin version
}
if( !defined( 'EDD_POINTS_URL' ) ) {
	define( 'EDD_POINTS_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}
if( !defined( 'EDD_POINTS_DIR' ) ) {
	define( 'EDD_POINTS_DIR', dirname( __FILE__ ) ); // plugin dir
}
if( !defined( 'EDD_POINTS_IMG_URL' ) ) {
	define( 'EDD_POINTS_IMG_URL', EDD_POINTS_URL . 'includes/images' ); // plugin url
}
if( !defined( 'EDD_POINTS_ADMIN' ) ) {
	define( 'EDD_POINTS_ADMIN', EDD_POINTS_DIR . '/includes/admin' ); // plugin admin dir
}
if( !defined( 'EDD_POINTS_LOG_POST_TYPE' ) ) {
	define( 'EDD_POINTS_LOG_POST_TYPE', 'eddpointslog'); //post type for points log
}
if( !defined( 'EDD_POINTS_BASENAME' ) ) {
	define( 'EDD_POINTS_BASENAME', basename( EDD_POINTS_DIR ) ); //points and rewards basename
}

/**
 * Admin notices
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.3.0
*/
function edd_points_admin_notices() {
	
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		
		echo '<div class="error">';
		echo "<p><strong>" . __( 'Easy Digital Downloads needs to be activated to be able to use the Points and Rewards.', 'eddpoints' ) . "</strong></p>";
		echo '</div>';
	}
}

/**
 * Check Easy Digital Downloads Plugin
 *
 * Handles to check Easy Digital Downloads plugin
 * if not activated then deactivate our plugin
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.3.0
 */
function edd_points_check_activation() {
	
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		// is this plugin active?
		if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
			// deactivate the plugin
	 		deactivate_plugins( plugin_basename( __FILE__ ) );
	 		// unset activation notice
	 		unset( $_GET[ 'activate' ] );
	 		// display notice
	 		add_action( 'admin_notices', 'edd_points_admin_notices' );
		}
	}
}
//Check Easy Digital Downloads plugin is Activated or not
add_action( 'admin_init', 'edd_points_check_activation' );

// loads the Misc Functions file
require_once ( EDD_POINTS_DIR . '/includes/edd-points-misc-functions.php' );

//Add post type page for points functionality.
require_once( EDD_POINTS_DIR . '/includes/edd-points-post-types.php');

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'edd_points_install' );

/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * stest default values for the plugin options.
 * 
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.1.2
 */
function edd_points_install() {
	
	global $wpdb, $edd_options;
	
	//register post type
	edd_points_register_post_type();
	
	//IMP Call of Function
	//Need to call when custom post type is being used in plugin
	flush_rewrite_rules();
	
	$udpopt = false;
	//check earning points conversion not set
	if( !isset( $edd_options['edd_points_earn_conversion'] ) ) {
		$edd_options['edd_points_earn_conversion'] = array( 'points' => __( '1', 'eddpoints' ), 'rate' => __( '1', 'eddpoints' ) );
		$udpopt = true;
	}//end if
	
	//check redeem conversion is not set
	if( !isset( $edd_options['edd_points_redeem_conversion'] ) ) {
		$edd_options['edd_points_redeem_conversion'] = array( 'points' => __( '100', 'eddpoints' ), 'rate' => __( '1', 'eddpoints' ) );
		$udpopt = true;
	} //end if
	
	//check buy conversion rate is not set
	if( !isset( $edd_options['edd_points_buy_conversion'] ) ) {
		$edd_options['edd_points_buy_conversion'] = array( 'points' => __( '100', 'eddpoints' ), 'rate' => __( '1', 'eddpoints' ) );
		$udpopt = true;
	}//end if
	
	//check selling conversion rate is not set
	if( !isset( $edd_options['edd_points_selling_conversion'] ) ) {
		$edd_options['edd_points_selling_conversion'] = array( 'points' => __( '0.00', 'eddpoints' ), 'rate' => __( '0.00', 'eddpoints' ) );
		$udpopt = true;
	}//end if
	
	//check cart maximum discount is not set
	if( !isset( $edd_options['edd_points_cart_max_discount'] ) ) {
		$edd_options['edd_points_cart_max_discount'] = '';
		$udpopt = true;
	} //end if
	
	//check max discount is not set
	if( !isset( $edd_options['edd_points_max_discount'] ) ) {
		$edd_options['edd_points_max_discount'] = '';
		$udpopt = true;
	} //end if
	
	//check points label is not set
	if( !isset( $edd_options['edd_points_label'] ) ) {
		$edd_options['edd_points_label'] = array( 'singular' => __( 'Point', 'eddpoints' ), 'plural' => __( 'Points', 'eddpoints' ) );
		$udpopt = true;
	} //end if
	
	//check points single product message
	if( !isset( $edd_options['edd_points_single_product_messages'] ) ) {
		$edd_options['edd_points_single_product_messages'] = sprintf(__( 'Purchase this product now and earn %s!','eddpoints'),'<strong>{points}</strong> {points_label}');
		$udpopt = true;
	} //end if
	
	//check points cart message
	if( !isset( $edd_options['edd_points_cart_messages'] ) ) {
		$edd_options['edd_points_cart_messages'] = sprintf(__('Complete your order and earn %s for a discount on a future purchase.','eddpoints'),'<strong>{points}</strong> {points_label}');
		$udpopt = true;
	} //end if
	
	//check redeem cart message
	if( !isset( $edd_options['edd_points_reedem_cart_messages'] ) ) {
		$edd_options['edd_points_reedem_cart_messages'] = sprintf(__('Use %s for a %s discount on this order.','eddpoints'),'<strong>{points}</strong> {points_label}','<strong>{points_value}</strong>');
		$udpopt = true;
	} //end if
	
	//check earn guest message
	if( !isset( $edd_options['edd_points_earn_guest_messages'] ) ) {
		$edd_options['edd_points_earn_guest_messages'] = sprintf(__( 'You need to register an account in order to earn %s.','eddpoints' ),'<strong>{points}</strong> {points_label}');
		$udpopt = true;
	} //end if
	
	//check bought guest message
	if( !isset( $edd_options['edd_points_bought_guest_messages'] ) ) {
		$edd_options['edd_points_bought_guest_messages'] = sprintf(__( 'You need to register an account in order to fund %s into your account.','eddpoints' ),'<strong>{points}</strong> {points_label}');
		$udpopt = true;
	} //end if
	
	//check earning points for accoutn signup
	if( !isset( $edd_options['edd_points_earned_account_signup'] ) ) {
		$edd_options['edd_points_earned_account_signup'] = '500';
		$udpopt = true;
	} //end if
	
	//check need to update the defaults value to options
	if( $udpopt == true ) { // if any of the settings need to be updated
		update_option( 'edd_settings', $edd_options );
	}
}

/**
 * Add plugin action links
 *
 * Adds a Settings, Docs link to the plugin list.
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.3.0
 */
function edd_points_add_plugin_links( $links ) {
	$plugin_links = array(
		'<a href="edit.php?post_type=download&page=edd-settings&tab=extensions">' . __( 'Settings', 'eddpoints' ) . '</a>',
		'<a href="http://wpweb.co.in/documents/edd-points-and-rewards/">' . __( 'Docs', 'eddpoints' ) . '</a>'		
	);

	return array_merge( $plugin_links, $links );
}
	
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'edd_points_add_plugin_links' );	

/**
 * Load Text Domain
 * 
 * This gets the plugin ready for translation.
 * 
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.3.2
 */
function edd_points_load_text_domain() {
	
	// Set filter for plugin's languages directory
	$edd_points_lang_dir	= dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$edd_points_lang_dir	= apply_filters( 'edd_points_languages_directory', $edd_points_lang_dir );
	
	// Traditional WordPress plugin locale filter
	$locale	= apply_filters( 'plugin_locale',  get_locale(), 'eddpoints' );
	$mofile	= sprintf( '%1$s-%2$s.mo', 'eddpoints', $locale );
	
	// Setup paths to current locale file
	$mofile_local	= $edd_points_lang_dir . $mofile;
	$mofile_global	= WP_LANG_DIR . '/' . EDD_POINTS_BASENAME . '/' . $mofile;
	
	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/edd-points-and-rewards folder
		load_textdomain( 'eddpoints', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) { // Look in local /wp-content/plugins/edd-points-and-rewards/languages/ folder
		load_textdomain( 'eddpoints', $mofile_local );
	} else { // Load the default language files
		load_plugin_textdomain( 'eddpoints', false, $edd_points_lang_dir );
	}
}

//add action to load plugin
add_action( 'plugins_loaded', 'edd_points_plugin_loaded' );

/**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded 
 * successfully
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.3.0
 **/ 
function edd_points_plugin_loaded() {
		
	//check easy digital downloads is activated or not
	if( class_exists( 'Easy_Digital_Downloads' ) ) {
		
		// load first text domain
		edd_points_load_text_domain();	
	
		//check EDD_License class is exist
		if( class_exists( 'EDD_License' ) ) {
			
			// Instantiate the licensing / updater. Must be placed in the main plugin file
			$license = new EDD_License( __FILE__, 'Points and Rewards', EDD_POINTS_VERSION, 'WPWeb' );
		}		
		
		/**
		 * Deactivation Hook
		 * 
		 * Register plugin deactivation hook.
		 * 
		 * @package Easy Digital Downloads - Points and Rewards
		 * @since 1.0.0
		 */
		register_deactivation_hook( __FILE__, 'edd_points_uninstall' );		
		
		/**
		 * Plugin Setup (On Deactivation)
		 * 
		 * Delete  plugin options.
		 * 
		 * @package Easy Digital Downloads - Points and Rewards
		 * @since 1.0.0
		 */
		function edd_points_uninstall() {
			
			global $wpdb;
			
			//IMP Call of Function
			//Need to call when custom post type is being used in plugin
			flush_rewrite_rules();						
		}
		
		/**
		 * Includes Files
		 * 
		 * Includes some required files for plugin
		 * 
		 * @package Easy Digital Downloads - Points and Rewards
		 * @since 1.0.0
		 */
		global $edd_points_model, $edd_points_scripts,
			$edd_points_render, $edd_points_shortcodes,
			$edd_points_public, $edd_points_admin,
			$edd_points_log;		
		
		//Pagination Class
		require_once( EDD_POINTS_DIR . '/includes/class-edd-points-pagination-public.php' ); // front end pagination class
		
		//Model Class for generic functions
		require_once( EDD_POINTS_DIR . '/includes/class-edd-points-model.php' );
		$edd_points_model = new EDD_Points_Model();
		
		//Scripts Class for scripts / styles
		require_once( EDD_POINTS_DIR . '/includes/class-edd-points-scripts.php' );
		$edd_points_scripts = new EDD_Points_Scripts();
		$edd_points_scripts->add_hooks();
		
		//Renderer Class for HTML
		require_once( EDD_POINTS_DIR . '/includes/class-edd-points-renderer.php' );
		$edd_points_render = new EDD_Points_Renderer();
		
		//Shortcodes class for handling shortcodes
		require_once( EDD_POINTS_DIR . '/includes/class-edd-points-shortcodes.php' );
		$edd_points_shortcodes = new EDD_Points_Shortcodes();
		$edd_points_shortcodes->add_hooks();		
		
		//Insert logs for points functionality.
		require_once( EDD_POINTS_DIR . '/includes/class-edd-points-log.php');
		$edd_points_log = new EDD_Points_Logging();
		
		//Public Class for public functionlities
		require_once( EDD_POINTS_DIR . '/includes/class-edd-points-public.php' );
		$edd_points_public = new EDD_Points_Public();
		$edd_points_public->add_hooks();
		
		//Admin Pages Class for admin site
		require_once( EDD_POINTS_ADMIN . '/class-edd-points-admin.php' );
		$edd_points_admin = new EDD_Points_Admin();
		$edd_points_admin->add_hooks();
		
	}//end if to check class Easy_Digital_Downloads is exist or not
	
} //end if of plugin loaded
?>