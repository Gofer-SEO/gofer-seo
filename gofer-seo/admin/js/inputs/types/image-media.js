/**
 * WP Media Uploader
 *
 * @summary     Adds media library support to image inputs.
 *
 * @package  Gofer SEO
 * @since    1.0.0
 * @requires jQuery
 */

(function($) {
	$('body').on('click', '.gofer-seo-image-media-button', function(e) {
		var self = this;
		var imageTextInput = $(self).parent().find( '.gofer-seo-image-media-text' );
		// var imageSample    = $(self).parent().parent().find( '.gofer-seo-image-media-img' );
		e.preventDefault();

		self.uploader = wp.media({
				title: 'Image Media',
				library : {
					// uploadedTo : wp.media.view.settings.post.id,
					type : 'image'
				},
				button: {
					text: 'Select Image'
				},
				multiple: false
			}).on('select', function() {
				var attachment = self.uploader.state().get('selection').first().toJSON();

				$( imageTextInput ).val( attachment.id );
				imageTextInput[0].dispatchEvent( new Event( 'change' ) );
				// $( imageSample ).attr( 'src', attachment.url );
			}).open();
	});
}(jQuery));
