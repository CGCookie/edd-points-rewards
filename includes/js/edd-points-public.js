//function for ajax pagination
function edd_points_ajax_pagination(pid){
	var data = {
					action: 'edd_points_next_page',
					paging: pid
				};
				
			jQuery('.edd-points-sales-loader').show();
			jQuery('.edd-points-paging').hide();
			
			jQuery.post(EDDPoints.ajaxurl, data, function(response) {
				var newresponse = jQuery( response ).filter( '.edd-points-user-log' ).html();
				jQuery('.edd-points-sales-loader').hide();
				jQuery('.edd-points-user-log').html( newresponse );
			});	
	return false;
}