<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Public Pages Class
 *
 * Handles all the different features and functions
 * for the front end pages.
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */
class EDD_Points_Public	{
	
	var $render,$model,$logs;
	
	public function __construct() {
		
		global $edd_points_model, $edd_points_render, $edd_points_log;
		
		$this->render 	= $edd_points_render;
		$this->logs 	= $edd_points_log;
		$this->model 	= $edd_points_model;
	}
	
	/**
	 * Add points for signup
	 *
	 * Handles to add users points for signup
	 *  
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 */
	public function edd_points_add_point_for_signup( $user_id ) {
		
		global $edd_options;
		
		//update users points for signup
		edd_points_add_points_to_user( $edd_options['edd_points_earned_account_signup'], $user_id );
		
		//points label
		$pointslable = $this->model->edd_points_get_points_label( $edd_options['edd_points_earned_account_signup'] );
		
		$post_data = array(
						'post_title'	=> sprintf( __( '%s for Signup','eddpoints'), $pointslable ),
						'post_content'	=> sprintf( __('Get %s for signing up new account','eddpoints' ),$pointslable ),
						'post_author'	=>	$user_id
					);
		$log_meta = array(
						'userpoint'		=>	$edd_options['edd_points_earned_account_signup'],
						'events'		=>	'signup',
						'operation'		=>	'add' //add or minus
					);
					
		$this->logs->edd_points_insert_logs( $post_data ,$log_meta );
	}
	
	/**
	 * Add points for purchase
	 * 
	 * Handles to add points for purchases
	 *
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 */
	public function edd_points_add_point_for_complete_purchase( $payment_id ) {
		
		global $edd_options, $current_user;
		
		//get payment data
		$paymentdata	=	edd_get_payment_meta( $payment_id );
		$userdata		=	edd_get_payment_meta_user_info( $payment_id );
		$user_id		=	isset( $userdata['id'] ) && !empty( $userdata['id'] ) ? $userdata['id'] : 0;
		
		//get discount towards points
		$gotdiscount = $this->model->edd_points_get_payment_discount( $payment_id );
		
		//check user has redeemed points or not & user_id should not empty
		if( isset( $gotdiscount ) && !empty( $gotdiscount ) && !empty( $user_id ) ) {
			
			//get discounte price from points
			$discountedpoints = $this->model->edd_points_calculate_points( $gotdiscount );
			
			//update user points
			edd_points_minus_points_from_user( $discountedpoints, $user_id );
			
			//points label
			$pointslable = $this->model->edd_points_get_points_label( $discountedpoints );
			
			//record data logs for redeem for purchase
			$post_data = array(
								'post_title'	=> sprintf( __( 'Redeem %s for purchase', 'eddpoints' ), $pointslable ),
								'post_content'	=> sprintf( __( '%s redeemed for purchasing download by redeeming the points and get discounts.', 'eddpoints' ), $pointslable ),
								'post_author'	=>	$user_id
							);
			//log meta array
			$log_meta = array(
								'userpoint'		=>	$discountedpoints,
								'events'		=>	'redeemed_purchase',
								'operation'		=>	'minus'//add or minus
							);
						
			//insert entry in log
			$this->logs->edd_points_insert_logs( $post_data, $log_meta );
			
			// set order meta, regardless of whether any points were earned, just so we know the process took place
			update_post_meta( $payment_id, '_edd_points_order_redeemed', $discountedpoints );
			
		} //end if to check points redeemed taken by buyer or not
		
		// get cartdata from older order
		$cartdata = edd_get_payment_meta_cart_details( $payment_id );
		
		//get bought points for points downloads types
		$boughtpoints = $this->model->edd_points_get_bought_points( $cartdata );
		
		//get cart points from cartdata and payment discount given to user
		$cartpoints = $this->model->edd_points_get_user_checkout_points( $cartdata, $gotdiscount );
		
		//add bought points to cart points
		$cartpoints = !empty( $boughtpoints ) ? ( $cartpoints + $boughtpoints ) : $cartpoints;
		
		//check checkout points earned points or user id is not empty
		if( !empty( $cartpoints ) && !empty( $user_id ) ) {
			
			//points label
			$pointslable = $this->model->edd_points_get_points_label( $cartpoints );
						
			//get user points after subtracting the redemption points
			$userpoints = edd_points_get_user_points();
			
			$post_data = array(
								'post_title'	=> sprintf( __('%s earned for purchasing the downloads.','eddpoints'), $pointslable ),
								'post_content'	=> sprintf( __('Get %s for purchasing the downloads.','eddpoints'), $pointslable ),
								'post_author'	=>	$user_id
							);
			$log_meta = array(
								'userpoint'		=>	$cartpoints,
								'events'		=>	'earned_purchase',
								'operation'		=>	'add'//add or minus
							);
						
			//insert entry in log	
			$this->logs->edd_points_insert_logs( $post_data, $log_meta );
			
			//update user points
			edd_points_add_points_to_user( $cartpoints, $user_id );
			
			// set order meta, regardless of whether any points were earned, just so we know the process took place
			update_post_meta( $payment_id, '_edd_points_order_earned', $cartpoints );
			
		} //end if to check checkout points should not empty
	}
	
