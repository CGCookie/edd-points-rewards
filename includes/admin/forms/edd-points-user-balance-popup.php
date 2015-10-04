<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * User Balance Popup
 *
 * This is the code for the pop up user balance, which shows up when an user clicks
 * on the adjust under points column in user listing.
 *
 * @package Easy Digital Downloads - Points and Rewards
 * @since 1.0.0
 **/
?>

<div class="edd-points-popup-content">

	<div class="edd-points-header">
		<div class="edd-points-header-title"><?php _e( 'Edit Users Points Balance', 'eddpoints' );?></div>
		<div class="edd-points-popup-close"><a href="javascript:void(0);" class="edd-points-close-button"><img src="<?php echo EDD_POINTS_IMG_URL;?>/tb-close.png" title="<?php _e( 'Close', 'eddpoints' );?>"></a></div>
	</div>
	
	<div class="edd-points-popup">
		
		<table class="form-table">
			<tr>
				<td width="25%">
					<?php _e( 'ID', 'eddpoints' ); ?>
				</td>
				<td width="35%">
					<?php _e( 'User', 'eddpoints' ); ?>
				</td>
				<td width="40%">
					<?php _e( 'Current Balance', 'eddpoints' ); ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><span id="edd_points_user_id"></span></strong>
				</td>
				<td>
					<strong><span id="edd_points_user_name"></span></strong>
				</td>
				<td>
					<strong><span id="edd_points_user_current_balance"></span></strong>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<label for="edd_points_update_users_balance_amount"><?php _e( 'Amount:', 'eddpoints' ); ?></label><br />
					<input type="text" value="" id="edd_points_update_users_balance_amount" name="edd_points_update_users_balance[amount]" /><br>
					<span class="description"><?php _e( 'A positive or negative value.', 'eddpoints' ); ?></span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<label for="edd_points_update_users_balance_entry"><?php _e( 'Log Entry:', 'eddpoints' ); ?></label><br />
					<input type="text" value="" id="edd_points_update_users_balance_entry" class="large-text" name="edd_points_update_users_balance[entry]" /><br>
					<span class="description"><?php _e( '(optional)', 'eddpoints' ); ?></span>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<input type="button" class="button button-primary edd-points-left" value="<?php _e( 'Update Balance', 'eddpoints' ); ?>" id="edd_points_update_users_balance_submit" name="edd_points_update_users_balance_submit" />
					<div class="edd-points-loader edd-points-left"><img src="<?php echo EDD_POINTS_IMG_URL;?>/loader.gif"/></div>
				</td>
			</tr>
		</table>
		
	</div><!--.edd-points-popup-->
	
</div><!--.edd-points-popup-content-->
<div class="edd-points-popup-overlay"></div>