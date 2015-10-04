<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Logging Class
 *
 * Handles all the different functionalities of logs
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */
class EDD_Points_Logging {
	
	public function __construct(){
		global  $edd_points_scripts;
	}
	
	/**
	 * Stores a log entry
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	function edd_points_insert_logs( $log_data = array(), $log_meta = array() ) {
		
		global $current_user;
		
		$log_id = 0;
		
		$logspoints =  abs( $log_meta['userpoint'] );
		//if user should enter user points more than zero
		if( !empty( $logspoints ) ) {
		
			$defaults = array(
				'post_type' 	=> EDD_POINTS_LOG_POST_TYPE,
				'post_status'	=> 'publish',
				'post_parent'	=> 0,
				'post_title'	=> '',
				'post_content'	=> ''
			);
			
			$args = wp_parse_args( $log_data, $defaults );
			
			//check there is operation type is set or not
			if( isset( $log_meta['operation'] ) && $log_meta['operation'] == 'minus' ) {
				$log_meta['userpoint'] = '-'.$log_meta['userpoint'];
			} else {
				$log_meta['userpoint'] = '+'.$log_meta['userpoint'];
			}
		
			// Store the log entry
			$log_id = wp_insert_post( $args );
	
			// Set log meta, if any
			if ( $log_id && ! empty( $log_meta ) ) {
				foreach ( (array) $log_meta as $key => $meta ) {
					update_post_meta( $log_id, '_edd_log_' . sanitize_key( $key ), $meta );
				}
			}
		}
		
		return $log_id;
	}
	
	/**
	 * Update and existing log item
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	function edd_points_update_logs( $log_data = array(), $log_meta = array() ) {
		
		$defaults = array(
			'post_type' 	=> EDD_POINTS_LOG_POST_TYPE,
			'post_status'	=> 'publish',
			'post_parent'	=> 0
		);

		$args = wp_parse_args( $log_data, $defaults );

		// Store the log entry
		$log_id = wp_update_post( $args );

		if ( $log_id && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				if ( ! empty( $meta ) )
					update_post_meta( $log_id, '_edd_log_' . sanitize_key( $key ), $meta );
			}
		}

	}
	
}