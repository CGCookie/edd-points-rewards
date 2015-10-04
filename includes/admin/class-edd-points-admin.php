<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 *
 * Handles generic Admin functionality and AJAX requests.
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */
class EDD_Points_Admin {
	
	var $model, $scripts, $render;
	
	public function __construct() {	
	
		global $edd_points_model, $edd_points_scripts,
			$edd_points_render, $edd_points_log;
		
		$this->model 	= $edd_points_model;
		$this->scripts 	= $edd_points_scripts;
		$this->render 	= $edd_points_render;
		$this->logs 	= $edd_points_log;
	}
	/**
	 * Add Custom column label for users screen
	 *
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 */
	function edd_points_add_points_column($columns) {
	    $columns['_edd_userpoints'] = __('Points','eddpoints');
	    return $columns;
	}
 
	/**
	 * Add custom column content for users screen
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 */
	function edd_points_show_points_column_content($value, $column_name, $user_id) {
		
		switch ($column_name){
			case '_edd_userpoints' : 
				
			    $points = get_user_meta( $user_id, $column_name, true ); 
				
				if ( '_edd_userpoints' == $column_name)
				{
					$ubalance = !empty($points) ? $points : '0';
				}
				
				$balance = '<div id="edd_points_user_' . $user_id . '_balance">' . $ubalance . '</div>';
				
				// Row actions
				$row = array();
				$row['history'] = '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-points-log&userid=' . $user_id ) . '">' . __( 'History', 'eddpoints' ) . '</a>';
				if ( current_user_can( 'edit_users' ) ) { // Check edit user capability
					$row['adjust'] = '<a href="javascript:void(0)" id="edd_points_user_' . $user_id . '_adjust" class="edd-points-editor-popup" data-userid="' . $user_id . '" data-current="' . $ubalance . '">' . __( 'Adjust', 'eddpoints' ) . '</a>';
				}
				
				$balance .= $this->edd_points_row_actions( $row );
				return $balance;
			    break;
		} 
	}
	
	/**
	 * Make points column sortable for users screen
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.2.1
	 */
	function edd_points_make_points_column_sortable( $columns  ) {

	  	$columns['_edd_userpoints'] = '_edd_userpoints';
   	 	return $columns;
	}
	
	/**
	 * Add Reset points options in bulk action for users screen
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.2.1
	 */
	public function edd_points_add_reset_points_to_bulk_actions( $actions ) {
		
	 	?>
	    <script type="text/javascript">
	        jQuery(document).ready(function($) {
	            jQuery('<option>').val('reset_points').text('<?php _e('Reset Points', 'eddpoints' )?>').appendTo("select[name='action']");
        		jQuery('<option>').val('reset_points').text('<?php _e('Reset Points', 'eddpoints' )?>').appendTo("select[name='action2']");
	        });
	    </script>
    	<?php
	}
	
	/**
	 * Reset points to zero of selected users for users screen
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.2.1
	 */
	public function edd_points_reset_points() {
		
  		// get the action
	  	$wp_list_table = _get_list_table('WP_Users_List_Table');
  		$action = $wp_list_table->current_action();
		
	  	switch( $action ) {
	    	// Perform the action
	    	case 'reset_points':
	    		
	    		if ( !empty( $_GET['users'] ) ) {
					
					foreach ( $_GET['users'] as $key => $user_id )	{
						if( !empty( $user_id ) ) {
							
							$user_points = get_user_meta( $user_id, '_edd_userpoints', true );
							
							update_user_meta( $user_id, '_edd_userpoints', 0 );
							
							//points label
							$pointslable = $this->model->edd_points_get_points_label( $user_points );
							
							$post_data = array(
											'post_title'	=> sprintf( __( '%s for Reset points','eddpoints'), $pointslable ),
											'post_content'	=> sprintf( __('%s Points Reset','eddpoints' ),$pointslable ),
											'post_author'	=>	$user_id
										);
							$log_meta = array(
											'userpoint'		=>	$user_points,
											'events'		=>	'reset_points',
											'operation'		=>	'minus' //add or minus
										);
										
							$this->logs->edd_points_insert_logs( $post_data ,$log_meta );
						}
					}
				}
				
				// Redirect back to users
				$referrer = wp_get_referer();
				wp_redirect( add_query_arg('reset_points_message', true, $referrer) );
				exit;
				
	    		break;
	    	default:
	    		break;
	  	}
	}
 		
	/**
	 * Display message for points reset for users screen
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.2.1
	 */
	public function edd_points_display_reset_points_notice() {
	 
		global $post_type, $pagenow;
				
		if( $pagenow == 'users.php' && isset( $_GET['reset_points_message'] ) && $_GET['reset_points_message'] == '1' ) {
					
		  	$message = __( 'Points reset successfully', 'eddpoints' );
		  	echo "<div class='updated'><p>{$message}</p></div>";
		}
	}		
	
	/**
	 *  Register All need admin menu page
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	
	public function edd_points_admin_menu_pages() {
		
		$edd_points_log = add_submenu_page( 'edit.php?post_type=download', __( 'Points Log', 'eddpoints' ), __( 'Points Log', 'eddpoints' ), 'manage_shop_settings', 'edd-points-log', array( $this, 'edd_points_log' ));
	}
	
	/**
	 * Add Metabox
	 * 
	 * Add metabox for points and rewards
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_metabox() {
		
		 add_meta_box(
            'edd_points_and_rewards',
            __( 'Download Points and Rewards Configuration', 'eddpoints' ),
            array( $this->render, 'edd_points_metabox' ),
            'download','normal', 'default'
        );
		
	}
	
	/**
	 * Save our extra meta box fields
	 *
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 */
	public function edd_points_meta_fields_save( $post_id ) {

		global $post_type;
		
		$post_type_object = get_post_type_object( $post_type );
		
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) // Check Autosave
			|| ( ! isset( $_POST['post_ID'] ) || $post_id != $_POST['post_ID'] ) // Check Revision
			|| ( $post_type != 'download' ) // Check if current post type is supported.
			|| ( ! check_admin_referer( EDD_POINTS_BASENAME, 'at_edd_points_and_rewards_meta_nonce') ) // Check nonce - Security
			|| ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) ) ) { // Check permission
			
			return $post_id;
		}
		
		//check download type is points
		if( edd_get_download_type( $post_id ) == 'points' ) {
			$_POST['_edd_points_earned'] = '0';
		}
		
		//update earned points
		$edd_points_earned = trim($_POST['_edd_points_earned']);
		$edd_points_earned = ( !empty($edd_points_earned) ) ? edd_sanitize_amount( $edd_points_earned ) : $edd_points_earned;
		update_post_meta( $post_id, '_edd_points_earned', $edd_points_earned );
		
		//update maximum discount points
		$edd_points_max_discount = trim( $_POST['_edd_points_max_discount'] );
		$edd_points_max_discount = ( !empty($edd_points_max_discount) ) ? edd_sanitize_amount( $edd_points_max_discount ) : $edd_points_max_discount;
		update_post_meta( $post_id, '_edd_points_max_discount', $edd_points_max_discount );
	}
		
	/**
	 * List of all points
	 *
	 * Handles Function to listing all points
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_log() {
		
		include_once( EDD_POINTS_ADMIN . '/forms/class-edd-points-list.php' );
		
	}
	
	/**
	 * Validate Settings
	 *
	 * Handles to validate settings
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_settings_validate( $input ) {
		
		$edd_settings = get_option('edd_settings');
		
		// Sanitizing earn conversion points
		$input['edd_points_earn_conversion']['points'] 	= trim( $input['edd_points_earn_conversion']['points'] );
		$input['edd_points_earn_conversion']['points'] 	= ( !empty($input['edd_points_earn_conversion']['points']) ) ? edd_sanitize_amount($input['edd_points_earn_conversion']['points']) : $input['edd_points_earn_conversion']['points'];
		
		// Sanitizing earn point conversion rate
		$input['edd_points_earn_conversion']['rate'] 	= trim( $input['edd_points_earn_conversion']['rate'] );
		$input['edd_points_earn_conversion']['rate']	= ( !empty($input['edd_points_earn_conversion']['rate']) ) ? edd_sanitize_amount($input['edd_points_earn_conversion']['rate']) : $input['edd_points_earn_conversion']['rate'];
		
		// Sanitizing redeem points conversion points
		$input['edd_points_redeem_conversion']['points'] = trim( $input['edd_points_redeem_conversion']['points'] );
		$input['edd_points_redeem_conversion']['points'] = ( !empty($input['edd_points_redeem_conversion']['points']) ) ? edd_sanitize_amount($input['edd_points_redeem_conversion']['points']) : $input['edd_points_redeem_conversion']['points']; 
		
		// Sanitizing redeem points conversion rate
		$input['edd_points_redeem_conversion']['rate'] = trim( $input['edd_points_redeem_conversion']['rate'] );
		$input['edd_points_redeem_conversion']['rate'] = !empty($input['edd_points_redeem_conversion']['rate']) ? edd_sanitize_amount($input['edd_points_redeem_conversion']['rate']) : $input['edd_points_redeem_conversion']['rate'];
		
		// Sanitizing buy points conversion rate points
		$input['edd_points_buy_conversion']['points'] = trim( $input['edd_points_buy_conversion']['points'] );
		$input['edd_points_buy_conversion']['points'] = ( !empty($input['edd_points_buy_conversion']['points']) ) ? edd_sanitize_amount($input['edd_points_buy_conversion']['points']) : $input['edd_points_buy_conversion']['points'];
		
		// Sanitizing buy points conversion rate
		$input['edd_points_buy_conversion']['rate'] = trim( $input['edd_points_buy_conversion']['rate'] );
		$input['edd_points_buy_conversion']['rate'] = ( !empty($input['edd_points_buy_conversion']['rate']) ) ? edd_sanitize_amount($input['edd_points_buy_conversion']['rate']) : $input['edd_points_buy_conversion']['rate'];
		
			// Sanitizing selling points conversion rate points
		$input['edd_points_selling_conversion']['points'] = trim( $input['edd_points_selling_conversion']['points'] );
		$input['edd_points_selling_conversion']['points'] = ( !empty($input['edd_points_selling_conversion']['points']) ) ? edd_sanitize_amount($input['edd_points_selling_conversion']['points']) : $input['edd_points_selling_conversion']['points'];
		
		// Sanitizing selling points conversion rate
		$input['edd_points_selling_conversion']['rate'] = trim( $input['edd_points_selling_conversion']['rate'] );
		$input['edd_points_selling_conversion']['rate'] = ( !empty($input['edd_points_selling_conversion']['rate']) ) ? edd_sanitize_amount($input['edd_points_selling_conversion']['rate']) : $input['edd_points_selling_conversion']['rate'];
		
		// Sanitizing maximum cart discount
		$edd_points_cart_max_discount = trim( $input['edd_points_cart_max_discount'] );
		$edd_points_cart_max_discount = ( !empty($edd_points_cart_max_discount) ) ? edd_sanitize_amount( $edd_points_cart_max_discount ) : $edd_points_cart_max_discount;
		
		// Sanitizing maximum per-product points discount
		$edd_points_max_discount = trim( $input['edd_points_max_discount'] );
		$edd_points_max_discount = ( !empty($edd_points_max_discount) ) ? edd_sanitize_amount( $edd_points_max_discount ) : $edd_points_max_discount;
		
		// Sanitizing points earned for account signup
		$edd_points_earned_account_signup = trim( $input['edd_points_earned_account_signup'] );
		$edd_points_earned_account_signup = ( !empty($edd_points_earned_account_signup) ) ? edd_sanitize_amount( $edd_points_earned_account_signup ) : $edd_points_earned_account_signup;
		
		$input['edd_points_earn_conversion'] 		= $this->model->edd_points_escape_slashes_deep( $input['edd_points_earn_conversion'] );
		$input['edd_points_redeem_conversion'] 		= $this->model->edd_points_escape_slashes_deep( $input['edd_points_redeem_conversion'] );
		$input['edd_points_cart_max_discount'] 		= $this->model->edd_points_escape_slashes_deep( $edd_points_cart_max_discount );
		$input['edd_points_max_discount'] 			= $this->model->edd_points_escape_slashes_deep( $edd_points_max_discount );
		$input['edd_points_label'] 					= $this->model->edd_points_escape_slashes_deep( $input['edd_points_label'] );
		$input['edd_points_single_product_messages']= $this->model->edd_points_escape_slashes_deep( $input['edd_points_single_product_messages'], true, true );
		$input['edd_points_cart_messages'] 			= $this->model->edd_points_escape_slashes_deep( $input['edd_points_cart_messages'], true, true );
		$input['edd_points_reedem_cart_messages'] 	= $this->model->edd_points_escape_slashes_deep( $input['edd_points_reedem_cart_messages'], true, true );
		$input['edd_points_bought_guest_messages'] 	= $this->model->edd_points_escape_slashes_deep( $input['edd_points_bought_guest_messages'], true, true );
		$input['edd_points_earned_account_signup'] 	= $this->model->edd_points_escape_slashes_deep( $edd_points_earned_account_signup );
		
		// Check points label settings
		if( ( isset( $input['edd_points_label']['singular'] ) && empty( $input['edd_points_label']['singular'] ) ) 
			|| ( isset( $input['edd_points_label']['plural'] ) && empty( $input['edd_points_label']['plural'] ) ) ) {
			$input['edd_points_label'] = $edd_settings['edd_points_label'];
		}
		
		return $input;
	}
	
	/**
	 * Generate row actions div
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;

		if ( !$action_count )
			return '';

		$out = '<div class="' . ( $always_visible ? 'row-actions-visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		return $out;
	}
	
	/**
	 * Pop Up On Editor
	 *
	 * Includes the pop up on the user listing page
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_user_balance_popup() {
		
		include_once( EDD_POINTS_ADMIN . '/forms/edd-points-user-balance-popup.php' );
	}
	
	/**
	 * AJAX Call for adjust user points
	 *
	 * Handles to adjust user points using ajax
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_adjust_user_points() {
		
		if( isset( $_POST['userid'] ) && !empty( $_POST['userid'] )
			&& isset( $_POST['points'] ) && !empty( $_POST['points'] ) ) { // Check user id and points are not empty
			
				$user_id = $_POST['userid'];
				$current_points = $_POST['points'];
				
				//check number contains minus sign or not
				if( strpos( $current_points, '-' ) !== false ){
					$operation = 'minus';
					$current_points = str_replace( '-', '', $current_points );
					// Update user points to user account
					edd_points_minus_points_from_user( $current_points, $user_id );
				} else {
					$operation = 'add';
					$current_points = str_replace( '+', '', $current_points );
					// Update user points to user account
					edd_points_add_points_to_user( $current_points, $user_id );
				}
				
				// Get user points from user meta
				$ubalance = edd_points_get_user_points( $user_id );
				
				if( isset( $_POST['log'] ) && !empty( $_POST['log'] ) && trim( $_POST['log'], ' ' ) != '' ) { // Check log is not empty
					
					$post_data = array(
											'post_title'	=> $_POST['log'],
											'post_content'	=> $_POST['log'],
											'post_author'	=> $user_id
										);
					$log_meta = array(
											'userpoint'		=>	abs( $current_points ),
											'events'		=>	'manual',
											'operation'		=>	$operation //add or minus
										);
								
					$this->logs->edd_points_insert_logs($post_data ,$log_meta);
				}
				
				echo $ubalance;
				
		} else {
			echo 'error';	
		}
		exit;
	}
	
	/**
	 * AJAX Call for search users
	 *
	 * Handles to search users with fancy select box 
	 * in points log list page
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_search_users() {
	
		$term = urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );
	
		if ( empty( $term ) )
			die();
	
		$default = isset( $_GET['select_default'] ) && !empty( $_GET['select_default'] ) ? $_GET['select_default'] : __( 'Show all customer', 'eddpoints' );
	
		$found_customers = array( '' => $default );
	
		$customer_args = array(
									'fields'			=> 'all',
									'orderby'			=> 'display_name',
									'search'			=> '*' . $term . '*',
									'search_columns'	=> array( 'ID', 'user_login', 'user_email', 'user_nicename' )
								);
				
		$customers_query = new WP_User_Query( $customer_args );
	
		$customers = $customers_query->get_results();
	
		if ( $customers ) {
			foreach ( $customers as $customer ) {
				$found_customers[ $customer->ID ] = $customer->display_name . ' (#' . $customer->ID . ' &ndash; ' . sanitize_email( $customer->user_email ) . ')';
			}
		}
	
		echo json_encode( $found_customers );
		die();
	}
	
	/**
	 * Apply Points to Previous Orders
	 * 
	 * Handles to apply points to previous orders
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_apply_for_previous_orders() {
		
		if( isset( $_GET['points_action'] ) && $_GET['points_action'] == 'apply_points'
			&& isset( $_GET['page'] ) && $_GET['page'] == 'edd-settings' ) {
			
			// perform the action in manageable chunks
			$success_count  = 0;
			$paymentmode     = edd_is_test_mode() ? 'test' : 'live';	
			$paymentargs = array(
									'mode'			=> $paymentmode,
									'fields'		=> 'ids',
									'status'		=> 'publish',
									'posts_per_page'=> '-1',
									'meta_query' 	=> array(
															array(
																'key'     => '_edd_points_order_earned',
																'compare' => 'NOT EXISTS'
															),
									)
								);
			// grab a set of order ids for existing orders with no earned points set
			$payment_ids = edd_get_payments( $paymentargs );
			
			// otherwise go through the results and set the order numbers
			if ( is_array( $payment_ids ) ) {
				
				foreach( $payment_ids as $payment_id ) {
					
					$payment      = get_post( $payment_id );
					$payment_data = edd_get_payment_meta( $payment_id );
					
					// get cartdata from older order
					$cartdata = edd_get_payment_meta_cart_details( $payment_id );
										
					//get cartdata points
					$checkoutpoints = $this->model->edd_points_get_user_checkout_points( $cartdata );
					
					//check checkout points should not empty which has redeemed by buyer
					if( !empty( $checkoutpoints ) ) {
						
						//get user points label
						$pointlable = $this->model->edd_points_get_points_label( $checkoutpoints );
						
						$post_data = array(
												'post_title'	=>	sprintf( __( '%s earned for purchasing the downloads.', 'eddpoints' ), $pointlable ),
												'post_content'	=>	sprintf( __( 'Get %s for purchasing the downloads.', 'eddpoints' ), $pointlable ),
												'post_author'	=>	$payment->post_author
											);
						
						$log_meta = array(
												'userpoint'		=>	$checkoutpoints,
												'events'		=>	'earned_purchase',
												'operation'		=>	'add' //add or minus
											);
									
						//insert entry in log	
						$this->logs->edd_points_insert_logs( $post_data, $log_meta );
						
						//update user points
						edd_points_add_points_to_user( $checkoutpoints, $payment->post_author );
						
						// set order meta, regardless of whether any points were earned, just so we know the process took place
						update_post_meta( $payment_id, '_edd_points_order_earned', $checkoutpoints );
					}
					
					$success_count++;
					
				} //end foreach loop
				
			} //end if check retrive payment ids are array
			
			$redirectargs = array( 
									'post_type'			=>	'download', 
									'page'				=>	'edd-settings', 
									'tab'				=>	'extensions', 
									'settings-updated' 	=>	'apply_points', 
									'success_count' 	=>	$success_count, 
									'points_action' 	=>	false 
								); 
			$redirect_url = add_query_arg( $redirectargs, admin_url( 'edit.php' ) );
			wp_redirect( $redirect_url );
			exit;
			
		} //end if check if there is fulfilling condition proper for applying discount for previous orders
	}
	/**
	 * Save Category Fields
	 * 
	 * Handles to save download category fields
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_save_download_category( $cat_id ){
		
		// check points and rewards is set or not
		if ( isset( $_POST['edd_points_rewards'] ) ) {
			
			// Sanitizing points earned of category
			
			$_POST['edd_points_rewards']['edd_points_earned'] = isset($_POST['edd_points_rewards']['edd_points_earned']) ? trim($_POST['edd_points_rewards']['edd_points_earned']) : '';
			if( !empty($_POST['edd_points_rewards']['edd_points_earned']) ) {
				$_POST['edd_points_rewards']['edd_points_earned'] = edd_sanitize_amount( $_POST['edd_points_rewards']['edd_points_earned'] );
			}
			
			// Sanitizing maximum points discount of category
			$_POST['edd_points_rewards']['edd_points_max_discount'] = isset($_POST['edd_points_rewards']['edd_points_max_discount']) ? trim($_POST['edd_points_rewards']['edd_points_max_discount']) : '';
			if( !empty($_POST['edd_points_rewards']['edd_points_max_discount']) && trim($_POST['edd_points_rewards']['edd_points_max_discount']) != '' ) {
				$_POST['edd_points_rewards']['edd_points_max_discount'] = edd_sanitize_amount( $_POST['edd_points_rewards']['edd_points_max_discount'] );
			}
			
			$eddpointscat = get_option( 'download_category_'.$cat_id );
			
			$cat_keys = array_keys( $_POST['edd_points_rewards'] );
			
			foreach ( $cat_keys as $key ) {
				if ( isset ( $_POST['edd_points_rewards'][$key] ) ) {
					$eddpointscat[$key] = $this->model->edd_points_escape_slashes_deep( trim($_POST['edd_points_rewards'][$key]) );
				}
			}
			// Save the option array.
			update_option( 'download_category_'.$cat_id, $eddpointscat );
		}
	}
	/**
	 * Add Category Column Header
	 * 
	 * Handles to add category column header in
	 * category listing page
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_category_column_header( $columns ) {
		
		$new_columns = array();

		foreach ( $columns as $column_key => $column_title ) {

			$new_columns[ $column_key ] = $column_title;
			
			if ( 'slug' == $column_key ) {
				$new_columns['edd_points_earned'] 		= __( 'Points Earned', 'eddpoints' );
				$new_columns['edd_points_max_discount'] = __( 'Maximum Points Discount', 'eddpoints' );
			}
		}

		return $new_columns;
	}
	/**
	 * Add Category Column Data
	 * 
	 * Handles to add points earned and points max discount 
	 * columns to category listing
	 **/
	public function edd_points_category_column_data( $columns, $column, $term_id ) {
		
		$catpointsoption = get_option( 'download_category_'.$term_id );
		
		switch ( $column ) {
			
			case 'edd_points_earned': 
										$points = isset( $catpointsoption['edd_points_earned'] ) && $catpointsoption['edd_points_earned'] != ''
														? $this->model->edd_points_escape_attr( $catpointsoption['edd_points_earned'] ) : '&mdash;';
										echo $points;
										break;
			
			case 'edd_points_max_discount': 
			
										$maxdiscount = isset( $catpointsoption['edd_points_max_discount'] ) && $catpointsoption['edd_points_max_discount'] != ''
															? $this->model->edd_points_escape_attr( $catpointsoption['edd_points_max_discount'] ) : '&mdash;';
										echo $maxdiscount;
										break;
			
		} //end switch
	}
	
	/**
	 * Display Points
	 * 
	 * Handles to display points in order details
	 **/
	public function edd_points_view_points( $payment_id ) {
		
		$points_earned = get_post_meta( $payment_id, '_edd_points_order_earned', true );
		$points_redeemed = get_post_meta( $payment_id, '_edd_points_order_redeemed', true );
		
		// Check earned points is not empty or redeemed points is not empty
		if( !empty( $points_earned ) || !empty( $points_redeemed ) ) {
			
			$points_earned = !empty( $points_earned ) ? $points_earned : __( 'None', 'eddpoints' );
			$points_redeemed = !empty( $points_redeemed ) ? $points_redeemed : __( 'None', 'eddpoints' );
		
			echo '<div class="edd-order-fees edd-admin-box-inside">
					<p class="strong">' . __( 'Points', 'eddpoints' ) . '</p>
					<ul class="edd-payment-fees">
						<li><span class="earned-label">' . __( 'Earned:', 'eddpoints' ) . '</span> ' . '<span class="right">' . $points_earned . '</span></li>
						<li><span class="redeemed-label">' . __( 'Redeemed:', 'eddpoints' ) . '</span> ' . '<span class="right">' . $points_redeemed . '</span></li>
					</ul>
				</div>';
		}
	}
	/**
	 * Receipt Template Tags
	 * 
	 * Handles to add template tags below purchase receipt
	 * template
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_recipt_template_tags_description( $tags ){ 
		
		$tags .= '<br />{points_earned} - ' . __( 'Adds a points earned by buyer for this purchase', 'eddpoints' );
		$tags .= '<br />{points_redeemed} - ' . __( 'Adds a points redeemed by buyer for this purchase', 'eddpoints' );
		
		return $tags;
	}
	/**
	 * Replace Email Template Tags in Preview
	 * 
	 * Handles to replace email template tags in preview
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_replace_preview_emails_template_tags( $message ) {
		
		//replace earned points
		$message 	= str_replace( '{points_earned}', '100', $message  );
	
		//replace redeemed points
		$message 	= str_replace( '{points_redeemed}', '200', $message  );
		
		//return message after replacing then points data
		return $message;
	}
	/**
	 * Add Download Type
	 * 
	 * Handles to add download type to
	 * easy digital downloads type
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.1.0
	 **/
	public function edd_points_download_types( $types ) {
		
		//add points download type to download types combo box in metabox
		$types['points']  = __( 'Points', 'eddpoints' );
		
		return $types;
	}
	
	/**
	 * Sorting With Number Of Points
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.2.1
	 */
	public function edd_points_manage_points_column_sorting( $query ) {
		
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ! function_exists( 'get_current_screen' ) ) return;
		
		$screen = get_current_screen();
		if ( $screen === NULL || $screen->id != 'users' ) return;
		
		if ( isset( $query->query_vars['orderby'] ) && $query->query_vars['orderby'] == '_edd_userpoints' ) {
			
			global $wpdb;
			
			$edd_points_meta = $query->query_vars['orderby'];
			
			$order = 'ASC';
			if ( isset( $query->query_vars['order'] ) )
				$order = $query->query_vars['order'];
			
			$query->query_from .= "
				LEFT JOIN {$wpdb->usermeta} 
				ON ({$wpdb->users}.ID = {$wpdb->usermeta}.user_id AND {$wpdb->usermeta}.meta_key = '{$edd_points_meta}')";
			
			$query->query_orderby = "ORDER BY {$wpdb->usermeta}.meta_value+0 {$order} ";
		}
	}
	
	/**
	 * Adding Hooks
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		//add admin menu pages
		add_action ( 'admin_menu', array($this,'edd_points_admin_menu_pages' ));
		
		//add filter to add settings
		add_filter( 'edd_settings_extensions', array( $this->model, 'edd_points_settings') );
		
		//add filter to extension settings field
		add_filter( 'edd_settings_extensions_sanitize', array( $this, 'edd_points_settings_validate' ) );
		
		// Add meta fields
		add_action( 'add_meta_boxes', array( $this, 'edd_points_metabox' ) );
		
		// Add our meta fields to the EDD save routine
		add_action( 'save_post', array( $this, 'edd_points_meta_fields_save' ) );
		
		// mark up for popup
		add_action( 'admin_footer-users.php', array( $this,'edd_points_user_balance_popup' ) );
		
		// Add Cusom column title
		add_filter('manage_users_columns',  array( $this,'edd_points_add_points_column'));
		
		// Add Cusom column Content
		add_action('manage_users_custom_column',   array( $this,'edd_points_show_points_column_content'), 10, 3);
		
		// Add filter to make points column sortable
		add_filter( 'manage_users_sortable_columns', array( $this,'edd_points_make_points_column_sortable') );
		
		// Add actions to add reset points in bulk actions
		add_action( 'admin_footer-users.php', array( $this,'edd_points_add_reset_points_to_bulk_actions') );
		
		// Add actions to reset points when reset points bulk actions performed
		add_action('load-users.php', array( $this, 'edd_points_reset_points' ) );
		
		// Add actions to display admin notices for reset points
		add_action('admin_notices', array( $this, 'edd_points_display_reset_points_notice' ) );
		
		// Add Custom field to user profile
		add_action( 'profile_personal_options', array( $this->render ,'edd_points_add_custom_user_profile_fields' ));
		
		//AJAX Call for adjust user points
		add_action( 'wp_ajax_edd_points_adjust_user_points', array( $this, 'edd_points_adjust_user_points' ) );
		add_action( 'wp_ajax_nopriv_edd_points_adjust_user_points', array( $this, 'edd_points_adjust_user_points' ) );
		
		//AJAX Call to search users
		add_action( 'wp_ajax_edd_points_search_users', array( $this, 'edd_points_search_users' ) );
		add_action( 'wp_ajax_nopriv_edd_points_search_users', array( $this, 'edd_points_search_users' ) );
		
		// add action to apply points to previous orders
		add_action( 'admin_init', array( $this, 'edd_points_apply_for_previous_orders' ) );
		
		//add action to add fields in EDD category fields
		add_action( 'download_category_add_form_fields', array( $this->render, 'edd_points_download_category_add_fields_html' ) );
		add_action( 'download_category_edit_form_fields', array( $this->render, 'edd_points_download_category_edit_fields_html' ) );
		
		//save download category fields
		add_action( 'create_download_category', array( $this, 'edd_points_save_download_category'), 10, 2 );
		add_action( 'edited_download_category', array( $this, 'edd_points_save_download_category'), 10, 2 );
		
		//add filter to add category column header
		add_filter( 'manage_edit-download_category_columns', array( $this, 'edd_points_category_column_header' ) );
		//add filter to add category column data
		add_filter( 'manage_download_category_custom_column', array( $this, 'edd_points_category_column_data' ), 10, 3 );
		
		// Add action to view points in order details page
		add_action( 'edd_view_order_details_totals_before', array( $this, 'edd_points_view_points' ) );
		
		//Add filter to add tags to email template description
		add_filter( 'edd_purchase_receipt_template_tags_description', array( $this, 'edd_points_recipt_template_tags_description' ) );
		add_filter( 'edd_sale_notification_template_tags_description', array( $this, 'edd_points_recipt_template_tags_description' ) );
		
		//Add filter to replace the points template tage for email template in preview
		add_filter( 'edd_email_preview_template_tags', array( $this, 'edd_points_replace_preview_emails_template_tags' ) );
		
		//add filter to add edd download product type
		add_filter( 'edd_download_types', array( $this, 'edd_points_download_types' ) );
		
		// Add filter to make points column sortable
		add_action( 'pre_user_query', array( $this, 'edd_points_manage_points_column_sorting' ), 1 );
	}
}
?>