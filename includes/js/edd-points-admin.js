jQuery(document).ready( function($) {

	$( document ).on( "click", ".edd-points-editor-popup", function() {
		
		var username = $(this).parents('._edd_userpoints').siblings( 'td.column-username' ).find( 'strong>a' ).text();
		var user_id = $(this).attr('data-userid');
		var balance = $(this).attr('data-current');
		
		$( '#edd_points_user_id' ).html(user_id);
		$( '#edd_points_user_name' ).html(username);
		$( '#edd_points_user_current_balance' ).html(balance);
		
		$( '#edd_points_update_users_balance_amount' ).val('');
		$( '#edd_points_update_users_balance_entry' ).val('');
		
		$( '.edd-points-popup-overlay' ).fadeIn();
        $( '.edd-points-popup-content' ).fadeIn();
	});
	
	//close popup window 
	$( document ).on( "click", ".edd-points-close-button, .edd-points-popup-overlay", function() {
		
		$( '.edd-points-popup-overlay' ).fadeOut();
        $( '.edd-points-popup-content' ).fadeOut();
        
	});
	
	//update user balance
	$( document ).on( "click", "#edd_points_update_users_balance_submit", function() {
		
		var userid = $( 'span#edd_points_user_id' ).text();
		var points = $( '#edd_points_update_users_balance_amount' ).val();
		var log = $( '#edd_points_update_users_balance_entry' ).val();
		
		$( '#edd_points_update_users_balance_amount' ).removeClass('edd-points-validate-error');
		
		if( points != '' ) {
			 
			$('#edd_points_update_users_balance_submit').val( Edd_Points_Admin.processing_balance );
			var data = {
							action	: 'edd_points_adjust_user_points',
							userid	: userid,
							points	: points,
							log		: log
						};
			//call ajax to adjust points
			jQuery.post( ajaxurl, data, function( response ) {
				//alert( response );
				if( response != 'error' ) {
					$( '#edd_points_user_current_balance' ).html( response );
					$( '#edd_points_user_' + userid + '_balance' ).html( response );
					$( '#edd_points_user_' + userid + '_adjust' ).attr( 'data-current', response );
				}
				$('#edd_points_update_users_balance_amount').val('');
				$('#edd_points_update_users_balance_entry').val('');
				$('#edd_points_update_users_balance_submit').val( Edd_Points_Admin.update_balance );
        		
			});
		} else {
			$( '#edd_points_update_users_balance_amount' ).addClass('edd-points-validate-error');
		}
	});
	
	$('.edd-points-dropdown-wrapper select').css('width', '250px').chosen();
	$('select#edd_points_userid').ajaxChosen({
	    method: 		'GET',
	    url: 			ajaxurl,
	    dataType: 		'json',
	    afterTypeDelay: 100,
	    minTermLength: 	1,
	    data: {
		    	action: 		'edd_points_search_users',
		    	select_default: ''
	    }
	}, function (data) {

		var terms = {};

	    jQuery.each(data, function (i, val) {
	        terms[i] = val;
	    });

	    return terms;
	});
	
	//confirmation for applying discount buttons
	$( document ).on( "click", ".edd-points-apply-disocunts-prev-orders", function() {
		
		var confirmdiscount = confirm( Edd_Points_Admin.prev_order_apply_confirm_message );
		 
		if( confirmdiscount ) {
			return true;
		} else {
			return false;
		}
	});
	
});