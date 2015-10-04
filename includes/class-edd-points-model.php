<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Model Class
 *
 * Handles generic plugin functionality.
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 */
class EDD_Points_Model {
	
	public function __construct() {
	
	}
	
	/**
	 * Escape Tags & Slashes
	 *
	 * Handles escapping the slashes and tags
	 *
	 * @package  Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_escape_attr($data){
		return esc_attr(stripslashes($data));
	}
	
	/**
	 * Strip Slashes From Array
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_escape_slashes_deep( $data = array(), $flag = false, $limited = false ){
			
		if( $flag != true ) {
			
			$data = $this->edd_points_nohtml_kses($data);
			
		} else {
			
			if( $limited == true ) {
				$data = wp_kses_post( $data );
			}
			
		}
		$data = stripslashes_deep($data);
		return $data;
	}
	
	/**
	 * Strip Html Tags 
	 * 
	 * It will sanitize text input (strip html tags, and escape characters)
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_nohtml_kses( $data = array() ) {
		
		if ( is_array($data) ) {
			
			$data = array_map( array( $this,'edd_points_nohtml_kses' ), $data );
			
		} elseif ( is_string( $data ) ) {
			
			$data = wp_filter_nohtml_kses($data);
		}
		
		return $data;
	}	
	
	/**
	 * Convert Object To Array
	 *
	 * Converting Object Type Data To Array Type
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 * 
	 */
	public function edd_points_object_to_array( $result )
	{
	    $array = array();
	    foreach ($result as $key=>$value)
	    {	
	        if (is_object($value))
	        {
	            $array[$key] = $this->edd_points_object_to_array( $value );
	        } else {
	        	$array[$key] = $value;
	        }
	    }
	    return $array;
	}
	