	public function  edd_points_add_seller_points_for_complete_purchase( $payment_id ) {

		global $edd_options;
		
		//get payment data
	
		$paymentdata	= edd_get_payment_meta( $payment_id );
		$cart_details 	= $paymentdata['cart_details'];				
		
		if( !empty( $cart_details ) ) {
			
			$points = isset( $edd_options['edd_points_selling_conversion']['points'] ) 	? abs ( $edd_options['edd_points_selling_conversion']['points'] ) 	: 0;
			$rate 	= isset( $edd_options['edd_points_selling_conversion']['rate'] ) 	? abs ( $edd_options['edd_points_selling_conversion']['rate'] ) 		: 0;
			
			if( !empty( $rate ) && !empty( $points ) ) {
				
				foreach ( $cart_details as $key => $cart_detail ) {														
							
					if( !empty( $cart_detail['item_number']['id'] ) ) {
						
						$download  = get_post( $cart_detail['item_number']['id'] );
					
						//Calculate total points for seller
						$total_seller_points = ( $points * ( $cart_detail['item_price'] * $cart_detail['quantity'] ) ) / $rate;
						
						//points label
						$pointslable = $this->model->edd_points_get_points_label( $total_seller_points );
					
						$post_data = array(
										'post_title'	=> sprintf( __('%s earned for selling the downloads.','eddpoints'), $pointslable ),
										'post_content'	=> sprintf( __('Get %s for selling the downloads.','eddpoints'), $pointslable ),
										'post_author'	=> $download->post_author
									);
						$log_meta = array(
											'userpoint'		=>	$total_seller_points,
											'events'		=>	'earned_sell',
											'operation'		=>	'add'//add or minus
										);
									
						//insert entry in log	
						$this->logs->edd_points_insert_logs( $post_data, $log_meta );
					
						//update user points
						edd_points_add_points_to_user( $total_seller_points, $download->post_author );				
					}												
				}	
			}			
		}
	}
	
	/**
	 * Adjust the Tool Bar
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 */
	function edd_points_tool_bar( $wp_admin_bar ) {
		
		global  $current_user;
		
		$wp_admin_bar->add_group( array(
			'parent' => 'my-account',
			'id'     => 'edd-points-actions',
		) );
		
		//get total users points
		$tot_points = edd_points_get_user_points();
		
		$wp_admin_bar->add_menu( array(
			'parent' => 'edd-points-actions',
			'id'     => 'user-balance',
			'title'  => __( 'My Balance:', 'eddpoints' ) .' '. $tot_points,
			'href'   => admin_url( 'profile.php' )
		) );
	}
	/**
	 * Calculate Discount towards Points
	 * 
	 * Handles to calculate the discount towards points
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_redeem_points() {
		
		global $current_user, $edd_options;
		
		//remove applied points discount
		if( isset( $_GET['edd_points_remove_discount'] ) && !empty( $_GET['edd_points_remove_discount'] )
			&& $_GET['edd_points_remove_discount'] == 'remove'  ) {

			//get discount towards points
			$gotdiscount = EDD()->fees->get_fee( 'points_redeem' );
			
			//check redeempoints set and cart is empty
			if( !empty( $gotdiscount ) ) {
				
				//remove fees towards fees
				EDD()->fees->remove_fee( 'points_redeem' );
				
				 $redirecturl = remove_query_arg( 'edd_points_remove_discount', get_permalink() );
				
				//redirect to current page
				wp_redirect( $redirecturl );
				exit;
			}
			
		} //end if to check remove discount is called or not
		
		
		//get points plural label
		$pointslabel = isset( $edd_options['edd_points_label']['plural'] ) && !empty( $edd_options['edd_points_label']['plural'] )
					? $edd_options['edd_points_label']['plural'] : 'Points';
		
		
		//check apply discount button is clicked or not
		if( isset( $_POST['edd_points_apply_discount'] ) && !empty( $_POST['edd_points_apply_discount'] ) 
			&& $_POST['edd_points_apply_discount'] == __( 'Apply Discount', 'eddpoints' ) ) {
			
			//calculate discount towards points
			$available_discount = $this->model->edd_points_get_discount_for_redeeming_points();	
			
			//calcualte the redumption price
    		EDD()->fees->add_fee( ( $available_discount * -1 ), sprintf( __( '%s Discount', 'eddpoints' ),$pointslabel  ), 'points_redeem' );
    		
    		wp_redirect( edd_get_current_page_url() );
			exit;
    		
		} else {
			
			//else change the fees if its changed from backend
			$gotdiscount = EDD()->fees->get_fee( 'points_redeem' );
			
			if( isset( $gotdiscount['amount'] ) ) {
			
				//calculate discount towards points
				$available_discount = $this->model->edd_points_get_discount_for_redeeming_points();	
				
				//calcualte the redumption price
	    		EDD()->fees->add_fee( ( $available_discount * -1 ), sprintf( __( '%s Discount', 'eddpoints' ), $pointslabel ), 'points_redeem' );
	    		
			} //end if to check the user get already points discount or not
		}
	}
	/**
	 * Remove Applied Points Discount
	 * 
	 * Handles to remove applied points 
	 * discount when cart is going to empty
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0 
	 **/
	public function edd_points_remove_point_discount() {
		
		//get discount towards points
		$gotdiscount = EDD()->fees->get_fee( 'points_redeem' );
		
		//get cart content
		$cart = edd_get_cart_contents();
		
		//check redeempoints set and cart is empty
		if( !empty( $gotdiscount ) && ! is_array( $cart ) ) {
			
			//remove fees towards fees
			EDD()->fees->remove_fee( 'points_redeem' );
		}
	}
	
