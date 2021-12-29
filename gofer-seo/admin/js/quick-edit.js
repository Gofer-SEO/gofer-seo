/**
 * Post Quick-Edit
 *
 * @summary  Adds Quick-Edit functionality to the All Posts screen.
 *
 * @package  Gofer SEO
 * @since    1.0.0
 * @requires jQuery
 */

window.addEventListener('load', function() {
	let goferSeoData = {
		adminImagesURL: gofer_seo_l10n_post_editor_general.admin_images_URL,
		nonce:          gofer_seo_l10n_post_editor_general.nonce,
		i18n:           gofer_seo_l10n_post_editor_general.i18n
	};

	let quickEditWraps = document.getElementsByClassName( 'gofer-seo-quick-edit-wrap' );

	let i;
	for ( i = 0; i < quickEditWraps.length; i++ ) {
		let columnName = quickEditWraps[ i ].dataset.columnName;
		let postId     = quickEditWraps[ i ].dataset.postId;

		goferSeoQuickEditHandler( columnName, postId );
	}

	/**
	 * Gofer SEO Quick-Edit Handler
	 *
	 * @since 1.0.0
	 *
	 * @param {string} columnName
	 * @param {int}    postId
	 */
	function goferSeoQuickEditHandler( columnName, postId ) {
		this.postId     = postId;
		this.columnName = columnName;

		addListeners( this.columnName, this.postId );

		/**
		 * Add Listeners.
		 *
		 * @since 1.0.0
		 *
		 * @param {string} columnName
		 * @param {int}    postId
		 */
		function addListeners( columnName, postId ) {
			let field            = jQuery('#gofer_seo_' + columnName + '_' + postId);
			let dashicon         = field.parent().find('.gofer-seo-quick-edit-pencil').first();

			dashicon[0].addEventListener( 'click', function( event ) { handleOpenQuickEdit( event, columnName, postId ); }, false );
		}

		/**
		 * Handle Open Quick-Edit.
		 *
		 * @since 1.0.0
		 *
		 * @param {Event}  event
		 * @param {string} columnName
		 * @param {int}    postId
		 */
		function handleOpenQuickEdit( event, columnName, postId ) {
			event.stopPropagation();
			event.preventDefault();

			let field           = jQuery('#gofer_seo_' + columnName + '_' + postId);
			let dashicon        = field.parent().find('.gofer-seo-quick-edit-pencil').first();
			let initialElements = field.html();
			let value           = field.text().trim();

			field.addClass('gofer_seo_editing');

			let textarea = document.createElement('textarea');
			textarea.classList.add('gofer-seo-quick-edit-input');
			textarea.id   = 'gofer_seo_new_' + columnName + '_' + postId;
			textarea.rows = 4;
			textarea.cols = 32;

			if (goferSeoData.i18n.noValue !== value) {
				textarea.innerText = value;
			}

			let btnSave = document.createElement('a');
			btnSave.classList.add('dashicons', 'dashicons-yes-alt', 'gofer-seo-quick-edit-input-save');
			btnSave.id = 'gofer_seo_save_' + columnName + '_' + postId;
			btnSave.title = goferSeoData.i18n.save;

			btnSave.addEventListener('click', function( event ) {
				handleBtnSave( event, postId, columnName, initialElements );
			});

			let btnCancel = document.createElement('a');
			btnCancel.id = 'gofer_seo_cancel_' + columnName + '_' + postId;
			btnCancel.classList.add('dashicons', 'dashicons-dismiss', 'gofer-seo-quick-edit-input-cancel');
			btnCancel.title = goferSeoData.i18n.cancel;

			btnCancel.addEventListener('click', function() {
				dashicon.show();
				field.html(initialElements);
				field.removeClass('gofer_seo_editing');
			});

			let buttons = document.createElement('div');
			buttons.append( btnSave, btnCancel );

			dashicon.hide();
			field.empty().append( textarea, buttons );
		}

		/**
		 * Handle Save Button.
		 *
		 * @since 1.0.0
		 *
		 * @param {int}     postId          The ID of the post.
		 * @param {string}  columnName      The name of the column/attribute.
		 * @param {Element} initialElements The initial column elements (dashicon + span).
		 */
		function handleBtnSave( event, postId, columnName, initialElements ) {
			event.stopPropagation();
			event.preventDefault();

			let field      = jQuery('div#gofer_seo_' + columnName + '_' + postId);
			let inputValue = document.getElementById( 'gofer_seo_new_' + columnName + '_' + postId ).value;

			field.fadeOut('fast', function() {
				/**
				 *  @see admin/images/activity.gif
				 */
				let spinner = document.createElement('img');
				spinner.classList.add('gofer-seo-quickedit-spinner');
				spinner.src   = goferSeoData.adminImagesURL + 'activity.gif';
				spinner.align = 'absmiddle';

				let span = document.createElement('span');
				span.innerText   = goferSeoData.i18n.wait;
				span.style.float = 'left';

				let message = document.createElement('span');
				message.append(spinner, span);

				field.html( message );

				field.fadeIn('fast', function() {
					var formData = new FormData();
					formData.append( 'action', 'gofer_seo_post_editor_quick_edit' );
					formData.append( '_ajax_nonce', goferSeoData.nonce );

					formData.append( 'post_id', postId );
					formData.append( 'columnName', columnName );
					formData.append( 'value', inputValue.trim() );

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: formData,
						cache: false,
						processData: false,
						contentType: false,

						success: function() {
							field.empty().append(initialElements);
							field.removeClass('gofer_seo_editing');

							if ('image_title' === columnName) {
								//goferSeoMediaColumns.updatePostTitle(postId, inputValue);
							}

							if ('' === inputValue) {
								inputValue = '<strong>' + goferSeoData.i18n.noValue + '</strong>';
							}
							jQuery('#gofer_seo_' + columnName + '_' + postId + '_value').html(inputValue);
							addListeners( columnName, postId );
						},
						error: function() {
							field.empty().append(initialElements);
							field.removeClass('gofer_seo_editing');
							console.log('Request to update ' + columnName + ' failed.'); // jshint ignore:line
						}
					});
				});
			});
		}
	}
});