	/**
	 * For Get points
	 *
	 * @access public
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_get_points( $args=array() ) {
		
		$queryargs = array( 'post_type' => EDD_POINTS_LOG_POST_TYPE, 'post_status' => 'publish' );
		
		$queryargs = wp_parse_args( $args, $queryargs );

		//if search is called then retrive searching data
		if(isset($args['search'])) {
			$queryargs['s'] = $args['search'];
		}
		// filter related month
		if( isset( $args['monthyear'] ) && !empty( $args['monthyear'] ) ) {
			$queryargs['m'] = $args['monthyear'];
		}
		if(isset($args['author'])) {
			$queryargs['author'] = $args['author'];
		}
		if(isset($args['event'])) {
			$queryargs['meta_query'] = array(
									       array(
									           'key' => '_edd_log_events',
									           'value' => $args['event'],
									           'compare' => '=',
									       )
									   );
		}
	
		//show how many per page records
		if(isset( $args['posts_per_page'] ) && !empty( $args['posts_per_page'] ) ) {
			$queryargs['posts_per_page'] = $args['posts_per_page'];
		} else {
			$queryargs['posts_per_page'] = -1;
		}
		
		//show per page records
		if(isset( $args['paged'] ) && !empty( $args['paged'] ) ) {
			$queryargs['paged']	=	$args['paged'];
		}
		
		//fire query in to table for retriving data
		$result = new WP_Query( $queryargs );
		
		if(isset($args['getcount']) && $args['getcount'] == '1') {
			$postslist = $result->post_count;	
		}  else {
			//retrived data is in object format so assign that data to array for listing
			$postslist = $this->edd_points_object_to_array($result->posts);
			
			// if get list for deal sales list then return data with data and total array
			if( isset($args['edd_points_list']) && $args['edd_points_list'] ) {

				$data_res	= array();
					
				$data_res['data'] 	= $postslist;

				//To get total count of post using "found_posts" and for users "total_users" parameter
				$data_res['total']	= isset($result->found_posts) ? $result->found_posts : '';

				return $data_res;
			}
		}
		
		return $postslist;
	}
	/**
	 * Get Events from Slug
	 * 
	 * Handles to get points log event from slug
	 *  
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_get_events( $event = '' ) {
		
		global $edd_options;
		
		//points plural label
		$plurallable = isset( $edd_options['edd_points_label']['plural'] ) ? $edd_options['edd_points_label']['plural'] : '';
		
		$value = '';
		switch ($event) {
			
			case 'earned_purchase' :
				$value = sprintf( __( '%s earned for purchase' , 'eddpoints'), ucfirst( $plurallable ) );
				break;
			
			case 'earned_sell' :
				$value = sprintf( __( '%s earned for sell' , 'eddpoints'), ucfirst( $plurallable ) );
				break;
				
			case 'redeemed_purchase' :
				$value = sprintf( __( '%s redeemed towards purchase' , 'eddpoints'), ucfirst( $plurallable ) );
				break;
			
			case 'signup' :
				$value = sprintf( __( '%s earned for account signup' , 'eddpoints'), ucfirst( $plurallable ) );
				break;
			
			case 'reset_points' :
				$value = sprintf( __( '%s reset' , 'eddpoints'), ucfirst( $plurallable ) );
				break;	
			
			default:
				break;
		}
		return $value;
	}
	
	/**
	 * Register Settings
	 * 
	 * Handels to add settings in settings page
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_settings( $settings ) {
		
		$success_message = '';
		// Display success message when click Apply Points extensions settings
		if( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == 'apply_points'
			&& isset( $_GET['success_count'] ) ) {
			$success_count = $_GET['success_count'];
			$success_message = '<div class="updated" id="message"><p><strong>' . sprintf( __( '%d order(s) updated.','eddpoints' ), $success_count ) . '</strong></p></div>';
		}
		
		$edd_points_settings = array(
		
				array(
					'id'	=> 'edd_points_settings',
					'name'	=> $success_message . '<strong>' . __( 'Points And Rewards Options', 'eddpoints' ) . '</strong>',
					'desc'	=> __( 'Configure Points And Rewards Settings', 'eddpoints' ),
					'type'	=> 'header'
				),
				
				//Points & Rewards Settings
				array(
					'id'	=> 'edd_points_general_settings',
					'name'	=> '<strong>' . __( 'Points Settings', 'eddpoints' ) . '</strong>',
					'desc'	=> __( 'Configure Points Settings', 'eddpoints' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_points_earn_conversion',
					'name'	=> __( 'Earn Points Conversion Rate:', 'eddpoints' ),
					'desc'	=> '<p class="description">'.__( 'Set the number of points awarded based on the product price.', 'eddpoints' ).'</p>',
					'type'	=> 'pointsrate',
					'size'	=> 'small'
				),
				array(
					'id'	=> 'edd_points_redeem_conversion',
					'name'	=> __( 'Redeem Points Conversion Rate:', 'eddpoints' ),
					'desc'	=> '<p class="description">'.__( 'Set the value of points redeemed for a discount.', 'eddpoints' ).'</p>',
					'type'	=> 'pointsrate',
					'size'	=> 'small'
				),
				array(
					'id'	=> 'edd_points_buy_conversion',
					'name'	=> __( 'Buy Points Conversion Rate:', 'eddpoints' ),
					'desc'	=> '<p class="description">'.__( 'Set the value for buy points.', 'eddpoints' ).'</p>',
					'type'	=> 'pointsrate',
					'size'	=> 'small'
				),
				array(
					'id'	=> 'edd_points_selling_conversion',
					'name'	=> __( 'Selling Points Conversion Rate:', 'eddpoints' ),
					'desc'	=> '<p class="description">'.__( 'Set the value for selling points.', 'eddpoints' ).'</p>',
					'type'	=> 'pointsrate',
					'size'	=> 'small'
				),
				array(
					'id'	=> 'edd_points_cart_max_discount',
					'name'	=> __( 'Maximum Cart Discount:', 'eddpoints' ),
					'desc'	=> edd_currency_filter('').'<p class="description">'.__( 'Set the maximum discount allowed for the cart when redeeming points. Leave blank to disable.', 'eddpoints' ).'</p>',
					'type'	=> 'text',
					'size'	=> 'medium'
				),
				array(
					'id'	=> 'edd_points_max_discount',
					'name'	=> __( 'Maximum Per-Product Points Discount:', 'eddpoints' ),
					'desc'	=> edd_currency_filter('').'<p class="description">'.__( 'Set the maximum per-product discount allowed for the cart when redeeming points. Leave blank to disable.', 'eddpoints' ).'</p>',
					'type'	=> 'text',
					'size'	=> 'medium'
				),
				array(
					'id'	=> 'edd_points_label',
					'name'	=> __( 'Points Label:', 'eddpoints' ),
					'desc'	=> '<p class="description">'.__( 'The label used to refer the points on the frontend, singular and plural.', 'eddpoints' ).'</p>',
					'type'	=> 'singularplural',
					'size'	=> 'small'
				),
				array(
					'id'	=> 'edd_points_product_messages',
					'name'	=> '<strong>' . __( 'Product / Cart / Checkout Messages', 'eddpoints' ) . '</strong>',
					'desc'	=> __( 'Configure Message Settings', 'eddpoints' ),
					'type'	=> 'header'
				),
				array(
					'id' => 'edd_points_single_product_messages',
					'name' => __( 'Single Product Page Message:', 'eddpoints' ),
					'desc' => '<p class="description">'.sprintf(__( 'Add an optional message to the single product page below the price. Customize the message using %s and %s.Limited HTML is allowed.Leave blank to disable.', 'eddpoints' ),'{points}','{points_label}').'</p>',
					'type' => 'textarea'
				),
				array(
					'id' => 'edd_points_cart_messages',
					'name' => __( 'Earn Points Cart / Checkout Page Message:', 'eddpoints' ),
					'desc' => '<p class="description">'.sprintf(__( 'Displayed on the cart and checkout page when points are earned. Customize the message using %s and %s.Limited HTML is allowed.', 'eddpoints' ),'{points}','{points_label}').'</p>',
					'type' => 'textarea'
				),
				array(
					'id' => 'edd_points_reedem_cart_messages',
					'name' => __( 'Reedem Points Cart / Checkout Page Message:', 'eddpoints' ),
					'desc' => '<p class="description">'.sprintf(__( 'Displayed on the cart and checkout page when points are available for redemption. Customize the message using %s, %s, and %s.Limited HTML is allowed.', 'eddpoints' ),'{points}', '{points_value}','{points_label}').'</p>',
					'type' => 'textarea'
				),
				array(
					'id' => 'edd_points_earn_guest_messages',
					'name' => __( 'Guest User Cart/Chekout Page Message:', 'eddpoints' ),
					'desc' => '<p class="description">'.sprintf(__( 'Displayed on the cart and checkout page for guest users to indicate to create an account for earn the points. Customize the message using %s, %s and %s. Limited HTML is allowed. Leave blank to disable.', 'eddpoints' ),'{points}','{points_label}','{signup_points}').'</p>',
					'type' => 'textarea'
				),
				array(
					'id' => 'edd_points_bought_guest_messages',
					'name' => __( 'Guest User Cart/Chekout Page Buy Message:', 'eddpoints' ),
					'desc' => '<p class="description">'.sprintf(__( 'Displayed on the cart and checkout page for guest users to indicate to create an account to get points into their account. Customize the message using %s and %s. Limited HTML is allowed. Leave blank to disable.', 'eddpoints' ),'{points}','{points_label}').'</p>',
					'type' => 'textarea'
				),
				array(
					'id'	=> 'edd_points_earned_action',
					'name'	=> '<strong>' . __( 'Points Earned For Actions', 'eddpoints' ) . '</strong>',
					'desc'	=> __( 'Configure Points Settings', 'eddpoints' ),
					'type'	=> 'header'
				),
				array(
					'id'	=> 'edd_points_earned_account_signup',
					'name'	=> __( 'Points earned for account signup:', 'eddpoints' ),
					'desc'	=> '<p class="description">'.__( 'Enter the amount of points earned when a customer signs up for a new account.', 'eddpoints' ).'</p>',
					'type'	=> 'text',
					'size'	=> 'regular'
				),
				array(
					'id'	=> 'edd_points_apply_points',
					'name'	=> __( 'Apply Points to Previous Orders:', 'eddpoints' ),
					'desc'	=> '<p class="description">'.__( 'This will apply points to all previous orders and cannot be reversed.', 'eddpoints' ).'</p>',
					'type'	=> 'apply_points',
					'size'	=> 'button',
					'button'=> __( 'Apply Points','eddpoints' ),
				),
			);
		
		return array_merge( $settings, $edd_points_settings );
		
	}
	
	/**
	 * Return Time
	 * 
	 * Handles to return formated time
	 * from specific timestamp
	 *  
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_log_time( $timestamp,$type='' ) {
		
		// calculate the diffrence 
		$timediff = time() - $timestamp;
		$returndate = '';
		
		if ( $timediff < 3600 )  { 
	
	        if ($timediff < 60) { //if ($timediff < 120) { 
	            //$returndate = "less than a minute ago.";
	           $returndate = $timediff  .__( ' seconds ago', 'eddpoints' );
	        } else if($timediff > 60 && $timediff < 120) { 
	            $returndate =  ' ' . __( 'about a minute ago', 'eddpoints' ); 
	        } else { 
	            $returndate =  intval($timediff / 60) . ' ' . __( 'minutes ago', 'eddpoints' ); 
	        } 
	
	    } else if ($timediff < 7200) { 
	        
	    	$returndate = ' ' . __( '1 hour ago', 'eddpoints'); 
	    	
	    } else if ($timediff < 86400) { 
	        
	    	$returndate = intval($timediff / 3600) . ' ' . __( 'hours ago', 'eddpoints' );
	        
	    } else if ($timediff < 172800) { 
	        
	    	$returndate = ' '.__( '1 day ago', 'eddpoints' ); 
	        
	    } else if ($timediff < 604800) {
	        
	    	$returndate = intval($timediff / 86400) . ' ' . __( 'days ago', 'eddpoints' );
	        
	    } else if ($timediff < 1209600) { 
	    	
	        $returndate = ' ' . __( '1 week ago', 'eddpoints');
	        
	    } else if ($timediff < 3024000) { 
	    	
	        $returndate = intval($timediff / 604900) . ' ' . __( 'weeks ago', 'eddpoints' );
	        
	    } else { 
	    
	     	$returndate = @date('d.m.Y', $timestamp); 
	     	
	         if( $type=="fulldate" ) {   
	         	
	             $returndate = @date('d.m.Y H:i', $timestamp); 
	             
	         } else if ( $type=="time" ) { 
	         	
	             $returndate = @date('H:i', $timestamp); 
	           
	         } 
	 	}
		return $returndate;
	}
	/**
	 * Calculate Points using cartdata
	 * 
	 * Handles to calculate points using cartdata
	 * and return
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_get_user_checkout_points( $cartdata, $discount = 0 ) {
		
		global $edd_options;
		
		//initial total points
		$totalpoints = 0;
								
		//check conversion rate & points for earning should not empty & cartdata should not empty
		if( !empty( $cartdata ) ) {
			
			foreach ( $cartdata as $key => $item ) {
				
				//check item of options set then consider that
				if( isset( $item['options'] ) ) { $itemoptions  = $item['options']; }
				//if item_number of options set then consider it
				elseif ( isset( $item['item_number']['options'] ) ) {  $itemoptions  = $item['item_number']['options']; }
				//else it will be blank array
				else { $itemoptions = array(); }
				
				//get individual points for the item on checkout page
				$points 			= $this->edd_points_get_earning_points( $item['id'], $itemoptions, true );
				
				//calculate total points for item in cart
				$itemtotalpoints	= !empty( $points ) ? ( $points * edd_get_cart_item_quantity( $item['id'] ) ) : 0;
				
				//increase total points
				$totalpoints += $itemtotalpoints;
				
			} //end foreach loop
			
			if( !empty( $discount ) ) {
				$totalpointsdiscountgot = $discount;
			} else {
				//get discount got by user via points
				$gotdiscount = EDD()->fees->get_fee( 'points_redeem' );
				$totalpointsdiscountgot = !empty( $gotdiscount ) ? abs( $gotdiscount['amount'] ) : 0;
			}
			
			// reduce by any discounts.  One minor drawback: if the discount includes a discount on tax and/or shipping
			//it will cost the customer points, but this is a better solution than granting full points for discounted orders
			$totalpoints 			-= min( $this->edd_points_calculate_earn_points_from_price( $totalpointsdiscountgot ), $totalpoints );
			
		} //end if to check conversion points & rate should not empty
		
		//return total points user will get
		return intval( $totalpoints );
	}
	/**
	 * Returns the points label, singular or plural form, based on $points
	 *
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_get_points_label( $points ) {

		global $edd_options;
		
		$singular = isset( $edd_options['edd_points_label']['singular'] ) ? $edd_options['edd_points_label']['singular'] : '';
		$plural = isset( $edd_options['edd_points_label']['plural'] ) ? $edd_options['edd_points_label']['plural'] : '';
		
		if ( 1 == $points ) { return $singular; }
		else { return $plural; }
	}
	/**
	 * Get Downloads Earning Points 
	 * 
	 * Handles to return earning points for download
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_get_earning_points( $downloadid, $priceoptions = array(), $checkout = false ) { 
		
		//if this function called from checkout page then use third parameter to TRUE
		
		global $edd_options;
		
		$earningpointsbyuser = 0;
		
		//check if price is for checkout page
		if( !empty( $checkout ) ) {
			
			//if checkout page 
			
			$edd_price = edd_get_cart_item_price( $downloadid, $priceoptions );
			
			
		} else {
			
			//if not is checkout page
			if ( edd_has_variable_prices( $downloadid ) ) { //check product price is varible pricing enable or not
				
				//$prices = edd_get_variable_prices( $downloadid );
				//$edd_price = edd_sanitize_amount( $prices[0]['amount'] );
				
				
				$edd_price[0] = edd_get_lowest_price_option( $downloadid );
				$edd_price[1] = edd_get_highest_price_option( $downloadid );
				
			} else {
				//get download price
				$edd_price = edd_get_download_price( $downloadid );
				
			} //end else
			
		} //end else
		
			
		//get download points for download level from meta box
		$downloadearnpoints = $this->edd_points_get_download_earn_points( $downloadid );
		
		if ( is_numeric( $downloadearnpoints ) ) {
			return $downloadearnpoints;
		}
		
		//check if points of download are set in category level
		$downloadearnpoints = $this->edd_points_get_category_earn_points( $downloadid );
		
		if ( is_numeric( $downloadearnpoints ) ) {
			return $downloadearnpoints;
		}
		
		
		
		if(is_array($edd_price)) { // if product is variable then edd_price contains array of lowest and highest price
			
			$earning_points_by_user = array();
			foreach ($edd_price as $key => $data) {
				
				$earning_points_by_user[$key] = $this->edd_points_calculate_earn_points_from_price( $data );
				
			}
			return $earning_points_by_user;
			
		} else { // if product is simple product
		
			//calculate the earn points from price
			$earningpointsbyuser = $this->edd_points_calculate_earn_points_from_price( $edd_price );
		}
		
		// get download points based on global setting 
		return $earningpointsbyuser;
	}
	/**
	 * Get Download Points from Meta Box
	 * 
	 * Handles to get download points from Meta Box
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_get_download_earn_points( $downloadid ){
		
		//get earn points from metabox
		$earnedpoints = get_post_meta( $downloadid, '_edd_points_earned', true );
		//$edd_price = edd_get_download_price( $downloadid );
		
		// if a percentage modifier is set, adjust the points for the product by the percentage
		/*if ( strpos( $earnedpoints, '%' ) > 0 ) {
			$earnedpoints = $this->edd_points_discount_from_percent( $earnedpoints, $edd_price );
		}*/
		
