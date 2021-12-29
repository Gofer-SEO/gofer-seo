/**
 * Screen - Admin Page Module Debugger
 *
 * JavaScript code for the Debugger screen.
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

(function($) {
	window.addEventListener( 'load', function() {
		let goferSeoData = {
			nonce: gofer_seo_screen_module_debugger_l10n.nonce
		};

		document.getElementById('delete_errors').addEventListener( 'click', function(event) {
			// Prevents any unwanted actions.
			event.stopPropagation();
			event.preventDefault();

			var formData = new FormData();

			formData.append( 'action', 'gofer_seo_module_debugger_delete_errors' );
			formData.append( '_ajax_nonce', goferSeoData.nonce );
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false,

				success: function( data, textStatus, jqXHR ){
					window.location.reload();
				}
			});
		});

		document.getElementById('clear_cache').addEventListener( 'click', function(event) {
			// Prevents any unwanted actions.
			event.stopPropagation();
			event.preventDefault();

			var formData = new FormData();

			formData.append( 'action', 'gofer_seo_module_debugger_clear_cache' );
			formData.append( '_ajax_nonce', goferSeoData.nonce );
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false,

				success: function( data, textStatus, jqXHR ){}
			});
		});
	});
}(jQuery));
