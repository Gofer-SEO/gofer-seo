/**
 * Input Type - Snippet Default
 *
 * @summary     Constructs the snippet preview, and keeps the snippet in sync by handling any changes to the content.
 *
 * @package     Gofer SEO
 * @since       1.0.0
 */

/* global tinymce */
window.addEventListener('load', function() {
	let goferSeoData = {
		siteURL                            : gofer_seo_l10n_snippet.site_url,
		generateDescriptionEnableGenerator : ( '1' === gofer_seo_l10n_snippet.generate_description_enable_generator ) ? true : false,
		generateDescriptionUseContent      : ( '1' === gofer_seo_l10n_snippet.generate_description_use_content ) ? true : false,
		generateDescriptionUseExcerpt      : ( '1' === gofer_seo_l10n_snippet.generate_description_use_excerpt ) ? true : false,
		enableTrimDescription              : ( '1' === gofer_seo_l10n_snippet.enable_trim_description ) ? true : false
	};

	goferSeoInputSnippetPreview( goferSeoData );
});

/**
 * (Gofer SEO) Input - Snippet Preview.
 *
 * title 50-60
 * Description ~155-160
 *
 * @param {Object} data
 */
function goferSeoInputSnippetPreview( data ) {
	this.descriptionLengthTarget         = 160;
	this.descriptionLengthMax            = 340;
	this.snippetTitleClassName           = 'gofer-seo-snippet-title';
	this.snippetDescriptionClassName     = 'gofer-seo-snippet-description';
	this.snippetDescriptionLongClassName = 'gofer-seo-snippet-description-long';
	this.snippetSiteURLClassName         = 'gofer-seo-snippet-site-url';
	this.titleInputName                  = 'gofer_seo_modules-general-title';
	this.descriptionInputName            = 'gofer_seo_modules-general-description';

	this.siteTitle                     = 'Title';
	this.siteDescription               = 'Description';
	this.enableDescriptionGenerator    = data.generateDescriptionEnableGenerator;
	this.generateDescriptionUseContent = data.generateDescriptionUseContent;
	this.generateDescriptionUseExcerpt = data.generateDescriptionUseExcerpt;
	this.enableTrimDescription         = data.enableTrimDescription;

	addListeners();
	syncSnippet();

	/**
	 * Add Listeners.
	 *
	 * @since 1.0.0
	 */
	function addListeners() {
		let titleEle       = document.getElementsByName( 'gofer_seo_modules-general-title' )[0];
		let descriptionEle = document.getElementsByName( 'gofer_seo_modules-general-description' )[0];

		titleEle.addEventListener( 'keyup', function(e) { handleInputChange(e); }, false );
		descriptionEle.addEventListener( 'keyup', function(e) { handleInputChange(e); }, false );

		if ( isGutenbergEditor() ) {
			if ( typeof window._wpLoadBlockEditor !== 'undefined' ) {
				window._wpLoadBlockEditor.then( function() {
					// Block editor is initialized.

					wp.data.subscribe( function () {
						if ( 'select' in wp.data ) {
							let coreEditor = wp.data.select('core/editor');
							if ( coreEditor.isSavingPost() || coreEditor.isAutosavingPost() || coreEditor.hasChangedContent() ) {
								syncSnippet();
							}
						}
					});
				});
			}
		} else {
			let postTitleEle      = document.getElementById( 'title' );
			let postExcerptEle    = document.getElementById( 'excerpt' );
			let wpSwitchEditorEle = document.getElementsByClassName( 'wp-switch-editor' );

			postTitleEle.addEventListener( 'keyup', function(e) { handleInputChange(e); }, false );
			postExcerptEle.addEventListener( 'keyup', function(e) { handleInputChange(e); }, false );
			addListenersClassicEditorContent();

			let i;
			for ( i = 0; i < wpSwitchEditorEle.length; i++ ) {
				wpSwitchEditorEle[ i ].addEventListener( 'click', function(e) { addListenersClassicEditorContent(); } );
			}
		}
	}

	/**
	 * Add Listeners - Classic Editor Content.
	 *
	 * @since 1.0.0
	 */
	function addListenersClassicEditorContent() {
		if ( isClassicVisualTab() ) {
			tinymce.editors[0].addEventListener( 'keyup', function(e) { handleInputChange(e); }, false );
		} else {
			let editAreaEle = document.getElementsByClassName( 'wp-editor-area' )[0];
			editAreaEle.addEventListener( 'change', function(e) { handleInputChange(e); }, false );
		}
	}

	/**
	 * Handle Input Change.
	 *
	 * @since 1.0.0
	 *
	 * @param {Event} event
	 */
	function handleInputChange( event ) {
		syncSnippet();
	}

	/**
	 * Sync Snippet.
	 *
	 * @since 1.0.0
	 */
	function syncSnippet() {
		let title           = '';
		let description     = '';
		let descriptionLong = '';

		// Title.
		let postTitle = getWPTitle();
		let metaTitle = getGoferSeoTitle();
		title = postTitle;
		if ( '' !== metaTitle ) {
			title = metaTitle;
		}

		// Description.
		let postDescription = '';
		if ( this.enableDescriptionGenerator ) {
			let postContent = getWPContent();
			let postExcerpt = getWPExcerpt();
			if ( this.generateDescriptionUseExcerpt && '' !== postExcerpt ) {
				postDescription = postExcerpt;
			} else if ( this.generateDescriptionUseContent && '' !== postContent ) {
				postDescription = postContent;
			}
		}

		let metaDescription = getGoferSeoDescription();
		description = postDescription;
		if ( '' !== metaDescription ) {
			description = metaDescription;
		}

		// Clean description.
		description     = stripTags( description );
		postDescription = stripTags( postDescription );

		description     = trimWords( description, 3000 );
		if ( description.length > this.descriptionLengthTarget ) {
			descriptionLong = description;
			if ( this.enableTrimDescription ) {
				descriptionLong = trimWords( descriptionLong, this.descriptionLengthMax );
			}
		}
		description     = trimWords( description, this.descriptionLengthTarget );
		descriptionLong = descriptionLong.replace( description, '' );
		postDescription = trimWords( postDescription, this.descriptionLengthTarget );

		if ( '' === title ) {
			title = this.siteTitle;
		}
		if ( '' === description ) {
			description = this.siteDescription;
		}

		setSnippet( title, description, descriptionLong );
		setInputPlaceholders( postTitle, postDescription );
	}

	/**
	 * Set Snippet.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} title
	 * @param {string} description
	 * @param {string} extraDescription
	 */
	function setSnippet( title, description, extraDescription ) {
		let i;

		// Title.
		let snippetTitleElements = document.getElementsByClassName( this.snippetTitleClassName );
		for ( i = 0; i < snippetTitleElements.length; i++ ) {
			snippetTitleElements[ i ].innerText = title;
		}

		// Description.
		let snippetDescriptionElements = document.getElementsByClassName( this.snippetDescriptionClassName );
		for ( i = 0; i < snippetDescriptionElements.length; i++ ) {
			snippetDescriptionElements[ i ].innerText = description;
		}

		let snippetDescriptionLongElements = document.getElementsByClassName( this.snippetDescriptionLongClassName );
		for ( i = 0; i < snippetDescriptionLongElements.length; i++ ) {
			snippetDescriptionLongElements[ i ].innerText = extraDescription;
		}
	}

	/**
	 * Set Input Placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} title
	 * @param {string} description
	 */
	function setInputPlaceholders( title, description ) {
		let i;

		// Title.
		let titleInputElements = document.getElementsByName( this.titleInputName );
		for ( i = 0; i < titleInputElements.length; i++ ) {
			titleInputElements[ i ].setAttribute( 'placeholder', title );
		}

		// Description.
		let descriptionInputElements = document.getElementsByName( this.descriptionInputName );
		for ( i = 0; i < descriptionInputElements.length; i++ ) {
			descriptionInputElements[ i ].setAttribute( 'placeholder', description );
		}
	}

	/* **___________***************************************************************************************************/
	/* _/ Post Edit \_________________________________________________________________________________________________*/

	/**
	 * Get Gofer SEO Title.
	 *
	 * @since 1.0.0
	 *
	 * @returns {string}
	 */
	function getGoferSeoTitle() {
		return document.getElementsByName( this.titleInputName )[0].value;
	}

	/**
	 * Get Gofer SEO Description.
	 *
	 * @since 1.0.0
	 *
	 * @returns {string}
	 */
	function getGoferSeoDescription() {
		return document.getElementsByName( this.descriptionInputName )[0].value;
	}

	/**
	 * Get WP Title.
	 *
	 * @since 1.0.0
	 *
	 * @returns {string}
	 */
	function getWPTitle() {
		let title = '';
		if ( isGutenbergEditor ) {
			title = document.getElementById( 'post-title-0' ).value;
		} else {
			title = document.getElementById( 'title' ).innerText;
		}

		return title.trim();
	}

	/**
	 * Get WP Content.
	 *
	 * @since 1.0.0
	 *
	 * @returns {string}
	 */
	function getWPContent() {
		let content = '';
		if ( isGutenbergEditor() ) {
			content = wp.data.select('core/editor').getEditedPostAttribute('content');
		} else {
			if ( isClassicVisualTab() ) {
				// Visual Tab.
				content = tinymce.activeEditor.getContent({format : 'raw'});
			} else {
				// Raw/HTML Tab.
				content = document.getElementsByClassName( 'wp-editor-area' )[0].innerHTML;
			}
		}

		return content.trim();
	}

	/**
	 * Get WP Excerpt.
	 *
	 * @since 1.0.0
	 *
	 * @returns {string}
	 */
	function getWPExcerpt() {
		let excerpt = '';
		if ( isGutenbergEditor() ) {
			excerpt = wp.data.select('core/editor').getEditedPostAttribute('excerpt');
		} else {
			excerpt = document.getElementById( 'excerpt' ).value;
		}

		return excerpt.trim();
	}

	/**
	 * Is Gutenberg Editor.
	 *
	 * @since 1.0.0
	 *
	 * @returns {boolean}
	 */
	function isGutenbergEditor() {
		let regexMatch = new RegExp( '(block-editor-page)' );
		if ( document.body.className.match( regexMatch ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Is Classic Visual Tab.
	 *
	 * @since 1.0.0
	 *
	 * @returns {boolean}
	 */
	function isClassicVisualTab() {
		let regexMatch = new RegExp( '(tmce-active)' );
		if ( document.getElementById( 'wp-content-wrap' ).className.match( regexMatch ) ) {
			return true;
		}

		return false;
	}

	/* **________******************************************************************************************************/
	/* _/ Common \____________________________________________________________________________________________________*/
	/**
	 * Trim Words.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} str    The string to modify.
	 * @param {int}    length The max character limit.
	 * @returns {string}
	 */
	function trimWords( str, length ) {
		return str && str.length > length ? str.slice(0,length).split(' ').slice(0, -1).join(' ') : str;
	}

	/**
	 * Strip Tags.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} str The string to modify.
	 * @returns {string}
	 */
	function stripTags( str ) {
		let strNew = str;
		// Remove HTML tags.
		strNew = strNew.replace( /(<[^ >][^>]*>)?/gm, '' );
		// Replace line breaks with whitespace.
		strNew = strNew.replace( /[\r\n]+/gm, ' ' );

		let tmpEle = document.createElement( 'textarea' );
		tmpEle.innerHTML = strNew;
		return tmpEle.value.trim();
	}
}
