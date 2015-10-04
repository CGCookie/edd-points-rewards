jQuery(document).ready(function($) {
	var eddpointsed;
	var eddpointsurl;
	//add the button to tinymce editor
	(function() {
	    tinymce.create('tinymce.plugins.edd_points_log', {
	        init : function(ed, url) {
	           eddpointsed = ed;
			   eddpointsurl = url;
	        },
	        createControl : function(n, cm) {
	        	switch (n) {
					case 'edd_points_log':
						var c = cm.createMenuButton('edd_points_log', {
							title : 'EDD Points List',
							image : eddpointsurl+'/coins.png',
							onclick : function() {
								shortcodestr = '';
								shortcodestr += '[edd_points_log][/edd_points_log]';	
			                    tinymce.get('content').execCommand('mceInsertContent',false, shortcodestr);
			 				}
					});
					
					// Return the new splitbutton instance
					
					return c;
				}
				return null;
			}
		});
		tinymce.PluginManager.add('edd_points_log', tinymce.plugins.edd_points_log);
	
	})();
	
});