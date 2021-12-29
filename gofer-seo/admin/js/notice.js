/**
 * Admin Notices for Gofer SEO.
 *
 * @summary  Handles the AJAX Actions with Gofer_SEO_Notifications
 *
 * @since    1.0.0
 * @package  Gofer SEO
 */
(function($) {

	/**
	 * Notice Delay - AJAX Action
	 *
	 * @summary Sets up the Delay Button listeners
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @global string $gofer_seo_notice_data.notice_nonce
	 * @listens gofer-seo-notice-delay-{notice_slug}-{delay_index}:click
	 *
	 * @param {string} noticeSlug
	 * @param {string} delayIndex
	 */
	function gofer_seo_notice_delay_ajax_action( noticeSlug, delayIndex ) {
		var noticeNonce   = gofer_seo_notice_data.notice_nonce;
		var noticeDelayID = '#gofer-seo-notice-delay-' + noticeSlug + '-' + delayIndex;
		$( noticeDelayID ).on( 'click', function( event ) {
			var elem_href = $( this ).attr( 'href' );
			if ( '#' === elem_href || '' === elem_href ) {
				// Stops automatic actions.
				event.stopPropagation();
				event.preventDefault();
			}

			var formData = new FormData();
			formData.append( 'notice_slug', noticeSlug );
			formData.append( 'action_index', delayIndex );

			formData.append( 'action', 'gofer_seo_notice' );
			formData.append( '_ajax_nonce', noticeNonce );
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false,

				success: function( data, textStatus, jqXHR ){
					var noticeContainer = '.gofer-seo-notice-' + noticeSlug;
					$( noticeContainer ).remove();
				}
			});
		});
	}

	/**
	 * Notice Delay - WP Default AJAX Action
	 *
	 * @summary
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @global string $gofer_seo_notice_data.notice_nonce
	 * @listens gofer-seo-notice-delay-{notice_slug}-{delay_index}:click
	 *
	 * @param {string} noticeSlug
	 */
	function gofer_seo_notice_delay_wp_default_dismiss_ajax_action( noticeSlug ) {
		var noticeNonce     = gofer_seo_notice_data.notice_nonce;
		var noticeContainer = '.gofer-seo-notice-' + noticeSlug;
		$( noticeContainer ).on( 'click', 'button.notice-dismiss ', function( event ) {
			// Prevents any unwanted actions.
			event.stopPropagation();
			event.preventDefault();

			var formData = new FormData();
			formData.append( 'notice_slug', noticeSlug );
			formData.append( 'action_index', 'default' );

			formData.append( 'action', 'gofer_seo_notice' );
			formData.append( '_ajax_nonce', noticeNonce );
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: formData,
				cache: false,
				dataType: 'json',
				processData: false,
				contentType: false
			});
		});
	}

	/**
	 * INITIALIZE NOTICE JS
	 *
	 * Constructs the actions the user may perform.
	 */
	let noticeDelays  = gofer_seo_notice_data.notice_actions;

	$.each( noticeDelays, function ( k1NoticeSlug, v1DelayArr ) {
		$.each( v1DelayArr, function ( k2I, v2DelayIndex ) {
			gofer_seo_notice_delay_ajax_action( k1NoticeSlug, v2DelayIndex );
		});

		// Default WP action for Dismiss Button on Upper-Right.
		gofer_seo_notice_delay_wp_default_dismiss_ajax_action( k1NoticeSlug );
	});
}(jQuery));
// phpcs:enable
