<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Scripts Class
 *
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */
class EDD_Points_Scripts{
	
	public function __construct() {
		
	}
	
	/**
	 * Enqueue Admin Styles
	 * 
	 * Handles to enqueue styles for admin side
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_admin_styles( $hook_suffix ) {
		
		global $current_screen;
		
		$page_screen_id 	= isset($current_screen->id) ? $current_screen->id : '';
		$pages_hook_suffix 	= array( 'users.php', 'download_page_edd-points-log' );
		
		//Check pages when you needed
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {
			
			//css directory url
			$css_dir = EDD_PLUGIN_URL . 'assets/css/';
		
			// Use minified libraries if SCRIPT_DEBUG is turned off
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			
			wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), EDD_VERSION );
			wp_enqueue_style( 'jquery-chosen' );
			
			wp_register_style( 'edd-points-admin-styles', EDD_POINTS_URL . 'includes/css/style-admin.css', array(), EDD_POINTS_VERSION );
			wp_enqueue_style( 'edd-points-admin-styles' );
		}
		
		// Registring admin style in download category page
		if( $hook_suffix == 'edit-tags.php' && $page_screen_id == 'edit-download_category' ) {
			wp_register_style( 'edd-points-admin-styles', EDD_POINTS_URL . 'includes/css/style-admin.css', array(), EDD_POINTS_VERSION );
			wp_enqueue_style( 'edd-points-admin-styles' );
		}
	}
	
	/**
	 * Enqueue Admin Scripts
	 * 
	 * Handles to enqueue scripts for admin
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_admin_scripts( $hook_suffix ) {
		
		$pages_hook_suffix = array( 'users.php', 'download_page_edd-points-log' );
		
		//Check pages when you needed
		if( in_array( $hook_suffix, $pages_hook_suffix ) ) {
			
			//js directory url
			$js_dir = EDD_PLUGIN_URL . 'assets/js/';
			
			// Use minified libraries if SCRIPT_DEBUG is turned off
			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			
			wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery.min.js', array( 'jquery' ), EDD_VERSION, true );
			wp_enqueue_script( 'jquery-chosen' );
			
			wp_register_script( 'edd-points-ajax-chosen-scripts', EDD_POINTS_URL . 'includes/js/ajax-chosen.jquery.js', array( 'jquery' ) , EDD_POINTS_VERSION, true );
			wp_enqueue_script( 'edd-points-ajax-chosen-scripts' );
			
			wp_register_script( 'edd-points-admin-scripts', EDD_POINTS_URL . 'includes/js/edd-points-admin.js', array( 'jquery', 'jquery-chosen', 'jquery-ui-sortable' ) , EDD_POINTS_VERSION, true );
			wp_enqueue_script( 'edd-points-admin-scripts' );
			
			wp_localize_script( 'edd-points-admin-scripts', 'Edd_Points_Admin', array(
																						'update_balance'		=> __( 'Update Balance', 'eddpoints' ),
																						'processing_balance'	=> __( 'Processing...', 'eddpoints' ),
																						'prev_order_apply_confirm_message'	=>	__( 'Are you sure you want to apply points to all previous orders that have not already had points generated? This cannot be reversed! Note that this can take some time in shops with a large number of orders, if an error occurs, simply Apply Points again to continue the process.', 'eddpoints' )
																					) );
		}
	}
	
	/**
	 * Enqueue Public Scripts
	 * 
	 * Handles to enqueue scripts for public side
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_public_scripts() {
		
		global $edd_options, $post;
		
		wp_register_script( 'edd-points-public-script', EDD_POINTS_URL . 'includes/js/edd-points-public.js', array( 'jquery' ), null );
		wp_localize_script( 'edd-points-public-script', 'EDDPoints', array( 
																			'ajaxurl' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) )
																		) );
	}

	/**
	 * Enqueue Styles
	 *
	 * Loads the stylesheets for public side.
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */	
	public function edd_points_public_styles() {
		
		wp_register_style( 'edd-points-public-style', EDD_POINTS_URL . 'includes/css/style-public.css', array(), EDD_POINTS_VERSION );
		wp_enqueue_style( 'edd-points-public-style' );
	}
	
	/**
	 * Display button in post / page & custom post types container
	 *
	 * Handles to display button in post / page & custom post types container
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_shortcode_display_button( $buttons ) {
	 
		array_push( $buttons, "|", "edd_points_log" );
		return $buttons;
	}
	
	/**
	 * Include js for add button in post / page & custom post types container
	 *
	 * Handles to include js for add button in post / page & custom post types container
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_shortcode_button($plugin_array) {
	 
		$plugin_array['edd_points_log'] = EDD_POINTS_URL . 'includes/js/edd-points-shortcode.js&ver='.EDD_POINTS_VERSION;
		return $plugin_array;
	}
	
	/**
	 * Display button in post / page & custom post types container
	 * 
	 * Handles to display button in post / page & custom post types container
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_add_shortcode_button() {
		
		if( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) ) {
			add_filter( 'mce_external_plugins', array( $this, 'edd_points_shortcode_button' ) );
   			add_filter( 'mce_buttons', array( $this, 'edd_points_shortcode_display_button' ) );
		}
		
	}
	
	/**
	 * Adding Hooks
	 *
	 * Adding proper hooks for the scripts.
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function add_hooks() {

		//add styles for back end
		add_action( 'admin_enqueue_scripts', array($this, 'edd_points_admin_styles') );
		
		//add script to back side for Points and Rewards
		add_action( 'admin_enqueue_scripts', array($this, 'edd_points_admin_scripts') );
		
		//add script to front side for Points and Rewards
		add_action( 'wp_enqueue_scripts', array( $this, 'edd_points_public_scripts' ) );
		
		//add styles for front end
		add_action( 'wp_enqueue_scripts', array( $this, 'edd_points_public_styles' ) );
		
		// add filters for add add button in post / page container
		//add_action( 'admin_init', array( $this, 'edd_points_add_shortcode_button' ) );
		
	}
}
?>