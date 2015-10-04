<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Renderer Class
 *
 * To handles some small HTML content for front end
 * 
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */
class EDD_Points_Renderer {

	var $model;

	
	public function __construct() {
		
		global $edd_points_model;
		
		$this->model = $edd_points_model;
		
	}
	/**
	 * Show Listing for User Points
	 * 
	 * Handles to return / echo users
	 * points log listing at front side
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_user_log_list(){
		
		global $current_user, $edd_options;
		
		//enqueue script to work with public script
		wp_enqueue_script( 'edd-points-public-script' );
		
		$html = '';
		$perpage = 10;
		
		$argscount = array(
							'author' 	=>	$current_user->ID,
							'getcount'	=>	'1'
						);

		//get user logs count value
		$userpointslogcount = $this->model->edd_points_get_points( $argscount );
		
		$paging = new EDD_Points_Pagination_Public();
		$paging->items( $userpointslogcount ); 
		$paging->limit( $perpage ); // limit entries per page
		
		//check paging is set or not
		if( isset( $_POST['paging'] ) ) {
			$paging->currentPage( $_POST['paging'] ); // gets and validates the current page
		}
		
		$paging->calculate(); // calculates what to show
		$paging->parameterName( 'paging' );
		
		// setting the limit to start
		$limit_start = ( $paging->page - 1 ) * $paging->limit;
		
		if( isset( $_POST['paging'] ) ) { 
			//ajax call pagination
			$queryargs = array(
								'posts_per_page' 	=>	$perpage,
								'paged'				=>	$_POST['paging'],
								'author'			=>	$current_user->ID
							);
			
		} else {
			//on page load 
			$queryargs = array(
								'posts_per_page' 	=>	$perpage,
								'paged'				=>	'1',
								'author'			=>	$current_user->ID
							);
		}
		//get user logs data
		$userpointslog = $this->model->edd_points_get_points( $queryargs );
		
		//get user points
		$tot_points = edd_points_get_user_points( $current_user->ID ); 
		
			$html .= '<div class="edd-points-user-log">';
			
			//get points plural label
			$pointslabel = isset( $edd_options['edd_points_label']['plural'] ) && !empty( $edd_options['edd_points_label']['plural'] )
							? strtoupper( $edd_options['edd_points_label']['plural'] ) : __( 'POINTS', 'eddpoints' );
			
			$html .= '	<h4>'.sprintf( __( 'You have %d Points', 'eddpoints' ), $tot_points ).'</h4>';
			
			$html .= '		<table border="1">
								<tr>
									<th width="50%">'.__( 'EVENT','eddpoints' ).'</th>
									<th width="25%">'.__( 'DATE','eddpoints' ).'</th>
									<th width="15%">'.$pointslabel.'</th>
								</tr>';
		
				if( !empty( $userpointslogcount ) ) { //check user log in not empty
					
					foreach ( $userpointslog as $key => $value ){
						
						$event 			= get_post_meta( $value['ID'], '_edd_log_events', true );
						$event_data		= $this->model->edd_points_get_events( $event );
						$date			= $this->model->edd_points_log_time( strtotime( $value['post_date'] ) );
						$points 		= get_post_meta( $value['ID'], '_edd_log_userpoint', true );
						
						//check event is manual or not
						if( $event == 'manual' ) {
							$event_data = isset( $value['post_content'] ) && !empty( $value['post_content'] ) ? $value['post_content'] : '';
						}
						
						$html .= '<tr>
									<td>'.$event_data.'</td>
									<td>'.$date.'</td>
									<td>'.$points.'</td>
								</tr>';
						
					} //end foreach loop
					
				} else {
					$html .= 		'<tr><td colspan="3">'.__( 'No points log found.', 'eddpoints' ).'</td></tr>';
				}
						
		$html .= 		'</table>';
		$html .= '		<div class="edd-points-paging">
							<div id="edd-points-tablenav-pages" class="edd-points-tablenav-pages">'.
								 $paging->getOutput() .'
							</div>
						</div><!--edd-points-paging-->
						<div class="edd-points-sales-loader">
							<img src="'.EDD_POINTS_URL.'includes/images/loader.gif"/>
						</div>';
		$html .= '</div><!--edd-points-user-log-->';
		
		if( isset( $_POST['paging'] ) ) { //check paging is set in $_POST or not
			echo $html;
		} else {
			return $html;
		}
	}
	/**
	 * Show Message for checkout/redeemed product point
	 * 
	 * Handles to show message on checkout 
	 *
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 */
	public function edd_points_checkout_message_content( $postid ) {

		global $edd_options,$current_user;
		
		$edd_points_msg = $edd_options['edd_points_cart_messages'];
		
		//get cart data
		$cartdata = edd_get_cart_contents();
		
		//total points earned
		$totalpoints_earned = $this->model->edd_points_get_user_checkout_points( $cartdata );
		
		//points label
		$points_label 		= $this->model->edd_points_get_points_label( $totalpoints_earned );
		
		$points_replace 	= array( "{points}", "{points_label}" );
		$replace_message 	= array( $totalpoints_earned, $points_label );
		$message			= $this->model->edd_points_replace_array( $points_replace, $replace_message, $edd_points_msg );
		
		if( !empty( $message ) && !empty( $totalpoints_earned ) ) {
			echo "<fieldset class='edd-points-checkout-message'>".$message."</fieldset>";
		}
	}
	
