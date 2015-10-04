<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcodes Class
 *
 * Handles shortcodes functionality of plugin
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */
class EDD_Points_Shortcodes {
	
	public $model, $render;
	
	public function __construct(){
		
		global $edd_points_render,$edd_points_model;
		
		$this->render = $edd_points_render;
		$this->model = $edd_points_model;
	}
	
	/**
	 * Show All Points and Rewards Buttons
	 * 
	 * Handles to show all Points and Rewards buttons on the viewing page
	 * whereever user put shortcode
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_history( $content ) {
		
		global $edd_options;
		
		//check user is logged in or not
		if( is_user_logged_in() ) {
			//show user logs list
			$content .= $this->render->edd_points_user_log_list();
		} else {
			//get plural label for points from settings page
			$plurallabel = isset( $edd_options['edd_points_label']['plural'] ) ? $edd_options['edd_points_label']['plural'] : '';
			//if user is not logged in then show below message
			$content .= sprintf( __( 'Sorry, You have not earned any %s yet.', 'eddpoints' ), $plurallabel );
		}
		return $content;
	}
	
	/**
	 * Adding Hooks
	 *
	 * Adding hooks for calling shortcodes.
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function add_hooks() {
		
		//add shortcode to show all Points and Rewards buttons
		add_shortcode( 'edd_points_history', array( $this, 'edd_points_history' ) );
	}
}
?>