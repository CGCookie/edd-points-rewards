<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Register Post Type
 *
 * Register Custom Post Type for managing registered taxonomy
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 *
 */
function edd_points_register_post_type() {
	
	$labels = array(
					    'name'				=> __('Points','eddpoints'),
					    'singular_name' 	=> __('Point','eddpoints'),
					    'add_new' 			=> __('Add New','eddpoints'),
					    'add_new_item' 		=> __('Add New Point','eddpoints'),
					    'edit_item' 		=> __('Edit Point','eddpoints'),
					    'new_item' 			=> __('New Point','eddpoints'),
					    'all_items' 		=> __('All Points','eddpoints'),
					    'view_item' 		=> __('View Point','eddpoints'),
					    'search_items' 		=> __('Search Point','eddpoints'),
					    'not_found' 		=> __('No Points found','eddpoints'),
					    'not_found_in_trash'=> __('No Points found in Trash','eddpoints'),
					    'parent_item_colon' => '',
					    'menu_name' => __('Points','eddpoints'),
					);
	$args = array(
				    'labels'			=> $labels,
				    'public' 			=> false,
				    'query_var' 		=> false,
				    'rewrite' 			=> false,
				    'capability_type' 	=> EDD_POINTS_LOG_POST_TYPE,
				    'hierarchical' 		=> false,
				    'supports' 			=> array( 'title' )
			  );
	
	register_post_type( EDD_POINTS_LOG_POST_TYPE, $args );
}

//creating custom post type
add_action( 'init', 'edd_points_register_post_type', 1 ); //creating custom post

?>