	/**
	 * Replace Email Template Tags
	 * 
	 * Handles to replace email template tags
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_replace_emails_template_tags( $message, $payment_data, $payment_id ) {
		
		//get earned points from order data
		$pointsearned = get_post_meta( $payment_id, '_edd_points_order_earned', true );
		//get redeemed points from order data
		$pointsredeem = get_post_meta( $payment_id, '_edd_points_order_redeemed', true );
		
		//replace email tags
		//check points earned should not empty
		if( !empty( $pointsearned ) ) {
			//replace earned points template tag
			$message 	= str_replace( '{points_earned}', $pointsearned, $message  );
		} else {
			//replace earned points template tag
			$message 	= str_replace( '{points_earned}', '', $message  );
		}
		//check points redeemed should not empty
		if( !empty( $pointsredeem ) ) {
			//replace redeemed points template tag
			$message 	= str_replace( '{points_redeemed}', $pointsredeem, $message  );
		} else {
			//replace redeemed points template tag
			$message 	= str_replace( '{points_redeemed}', '', $message  );
		}
		
		//return message after replacing then points data
		return $message;
	}
	
	/**
	 * Add Error For Points Download
	 * 
	 * Handles to show to guest user to
	 * he can not buy a points download
	 * as a guest user
	 * 
	 * @package  Easy Digital Downloads - Points and Rewards
	 * @since 1.1.0
	 **/
	public function edd_points_download_error() {
		
		global $edd_options;
		
		//get cart data
		$cartdata = edd_get_cart_contents();
		
		//get bought points is exist in cart or not
		$boughtpoints = $this->model->edd_points_get_bought_points( $cartdata );
		
		//check user is not logged in and bought points is not empty
		if( ! is_user_logged_in() && !empty( $boughtpoints ) ) {
			
			//message 
			$guestmessage = $this->model->edd_points_guest_bought_download_message( $boughtpoints );
			
			//set error to show to user
			edd_set_error( 'points_download', $guestmessage );
			
		} //end if to check user is not logged in and not purchased points download
	}
	/**
	 * Adding Hooks
	 *
	 * Adding proper hoocks for the public pages.
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function add_hooks() {
	
		//Add points for signup
		add_action( 'user_register',	array( $this,'edd_points_add_point_for_signup' ) );

		//Add points for complete purchase
		add_action( 'edd_complete_purchase', array( $this,'edd_points_add_point_for_complete_purchase'), 100, 3 );
		
		//Add points for complete purchase
		add_action( 'edd_complete_purchase', array( $this,'edd_points_add_seller_points_for_complete_purchase'), 100, 3 );
		
		// Add message before content
		add_action( 'edd_before_download_content',array( $this->render,'edd_points_message_content' ) );
		
		// Add message for checkout
		//add_action( 'edd_before_checkout_cart', array( $this->render,'edd_points_checkout_message_content' ) );
		add_action( 'edd_before_purchase_form', array( $this->render,'edd_points_checkout_message_content' ) );
		
		//Add some content before checkout cart
		add_action( 'edd_before_purchase_form', array( $this->render,'edd_points_redeem_point_markup' ) );
		
		//Show message to guest user when he purchased points type product
		add_action( 'edd_before_purchase_form', array( $this->render,'edd_points_buy_points_type_user_message' ) );
		
		//Show message to guest user about points and rewards
		add_action( 'edd_before_purchase_form', array( $this->render,'edd_points_guest_user_message' ) );
		
		// Add menu in admin bar
		add_action( 'admin_bar_menu', array( $this, 'edd_points_tool_bar' ) );
		
		
		//AJAX Call for paging for points log
		add_action( 'wp_ajax_edd_points_next_page', array( $this->render, 'edd_points_user_log_list' ) );
		add_action( 'wp_ajax_nopriv_edd_points_next_page', array( $this->render, 'edd_points_user_log_list' ) );
		
		//add action which will call when cart will going to empty
		add_action( 'edd_post_remove_from_cart', array( $this, 'edd_points_remove_point_discount' ) );
		
		//add action to calculate fees when user apply the discount agains points
		add_action( 'init', array( $this, 'edd_points_redeem_points' ) );
		
		//Add filter to replace the points template tags for email template
		add_filter( 'edd_email_template_tags', array( $this, 'edd_points_replace_emails_template_tags' ), 10, 3 );
		
		//add action to add error on checkout page when user is guest & going to purchase points download
		add_action( 'edd_checkout_error_checks', array( $this, 'edd_points_download_error' ) );
	}
	
}
?>