		return $earnedpoints;
		
	}
	
	/**
	 * Replace First Array to Second Array in message
	 * 
	 * Handles to replace one array to another array 
	 * in particular message
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_replace_array( $searcharr, $replacearr , $message ) {
		
		$message = str_replace( $searcharr , $replacearr , $message );
		return $message;
		
	}
	
	/**
	 * Calculate Points for Discount
	 * 
	 * Handles to calculate discount for points
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 */
	public function edd_points_calculate_points( $discount ) {
		
		global $edd_options;
		
		//get redemption points from settings page
		$points = $edd_options['edd_points_redeem_conversion']['points'];
		//get redemption ration from settings page
		$rate = $edd_options['edd_points_redeem_conversion']['rate'];

		if ( empty( $points ) || empty( $rate ) ) {
			return 0;
		}
		
		return ceil( $discount * ( $points / $rate ) );
	}
	/**
	 * Calculate Points via Disocunted Amount
	 * 
	 * Handles to calculate points
	 * via discounted amount
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_calculate_earn_points_from_price( $amount ) {
		
		global $edd_options;
		
		// Get earning points from settings page
		$points = intval($edd_options['edd_points_earn_conversion']['points']);
		
		// Get earning ration from settings page
		$rate = intval($edd_options['edd_points_earn_conversion']['rate']);
		
		if ( empty( $points ) || empty( $rate ) ) {
			return 0;
		}
		
		return round( $amount * ( $points / $rate ) );
	}
	/**
	 * Calculate Discount From Product Price
	 * 
	 * Handles to calculate max discount from download price
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_calculate_redeem_points_from_price( $amount ) {
		
		global $edd_options;
		
		//get earning points from settings page
		$points = $edd_options['edd_points_redeem_conversion']['points'];
		//get earning ration from settings page
		$rate = $edd_options['edd_points_redeem_conversion']['rate'];
		
		if ( empty( $points ) || empty( $rate ) ) {
			return 0;
		}
		
		return ceil( $amount / $rate );
	}
	/**
	 * Calculate Max Discount Points for download
	 * 
	 * Handles to return max discounted point for particular downloads
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_get_max_points_discount_for_download( $downloadid ){
		
		global $edd_options;
		
		//get download level max points
		$max_discount = $this->edd_points_max_discount( $downloadid );
		//$edd_price = edd_get_download_price( $downloadid );
		
		if ( is_numeric( $max_discount ) ) {
			return $max_discount;
		}

		//get download category level max points
		$max_discount = $this->edd_points_get_category_max_discount( $downloadid );
		
		if ( is_numeric( $max_discount ) ) {
			return $max_discount;
		}
		
		// get global maximum disocunt grom settings page
		$max_discount = $edd_options['edd_points_max_discount'];

		if ( is_numeric( $max_discount ) ) {
			return $max_discount;
		}
		
		/*// if the global max discount is a percentage, calculate it by multiplying the percentage by the product price
		if ( strpos( $max_discount, '%' ) > 0 ) {
			$max_discount = $this->edd_points_discount_from_percent( $max_discount, $edd_price );
		}

		if ( is_numeric( $max_discount ) ) {
			return $max_discount;
		}*/

		// get global maximum disocunt grom settings page
		/*$max_discount = $this->edd_points_calculate_redeem_points_from_price( $edd_price );

		if ( is_numeric( $max_discount ) ) {
			return $max_discount;
		}*/
		
		// otherwise, there is no maximum discount set
		return '';
		
	}
	/**
	 * Max Discount for Download
	 * 
	 * Handles to return max discount for download
	 * 
	 * @package Easy Digital Download - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_max_discount( $downloadid ){
		
		$max_discount = get_post_meta( $downloadid, '_edd_points_max_discount', true );
		//$edd_price = edd_get_download_price( $downloadid );
		
		/*// if a percentage modifier is set, set the maximum discount using the price of the download
		if ( strpos( $max_discount, '%' ) > 0 ) {
			$max_discount = $this->edd_points_discount_from_percent( $max_discount, $edd_price );
		}*/
		return $max_discount;
	}
	/**
	 * Calculate Discount from Percentage
	 * 
	 * Handles to calculate discount from percentage
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_discount_from_percent( $percentage, $downloadprice ) {
		
		$percentage = str_replace( '%', '', $percentage ) / 100;

		return $percentage * $downloadprice;
	}
	
	/**
	 * Calculate Discount From User Points
	 * 
	 * Handles to calculate value from user points
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_get_userpoints_value() {
		
		global $edd_options;
		
		//get user total points
		$userpoints = edd_points_get_user_points();
		
		// Get redemption points from settings page
		$points = intval($edd_options['edd_points_redeem_conversion']['points']);
		
		// Get redemption ration from settings page
		$rate = intval($edd_options['edd_points_redeem_conversion']['rate']);
		
		if( empty( $points ) || empty( $rate ) ) {
			return 0;
		}
		
		return ( $userpoints * ( $rate / $points ) );
	}
	
	/**
	 * Calculate Maximum Possible discount available
	 *
	 * Handles to calculate maximum possible discount
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_get_discount_for_redeeming_points() {
		
		global $edd_options;
		
		//get users points
		$available_user_disc = $this->edd_points_get_userpoints_value();
		
		//get cart subtotal
		$cartsubtotal = edd_get_cart_subtotal() - edd_get_cart_discounted_amount();
		
		//check price is include tas or not 
		if( edd_prices_include_tax() == 'yes' ) {
			$cartsubtotal = ( $cartsubtotal - edd_get_cart_tax() );
		}
		
		//check user has points or not
		if( empty( $available_user_disc ) ) {
			return 0;
		}
		
		//get cart content
		$cartdata = edd_get_cart_contents();
		
		$discount_applied = 0;
		
		// calculate the discount to be applied by iterating through each item in the cart and calculating the individual
		// maximum discount available
		foreach ( $cartdata as $item_key => $item ) {
			
			//max discount
			$max_discount = $this->edd_points_get_max_points_discount_for_download( $item['id'] );
			
			//check item of options set then consider that
			if( isset( $item['options'] ) ) { $itemoptions  = $item['options']; }
			//if item_number of options set then consider it
			elseif ( isset( $item['item_number']['options'] ) ) {  $itemoptions  = $item['item_number']['options']; }
			//else it will be blank array
			else { $itemoptions = array(); }
				
			//get download price
			$downloadprice = edd_get_cart_item_price( $item['id'],$itemoptions );
			
			if ( is_numeric( $max_discount ) ) {
				
				// adjust the max discount by the quantity being ordered
				$max_discount *= $item['quantity'];
				
				// if the discount available is greater than the max discount, apply the max discount
				$discount = ( $available_user_disc <= $max_discount ) ? $available_user_disc : $max_discount;
				
			} else {

				//when maximum discount is not set for product then allow maximum total cost of product as a discount
				$max_price_discount = ( $downloadprice * $item['quantity'] );
				
				// if the discount available is greater than the max discount, apply the max discount
				$discount = ( $available_user_disc <= $max_price_discount ) ? $available_user_disc : $max_price_discount;
				
			}
			
			// add the discount to the amount to be applied
			$discount_applied += $discount;
			
			// reduce the remaining discount available to be applied
			$available_user_disc -= $discount;
			
		} //end for loop
		
		// if the available discount is greater than the order total, make the discount equal to the order total less any other discounts
		$discount_applied = max( 0, min( $discount_applied, $cartsubtotal ) );
		//$discount_applied = max( 0, $discount_applied );

		// limit the discount available by the global maximum discount if set
		$max_discount = $edd_options['edd_points_cart_max_discount'];

		if ( $max_discount && $max_discount < $discount_applied )
			$discount_applied = $max_discount;
		
		return $discount_applied;
	}
	/**
	 * Get Discount From Payment Data
	 * 
	 * Handles to get points from payment data
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_get_payment_discount( $payment_id ) {
		
		//discount given agains points
		$pointsdiscount = edd_get_payment_fees( $payment_id );
		
		$discount = 0;
		
		foreach ( $pointsdiscount as $key => $fee ) {
			if( isset( $fee['id'] ) && $fee['id'] == 'points_redeem' ) {
				$discount = abs( $fee['amount'] );
			}
		}
		return $discount;
	}
	/**
	 * Get Category Max Discount
	 * 
	 * Handles to get category max discount
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_get_category_earn_points( $downloadid ) {
		
		$downloadterms = wp_get_post_terms( $downloadid, 'download_category' );
		//$edd_price = edd_get_download_price( $downloadid );
		
		$cat_points = '';
		
		//get the return points from category
		foreach ( $downloadterms as $term ) {
			
			// get category meta data which stored in option table
			$termpoints = get_option( "download_category_$term->term_id" );
			
			$earn_points = isset( $termpoints['edd_points_earned'] ) ? $termpoints['edd_points_earned'] : '';
		
			//if points in percent then calculate it
			/*if ( strpos( $earn_points, '%' ) > 0 ) {
				$earn_points = $this->edd_points_discount_from_percent( $earn_points, $edd_price );
			}*/
			
			if ( ! is_numeric( $earn_points ) ) {
				continue;
			}
			
			//when download being assigned to multiple categoriew with different earned points, return biggest value of points
			if ( $earn_points >= (int) $cat_points ) {
				$cat_points = $earn_points;
			}
				
		} //end foreach loop
	
		return $cat_points;
	}
	/**
	 * Get Category Max Dicount
	 * 
	 * Handles to return maximum discount for category
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.0.0
	 **/
	public function edd_points_get_category_max_discount( $downloadid ) {
		
		$downloadterms = wp_get_post_terms( $downloadid, 'download_category' );
		//$edd_price = edd_get_download_price( $downloadid );
		
		$cat_max_discount = '';
		
		//get the return points from category
		foreach ( $downloadterms as $term ) {
			
			$termdisocunt = get_option( "download_category_$term->term_id" );
			$max_discount = isset( $termdisocunt['edd_points_max_discount'] ) ? $termdisocunt['edd_points_max_discount'] : '';
			
			//if points in percent then calculate it
			/*if ( strpos( $max_discount, '%' ) > 0 ) {
				$max_discount = $this->edd_points_discount_from_percent( $max_discount, $edd_price );
			}*/
			
			//return the minimum discount when download belonging to more then 1 categories
			if ( ! is_numeric( $cat_max_discount ) || intval( $max_discount ) < $cat_max_discount ) {
				$cat_max_discount = $max_discount;
			}
				
		} //end foreach loop
	
		return $cat_max_discount;
	}
	/**
	 * Get Download Points from Meta Box
	 * 
	 * Handles to get download points from Meta Box
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.1.0
	 **/
	public function edd_points_get_download_buy_points( $price ){
		
		global $edd_options;
		
		// Get charged points from settings page
		$points	= intval($edd_options['edd_points_buy_conversion']['points']);
		
		// Get charged ration from settings page
		$rate = intval($edd_options['edd_points_buy_conversion']['rate']);

		if ( empty( $points ) || empty( $rate ) ) {
			return 0;
		}
		
		return ceil( $price * ( $points / $rate ) );
	}
	/**
	 * Calculate Total Bought Points
	 * 
	 * Handles to calculate total bought
	 * points for cart data
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.1.0
	 **/
	public function edd_points_get_bought_points( $cartdata ) {

		$points = 0;
		
		//check cart not empty
		if( !empty( $cartdata ) ) {
			
			//calculate bought price
			foreach ( $cartdata as $items ) {
				
				//check download type is points
				if( edd_get_download_type( $items['id'] ) == 'points' ) {
					
					//check item of options set then consider that
					if( isset( $items['options'] ) ) { $itemoptions  = $items['options']; }
					//if item_number of options set then consider it
					elseif ( isset( $items['item_number']['options'] ) ) {  $itemoptions  = $items['item_number']['options']; }
					//else it will be blank array
					else { $itemoptions = array(); }
					
					//get cart item price
					$downloadprice = edd_get_cart_item_price( $items['id'], $itemoptions ) * $items['quantity'];
					
					//get buy points from download
					$points += $this->edd_points_get_download_buy_points( $downloadprice );
					
				}//end if to check download type is points
				
			}//end foreach loop
			
		} //check cart data should not empty
		
		//return points
		return $points;
	}
	/**
	 * Guest User Message for Points Download
	 * 
	 * Handles to return guest user points
	 * download purchase
	 * 
	 * @package Easy Digital Downloads - Points and Rewards
	 * @since 1.1.0
	 **/
	public function edd_points_guest_bought_download_message( $points = '' ) {
		
		global $edd_options;
		
		 $message = '';
		
		//check guest bought messsage is not empty and points not empty
		if( !empty( $edd_options['edd_points_bought_guest_messages'] ) && !empty( $points ) ) {
			
			//message 
			$guestmessage = $edd_options['edd_points_bought_guest_messages'];
			
			//points lable
			$points_label 		= $this->edd_points_get_points_label( $points );
			
			$points_replace 	= array( "{points}", "{points_label}" );
			$replace_message 	= array( $points, $points_label );
			$message			= $this->edd_points_replace_array( $points_replace, $replace_message, $guestmessage );
			
		}//check guest message is not empty & points not empty
		
		//return message
		return $message;
	}
}
?>