	/**
	 * Show Message for puchase points
	 * 
	 * Handles to show message for purchasing on 
	 * download view page
	 *
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 */
	public function edd_points_message_content( $postid ) {

		global $edd_options;
			
		//get earning points for downloads
		$earningpoints = $this->model->edd_points_get_earning_points( $postid );
		
		//check earning points should not empty
		if( !empty( $earningpoints ) ) {
			
			// Formatting point amount
			if(is_array($earningpoints)) { //if product is variable then edd_price contains array of lowest and highest price
				
				$earning_points = '';
				foreach ($earningpoints as $key => $value) {
					
					$earning_points .= edd_format_amount( $value ). ' - ';
				}
				
				$earningpoints = trim($earning_points,' - ');
				
			} else {
				$earningpoints = edd_format_amount( $earningpoints );
			}
			
			//points label
			$points_label = $this->model->edd_points_get_points_label( $earningpoints );
			
			$points_replace 	= array( "{points}","{points_label}" );
			$replace_message 	= array( $earningpoints , $points_label );
			$message			= $this->model->edd_points_replace_array( $points_replace, $replace_message, $edd_options['edd_points_single_product_messages'] );
			
			echo "<div class='edd-points-product-message'>".$message."</div>";
			
		} //end if to check earning points should not empty
		
	}
	/**
	 * Redeem Points Markup
	 * 
	 * Handles to show redeem points markup
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_redeem_point_markup() {
		
		global $current_user, $edd_options;
		
		if( ! isset( $_GET['payment-mode'] ) && count( edd_get_enabled_payment_gateways() ) > 1 && ! edd_is_ajax_enabled() )
				return; // Only show once a payment method has been selected if ajax is disabled
		
		//get points plural label
		$plurallabel = isset( $edd_options['edd_points_label']['plural'] ) && !empty( $edd_options['edd_points_label']['plural'] )
							? $edd_options['edd_points_label']['plural'] : 'Point';
				
		//get discount got by user via points
		$gotdiscount = EDD()->fees->get_fee( 'points_redeem' );
		
		//get message from settings
		$redemptionmessage = $edd_options['edd_points_reedem_cart_messages'];
		
		//calculate discount towards points
		$available_discount = $this->model->edd_points_get_discount_for_redeeming_points();	
		
		$button_color = isset( $edd_options['checkout_color'] ) ? $edd_options['checkout_color'] : '';
		
		if ( !empty( $available_discount ) && !empty( $redemptionmessage ) && empty( $gotdiscount ) ) {
			
			//get discounte price from points
			$discountedpoints = $this->model->edd_points_calculate_points( $available_discount );
			
			//get points label to show to user
			$points_label = $this->model->edd_points_get_points_label( $discountedpoints );
			
			//display price to show to user
			$displaydiscprice	= edd_currency_filter( $available_discount );
			
			//show message on checkout page
			$points_replace 	= array( "{points}","{points_label}", "{points_value}" );
			$replace_message 	= array( $discountedpoints , $points_label, $displaydiscprice );
			$message			= $this->model->edd_points_replace_array( $points_replace, $replace_message, $redemptionmessage );
			
			?> 
			<fieldset class="edd-points-redeem-points-wrap">
				<form method="POST" action="" >
					<input type="submit" id="edd_points_apply_discount" name="edd_points_apply_discount" class="button edd-submit <?php _e( $button_color ); ?> edd-points-apply-discount-button" value="<?php _e( 'Apply Discount', 'eddpoints' );?>" />
				</form>
				<div class="edd-points-redeem-message"><?php echo $message;?></div><!--.edd-points-checkout-message-->
			</fieldset><!--.edd-points-redeem-points-wrap-->
			<?php
			
		} //end if cart total not empty
		
		//if points discount applied then show remove link
		if( !empty( $gotdiscount ) ) {
			
			$removfeesurl = add_query_arg( array( 'edd_points_remove_discount' => 'remove' ), edd_get_current_page_url() );
			?>
				<fieldset class="edd-points-checkout-message">
					<a href="<?php echo $removfeesurl;?>" class="button edd-point-remove-discount-link edd-points-float-right"><?php _e( 'Remove', 'eddpoints' );?></a>
					<div class="edd-points-remove-disocunt-message"><?php printf( __( 'Remove %s Discount', 'eddpoints' ), $plurallabel );?></div><!--.edd-points-checkout-message-->
				</fieldset><!--.edd-points-redeem-points-wrap-->
			<?php
		}
	}
	/**
	 * Add custom fields.
	 *
	 * Handles to add custom fields in user profile page
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 **/
	public function edd_points_add_custom_user_profile_fields( $user ) {
	
		//get user points
		$userpoints = edd_points_get_user_points( $user->ID );
		?>
			<table class="form-table edd-points-user-profile-balance">
				<tr>
					<th>
						<label for="edd_userpoints"><?php _e('My current balance', 'eddpoints'); ?></label>
					</th>
					<td>
						<h2><?php echo $userpoints; ?></h2>
					</td>
				</tr>
			</table>
		<?php
		
	}
	/**
	 * Add Metabox fields
	 * 
	 * Handles to add metabox fields
	 *
	 * @package Easy Digital Downloads - Points and Rewards
 	 * @since 1.0.0
	 */
	public function edd_points_metabox( $post ) {
		
		global $edd_options;

		$points_earned       = get_post_meta( $post->ID, '_edd_points_earned', true );
		$max_points_discount = get_post_meta( $post->ID, '_edd_points_max_discount', true );
		
		//create nonce for metabox
		wp_nonce_field( EDD_POINTS_BASENAME, 'at_edd_points_and_rewards_meta_nonce' );
		
		?>
		<div id="edd_simple_point">
			<div id="edd_points_rewads_fields">
				<table>
					<tr>
						<td width="20%">
							<label for="edd_points_earned"><?php _e( 'Points Earned:', 'eddpoints' ); ?></label>
						</td>
						<td>
							<input type="text" class="edd-price-field" value="<?php echo $this->model->edd_points_escape_attr( $points_earned ); ?>" id="_edd_points_earned" name="_edd_points_earned"/>
						</td>
					</tr>
					<tr>
						<td></td>
						<td><span class="description"><?php _e( 'This can be a fixed number of points earned for purchasing this product. This setting modifies the global Points Conversion Rate and overrides any category value.Use 0 to assign no points for this product, and empty to use the global/category settings.', 'eddpoints' );?></span></td>
					</tr>
					<tr>
						<td width="20%">
							<label for="_edd_points_max_discount"><?php _e( 'Maximum Points Discount:', 'eddpoints' ); ?></label>
						</td>
						<td>
							<input type="text" class="edd-price-field" value="<?php echo $this->model->edd_points_escape_attr( $max_points_discount ); ?>" id="_edd_points_max_discount" name="_edd_points_max_discount"/>
							<?php echo edd_currency_filter('');?>
						</td>
					</tr>
					<tr>
						<td></td>
						<td><span class="description"><?php printf( __( 'Enter a fixed maximum discount amount which restricts the amount of points that can be redeemed for a discount. For example, if you want to restrict the discount on this product to a maximum of %s5, enter 5. This setting overrides the global and category settings. Use 0 to disable point discounts for this product, and blank to use the global/category defaults.', 'eddpoints' ), edd_currency_filter('') );?></span></td>
					</tr>
				</table>
			</div> <!--#edd_points_rewads_fields-->
		</div><!--#edd_simple_point-->
		<?php
	}
	/**
	 * Downloads Category fields HTML
	 * 
	 * Handles to add category fields HTML
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_download_category_add_fields_html() {
		
		$points_earned_description = __( 'This can be a fixed number of points earned for the purchase of any product that belongs to this category. This setting modifies the global Points Conversion Rate, but can be overridden by a product. Use 0 to assign no earn points for products belonging to this category, and empty to use the global setting. If a product belongs to multiple categories which define different point levels, the highest available point count will be used when awarding points for purchase.', 'eddpoints' );
		$max_discount_description  = sprintf( __( 'Enter a fixed maximum discount amount  which restricts  the amount of points that can be redeemed for a discount. For example, if you want to restrict the discount on this category to a maximum of %s5, enter 5. This setting overrides the global default, but can be overridden by a product. Use 0 to disable point discounts for this category, and blank to use the global setting. If a product belongs to multiple categories which define different point discounts, the lowest point count will be used when allowing points discount for purchase.', 'eddpoints' ), edd_currency_filter('') );
		
		?>
		
		<div class="form-field">
			<label for="edd_points_rewards[edd_points_earned]"><?php _e( 'Points Earned', 'eddpoints' ); ?></label>
			<input type="text" class="edd-points-earned-cat-field" name="edd_points_rewards[edd_points_earned]" id="edd_points_rewards[edd_points_earned]" style="width:80px;"/>
			<p><?php echo $points_earned_description;?></p>
		</div><!--.form-field-->
		<div class="form-field">
			<label for="edd_points_rewards[edd_points_max_discount]"><?php _e( 'Maximum Points Discount', 'eddpoints' ); ?></label>
			<input type="text" class="edd-points-dis-cat-field" name="edd_points_rewards[edd_points_max_discount]" id="edd_points_rewards[edd_points_max_discount]" style="width:80px;"/>
			<?php echo edd_currency_filter('');?>
			<p><?php echo $max_discount_description;?></p>
		</div><!--.form-field-->
		<?php
	}
	/**
	 * Downloads Category Edit fields HTML
	 * 
	 * Handles to edit category fields HTML
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_download_category_edit_fields_html( $term ) {
		
		$points_earned_description = __( 'This can be a fixed number of points earned for the purchase of any product that belongs to this category. This setting modifies the global Points Conversion Rate, but can be overridden by a product. Use 0 to assign no earn points for products belonging to this category, and empty to use the global setting. If a product belongs to multiple categories which define different point levels, the highest available point count will be used when awarding points for purchase.', 'eddpoints' );
		$max_discount_description  = sprintf( __( 'Enter a fixed maximum discount amount  which restricts  the amount of points that can be redeemed for a discount. For example, if you want to restrict the discount on this category to a maximum of %s5, enter 5. This setting overrides the global default, but can be overridden by a product. Use 0 to disable point discounts for this category, and blank to use the global setting. If a product belongs to multiple categories which define different point discounts, the lowest point count will be used when allowing points discount for purchase.', 'eddpoints' ),edd_currency_filter('') );
	
		$t_id = $term->term_id;
		$eddpointstermdata = get_option( "download_category_$t_id" ); 
		
		$earnedpoints 	= isset( $eddpointstermdata['edd_points_earned'] ) ? $eddpointstermdata['edd_points_earned'] : '';
		$maxdiscount 	= isset( $eddpointstermdata['edd_points_max_discount'] ) ? $eddpointstermdata['edd_points_max_discount'] : '';
		
		?>
			<tr class="form-field">
				<th valign="top" scope="row"><label for="edd_points_rewards[edd_points_earned]"><?php _e( 'Points Earned', 'eddpoints' ); ?></label></th>
				<td>
					<input type="text" name="edd_points_rewards[edd_points_earned]" id="edd_points[edd_points_earned]" value="<?php echo $this->model->edd_points_escape_attr( $earnedpoints );?>" style="width:80px;"/>
					<p class="description"><?php echo $points_earned_description;?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th valign="top" scope="row"><label for="edd_points_rewards[edd_points_max_discount]"><?php _e( 'Maximum Points Discount', 'eddpoints' ); ?></label></th>
				<td>
					<input type="text" class="edd-points-earned-cat-field" name="edd_points_rewards[edd_points_max_discount]" id="edd_points_rewards[edd_points_max_discount]"  value="<?php echo $this->model->edd_points_escape_attr( $maxdiscount );?>" style="width:80px;"/>
					<?php echo edd_currency_filter('');?>
					<p class="description"><?php echo $max_discount_description;?></p>
				</td>
			</tr>
		
		<?php
	}
	/**
	 * Show Message to Guest User
	 * 
	 * Handles to show message to guest 
	 * user (when user not logged in)
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_guest_user_message() {
		
		global $edd_options;
		
		//get cart data
		$cartdata = edd_get_cart_contents();
		
		//points earned
		$earned_points 		= $this->model->edd_points_get_user_checkout_points( $cartdata );
		
		//check guest user message is not empty user is not logged in & earned points is not empty
		if( !empty( $edd_options['edd_points_earn_guest_messages'] ) && !is_user_logged_in() && !empty( $earned_points )) {
			
			//message 
			$guest_message = $edd_options['edd_points_earn_guest_messages'];
			
			//points lable
			$points_label 		= $this->model->edd_points_get_points_label( $earned_points );
			
			//signup points 
			$signup_points = isset( $edd_options['edd_points_earned_account_signup'] ) && !empty( $edd_options['edd_points_earned_account_signup'] )
								? $edd_options['edd_points_earned_account_signup'] : '';
			
			$points_replace 	= array( "{points}", "{points_label}", "{signup_points}" );
			$replace_message 	= array( $earned_points, $points_label, $signup_points );
			$guest_message		= $this->model->edd_points_replace_array( $points_replace, $replace_message, $guest_message );
			
			echo "<fieldset class='edd-points-checkout-message'>".$guest_message."</fieldset>";
			
		} //end if to check earning points should not empty
		
	}
	
	/**
	 * Show Message to Guest User When Points Type 
	 * Download Exist in Cart
	 * 
	 * Handles to show message to guest when points type product
	 * exist in cart user (when user not logged in)
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.1.0
	 **/
	public function edd_points_buy_points_type_user_message() {
		
		global $edd_options;
		
		//get cart data
		$cartdata = edd_get_cart_contents();
		
		//get bought points
		$boughtpoints 		= $this->model->edd_points_get_bought_points( $cartdata );
	
		//check guest user message is not empty user is not logged in & bought points is not empty
		if( !empty( $boughtpoints ) && !is_user_logged_in() ) {

			//message 
			$guestmessage = $this->model->edd_points_guest_bought_download_message( $boughtpoints );
			
			echo "<fieldset class='edd-points-checkout-message'>".$guestmessage."</fieldset>";
			
		} //end if to check bought points should not empty
		
	}
}
?>