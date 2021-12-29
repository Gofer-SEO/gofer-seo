/**
 * Input Type - Add Field Robots.txt
 *
 * Coded specifically for the Robots.txt settings UI. Some of the code could be refactored
 * with add_field_list input typesets coding-pattern; could also be a new pattern for
 * multi-dimensional string array options ({key}, {=>key}, {=>=>value}).
 *
 * @package Gofer SEO
 * @since 1.0.0
 */

window.addEventListener( 'load', function() {
	let goferSeoData = {
		inputTypesets: gofer_seo_l10n_add_field_robots_txt.input_typesets,
		originalRules: gofer_seo_l10n_add_field_robots_txt.original_rules
	};

	let inputName;
	for ( inputName in goferSeoData.inputTypesets ) {
		goferSeoInputAddFieldRobotsTxt( inputName, goferSeoData.inputTypesets[ inputName ], goferSeoData.originalRules );
	}
});

/**
 * Input - Add Field Robots.txt.
 *
 * @since 1.0.0
 *
 * @param {string}  inputName
 * @param {Array[]} inputTypeset
 * @param {Array[]} originalRules
 */
function goferSeoInputAddFieldRobotsTxt( inputName, inputTypeset, originalRules ) {
	this.inputName     = inputName;
	this.inputTypeset  = inputTypeset;
	this.originalRules = originalRules;

	addListeners( this.inputName, this.inputTypeset );
	setUserAgentsTable( getData( this.inputName ) );
	setSitemapsTable( getData( this.inputName ) );
	setRobotsTxtPreview( getData( this.inputName ) );

	/**
	 * Add Listeners.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} inputName
	 * @param {array}  inputTypeset
	 */
	function addListeners( inputName, inputTypeset ) {
		let buttonAddUserAgent = document.getElementsByClassName( 'gofer-seo-input-name-' + inputName )[0].querySelector( '.gofer-seo-add-item-robots-txt-user-agent-button' );
		buttonAddUserAgent.addEventListener( 'click', function(e) { handleAddUserAgent( e, inputName, inputTypeset ); }, false );

		let selectUserAgentType = document.getElementsByClassName( 'gofer-seo-input-name-' + inputName + '-user_agents-rule_type' )[0].querySelector( 'select[name="robots_txt_rules-user_agents-rule_type"]' );
		selectUserAgentType.addEventListener( 'change', function(e) { handleUserAgentTypeInput( e, this, inputName ); }, false );

		let buttonAddSitemap = document.getElementsByClassName( 'gofer-seo-input-name-' + inputName )[0].querySelector( '.gofer-seo-add-item-robots-txt-sitemap-button' );
		buttonAddSitemap.addEventListener( 'click', function(e) { handleAddSitemap( e, inputName, inputTypeset ); }, false );

		let overrideEle = document.getElementById( 'enable_override_robots_txt' );
		overrideEle.addEventListener( 'click', function(e) { handleOverride( e, this ); }, false );
	}

	/**
	 * Handle Override.
	 *
	 * @since 1.0.0
	 *
	 * @param {Event}   e
	 * @param {Element} ele
	 */
	function handleOverride( e, ele ) {
		setRobotsTxtPreview( getData( self.inputName ) );
	}

	/**
	 * Handle User Agent Type on Change.
	 *
	 * @since 1.0.0
	 *
	 * @param {Event}   e
	 * @param {Element} ele
	 * @param {string}  inputName
	 */
	function handleUserAgentTypeInput( e, ele, inputName ) {
		let ruleValueEle = document.getElementsByClassName( 'gofer-seo-input-name-' + inputName + '-user_agents-rule_value' )[0].querySelector( 'input[name="robots_txt_rules-user_agents-rule_value"]' );
		ruleValueEle.value = '';
		if ( 'crawl_delay' === ele.value ) {
			ruleValueEle.type = 'number';
			ruleValueEle.min = 1;
			ruleValueEle.value = 1;
		} else {
			ruleValueEle.type = 'text';
			ruleValueEle.value = '/';
		}
	}

	/**
	 * Handle Add User Agent.
	 *
	 * @since 1.0.0
	 *
	 * @param {Event}   e
	 * @param {string}  inputName
	 * @param {Array[]} inputTypeset
	 */
	function handleAddUserAgent( e, inputName, inputTypeset ) {
		e.stopPropagation();
		e.preventDefault();

		let userAgentsInputName = inputName + '-user_agents';
		let userAgentsTypeset   = inputTypeset['wrap']['user_agents'];

		let inputFieldsData = getFieldsValue( userAgentsInputName, userAgentsTypeset['wrap_dynamic'] );

		// TODO vvv ? Create function getFieldsDefaults().
		let defaultFieldsValue = [];
		let inputTypesetName;
		for ( inputTypesetName in userAgentsTypeset['wrap_dynamic'] ) {
			defaultFieldsValue[ inputTypesetName ] = '';
			if (
				typeof userAgentsTypeset['wrap_dynamic'][ inputTypesetName ]['attrs'] !== 'undefined' &&
				typeof userAgentsTypeset['wrap_dynamic'][ inputTypesetName ]['attrs']['value'] !== 'undefined'
			) {
				defaultFieldsValue[ inputTypesetName ] = userAgentsTypeset['wrap_dynamic'][ inputTypesetName ]['attrs']['value'];
			}
		}
		// TODO ^^^ ? Create function getFieldsDefaults().

		// Set input fields back to default.
		setFieldsValue( userAgentsInputName, userAgentsTypeset['wrap_dynamic'], defaultFieldsValue );

		if ( '' === inputFieldsData['user_agent'] ) {
			// TODO Add WP_DEBUG & Gofer SEO Log.
			// alert( 'Empty User Agent.' ); // eslint-disable-line no-alert
		} else {
			// Add data to Input.
			let inputData = getData( inputName );
			if ( typeof inputData['user_agents'][ inputFieldsData['user_agent'] ] === 'undefined' ) {
				// Set new/unset variable.
				inputData['user_agents'][ inputFieldsData['user_agent'] ] = {
					'user_agent'  : inputFieldsData['user_agent'],
					'crawl_delay' : 0,
					'path_rules'  : {}
				};
			}

			if ( 'crawl_delay' === inputFieldsData['rule_type'] ) {
				inputData['user_agents'][ inputFieldsData['user_agent'] ]['crawl_delay'] = inputFieldsData['rule_value'];
			} else if ( 'allow' === inputFieldsData['rule_type'] || 'disallow' === inputFieldsData['rule_type'] ) {
				inputData['user_agents'][ inputFieldsData['user_agent'] ]['path_rules'][ inputFieldsData['rule_value'] ] = inputFieldsData['rule_type'];
			} else {
				return;
			}

			setData(inputName, inputData);

			// Add Item to List.
			setUserAgentsTable( inputData );
			setRobotsTxtPreview( inputData );
		}
	}

	/**
	 * Handle Add User Agent.
	 *
	 * @since 1.0.0
	 *
	 * @param {Event}   e
	 * @param {string}  inputName
	 * @param {Array[]} inputTypeset
	 */
	function handleAddSitemap( e, inputName, inputTypeset ) {
		e.stopPropagation();
		e.preventDefault();

		let userSitemapsInputName = inputName + '-sitemaps';
		let userSitemapsTypeset   = inputTypeset['wrap']['sitemaps'];

		let inputFieldsData = getFieldsValue( userSitemapsInputName, userSitemapsTypeset['wrap_dynamic'] );

		// TODO vvv ? Create function getFieldsDefaults().
		let defaultFieldsValue = [];
		let inputTypesetName;
		for ( inputTypesetName in userSitemapsTypeset['wrap_dynamic'] ) {
			defaultFieldsValue[ inputTypesetName ] = '';
			if (
				typeof userSitemapsTypeset['wrap_dynamic'][ inputTypesetName ]['attrs'] !== 'undefined' &&
				typeof userSitemapsTypeset['wrap_dynamic'][ inputTypesetName ]['attrs']['value'] !== 'undefined'
			) {
				defaultFieldsValue[ inputTypesetName ] = userSitemapsTypeset['wrap_dynamic'][ inputTypesetName ]['attrs']['value'];
			}
		}
		// TODO ^^^ ? Create function getFieldsDefaults().

		// Set input fields back to default.
		setFieldsValue( userSitemapsInputName, userSitemapsTypeset['wrap_dynamic'], defaultFieldsValue );

		// Add data to Input.
		let inputData = getData( inputName );
		if ( '' === inputFieldsData['sitemap'] ) {
			// TODO Add WP_DEBUG & Gofer SEO Log.
			// alert( 'Empty Sitemap URL.' ); // eslint-disable-line no-alert
		} else {
			inputData['sitemaps'][ inputData['sitemaps'].length ] = inputFieldsData['sitemap'];
			inputData['sitemaps'] = [... new Set(inputData['sitemaps'])];

			setData(inputName, inputData);

			// Add Item to List.
			setSitemapsTable( inputData );
			setRobotsTxtPreview( inputData );
		}
	}

	/**
	 * Handle Item Remote (Trash) Button.
	 *
	 * @since 1.0.0
	 *
	 * @param {Event}   e
	 * @param {Element} ele
	 */
	function handleItemRemoveButton( e, ele ) {
		let inputData = getData( inputName );

		if ( 'user_agents' === ele.dataset.prop0 ) {
			switch ( ele.dataset.prop2 ) {
				case 'crawl_delay':
					inputData[ ele.dataset.prop0 ][ ele.dataset.prop1 ][ ele.dataset.prop2 ] = 0;
					break;
				case 'path_rules':
					delete inputData[ ele.dataset.prop0 ][ ele.dataset.prop1 ][ ele.dataset.prop2 ][ ele.dataset.prop3 ];
					break;
			}

			if (
					0 === parseInt( inputData[ ele.dataset.prop0 ][ ele.dataset.prop1 ]['crawl_delay'], 10 ) &&
					0 === Object.keys( inputData[ ele.dataset.prop0 ][ ele.dataset.prop1 ]['path_rules'] ).length
			) {
				delete inputData[ ele.dataset.prop0 ][ ele.dataset.prop1 ];
			}

			setData(self.inputName, inputData);

			// Add Item to List.
			setUserAgentsTable( inputData );
			setRobotsTxtPreview( inputData );
		} else if ( 'sitemaps' === ele.dataset.prop0 ) {
			delete inputData[ ele.dataset.prop0 ][ ele.dataset.prop1 ];

			inputData[ ele.dataset.prop0 ] = inputData[ ele.dataset.prop0 ].filter(function (item) { return typeof item !== 'undefined'; });

			setData(self.inputName, inputData);
			// Add Item to List.
			setSitemapsTable( inputData );
			setRobotsTxtPreview( inputData );
		}
	}

	/**
	 * Get Values from Fields.
	 *
	 * @since 1.0.0
	 *
	 * @param {string}  inputName
	 * @param {Array[]} inputTypesets
	 * @returns {Array}
	 */
	function getFieldsValue( inputName, inputTypesets ) {
		let inputFieldsData = [];

		let inputTypesetName;
		let inputFieldEle;
		for ( inputTypesetName in inputTypesets ) {
			inputFieldEle = document.getElementsByName( inputName + '-' + inputTypesetName )[0];
			if ( typeof inputFieldEle.value !== 'undefined' ) {
				inputFieldsData[ inputTypesetName ] = inputFieldEle.value;
			} else {
				inputFieldsData[ inputTypesetName ] = 'NULL';
			}
		}

		return inputFieldsData;
	}

	/**
	 * Set Fields Value.
	 *
	 * @since 1.0.0
	 *
	 * @param {string}  inputName
	 * @param {Array[]} inputTypesets
	 * @param {Array}   values
	 */
	function setFieldsValue( inputName, inputTypesets, values ) {
		let inputTypesetName;
		for ( inputTypesetName in inputTypesets ) {
			if ( typeof values[ inputTypesetName ] !== 'undefined' ) {
				document.getElementsByName( inputName + '-' + inputTypesetName )[0].value = values[ inputTypesetName ];
				document.getElementsByName( inputName + '-' + inputTypesetName )[0].dispatchEvent( new Event( 'change' ) );
			}
		}
	}

	/**
	 * Get Input Data.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} inputName
	 */
	function getData( inputName ) {
		let inputData = document.getElementsByName( inputName )[0].value;

		inputData = JSON.parse( inputData );

		return inputData;
	}

	/**
	 * Set Input Data.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} inputName
	 * @param {Array}  inputData
	 */
	function setData(inputName, inputData) {
		document.getElementsByName( inputName )[0].value = JSON.stringify( inputData );
	}

	/**
	 * Set Table List.
	 *
	 * @since 1.0.0
	 *
	 * @param {Array[]} inputData
	 */
	function setUserAgentsTable( inputData ) {
		let elementWrap  = document.getElementsByClassName( 'gofer-seo-input-type-add-field-robots-txt' )[0];
		let elementTable = elementWrap.querySelector( 'table.gofer-seo-add-field-robots_txt_rules-user_agents-table' );

		elementTable.removeChild( elementTable.querySelector( 'tbody' ) );
		let elementTableBody = elementTable.createTBody();

		if ( 0 < Object.keys( inputData['user_agents'] ).length ) {
			elementTable.style.display = 'table';

			let dataIndex;
			let row;
			for ( dataIndex in inputData['user_agents'] ) {
				let cell;
				if ( 0 !== inputData['user_agents'][ dataIndex ]['crawl_delay'] ) {
					let trashButton;
					trashButton               = document.createElement( 'a' );
					trashButton.className     = 'dashicons dashicons-trash';
					trashButton.style.cursor  = 'pointer';
					trashButton.style.color   = '#e46363';
					trashButton.dataset.prop0 = 'user_agents';
					trashButton.dataset.prop1 = dataIndex;
					trashButton.dataset.prop2 = 'crawl_delay';
					trashButton.dataset.prop3 = '';
					trashButton.addEventListener( 'click', function(e) { handleItemRemoveButton( e, this ); }, false );

					row = elementTableBody.insertRow();
					row.insertCell().appendChild( trashButton );

					cell = row.insertCell();
					cell.appendChild( document.createTextNode( dataIndex ) );
					cell = row.insertCell();
					cell.appendChild( document.createTextNode( 'Crawl-Delay' ) );
					cell = row.insertCell();
					cell.appendChild( document.createTextNode( parseInt( inputData['user_agents'][ dataIndex ]['crawl_delay'], 10 ) ) );
				}

				let ruleTypeText;
				let pathRulesIndex;
				for ( pathRulesIndex in inputData['user_agents'][ dataIndex ]['path_rules'] ) {
					ruleTypeText = 'Allow';
					if ( 'disallow' === inputData['user_agents'][ dataIndex ]['path_rules'][ pathRulesIndex ] ) {
						ruleTypeText = 'Disallow';
					}

					let trashButton;
					trashButton               = document.createElement( 'a' );
					trashButton.className     = 'dashicons dashicons-trash';
					trashButton.style.cursor  = 'pointer';
					trashButton.style.color   = '#e46363';
					trashButton.dataset.prop0 = 'user_agents';
					trashButton.dataset.prop1 = dataIndex;
					trashButton.dataset.prop2 = 'path_rules';
					trashButton.dataset.prop3 = pathRulesIndex;
					trashButton.addEventListener( 'click', function(e) { handleItemRemoveButton( e, this ); }, false );

					row = elementTableBody.insertRow();
					row.insertCell().appendChild( trashButton );

					cell = row.insertCell();
					cell.appendChild( document.createTextNode( dataIndex ) );
					cell = row.insertCell();
					cell.appendChild( document.createTextNode( ruleTypeText ) );
					cell = row.insertCell();
					cell.appendChild( document.createTextNode( pathRulesIndex ) );
				}
			}
		}
	}

	/**
	 * Set Table List.
	 *
	 * @since 1.0.0
	 *
	 * @param {Array[]} inputData
	 */
	function setSitemapsTable( inputData ) {
		let elementWrap  = document.getElementsByClassName( 'gofer-seo-input-type-add-field-robots-txt' )[0];
		let elementTable = elementWrap.querySelector( 'table.gofer-seo-add-field-robots_txt_rules-sitemaps-table' );

		elementTable.removeChild( elementTable.querySelector( 'tbody' ) );
		let elementTableBody = elementTable.createTBody();

		if ( 0 < Object.keys( inputData['sitemaps'] ).length ) {
			elementTable.style.display = 'table';

			let dataIndex;
			let row;
			let cell;
			for ( dataIndex in inputData['sitemaps'] ) {
				let trashButton;
				trashButton               = document.createElement( 'a' );
				trashButton.className     = 'dashicons dashicons-trash';
				trashButton.style.cursor  = 'pointer';
				trashButton.style.color   = '#e46363';
				trashButton.dataset.prop0 = 'sitemaps';
				trashButton.dataset.prop1 = dataIndex;
				trashButton.dataset.prop2 = '';
				trashButton.dataset.prop3 = '';
				trashButton.addEventListener( 'click', function(e) { handleItemRemoveButton( e, this ); }, false );

				row = elementTableBody.insertRow();
				row.insertCell().appendChild( trashButton );

				cell = row.insertCell();
				cell.appendChild( document.createTextNode( inputData['sitemaps'][ dataIndex ] ) );
			}
		}
	}

	/**
	 * Set Robots.txt Preview.
	 *
	 * @since 1.0.0
	 *
	 * @param {Array[]} inputData
	 */
	function setRobotsTxtPreview( inputData ) {
		let output = '';
		let elementWrap     = document.getElementsByClassName( 'gofer-seo-input-type-add-field-robots-txt' )[0];
		let elementTextarea = elementWrap.querySelector( 'textarea[name="robots_txt_rules-preview"]' );

		// TODO Check Override robots input.
		let overrideEle = document.getElementById( 'enable_override_robots_txt' );
		if ( false === overrideEle.checked ) {
			// inputData = mergeRules( self.originalRules, inputData );
			inputData = mergeRules( Object.assign({}, self.originalRules), inputData );
		}

		if ( 0 < inputData['sitemaps'].length ) {
			let index;
			for ( index in inputData['sitemaps'] ) {
				output += 'Sitemap: ' + inputData['sitemaps'][ index ] + '\n';
			}
			output += '\n';
		}

		let userAgentName;
		let path;
		for ( userAgentName in inputData['user_agents'] ) {

			output += 'User-agent: ' + userAgentName + '\n';

			if ( 0 !== parseInt( inputData['user_agents'][ userAgentName ]['crawl_delay'], 10 ) ) {
				output += 'Crawl-delay: ' + inputData['user_agents'][ userAgentName ]['crawl_delay'] + '\n';
			}

			for ( path in inputData['user_agents'][ userAgentName ]['path_rules'] ) {
				if ( 'allow' === inputData['user_agents'][ userAgentName ]['path_rules'][ path ] ) {
					output += 'Allow: ' + path + '\n';
				} else {
					output += 'Disallow: ' + path + '\n';
				}
			}
		}

		elementTextarea.innerHTML = output;
	}

	/**
	 * Merge Rules.
	 *
	 * @since 1.0.0
	 *
	 * @param {Object} rules1
	 * @param {Object} rules2
	 * @returns {Object}
	 */
	function mergeRules( rules1, rules2 ) {
		rules1 = JSON.parse(JSON.stringify(rules1));

		let newRules = {};

		newRules['sitemaps'] = arrayMerge( rules1['sitemaps'], rules2['sitemaps'] );

		newRules['user_agents'] = rules1['user_agents'];
		// newRules['user_agents'] = Object.assign( {}, rules1['user_agents'] );
		for ( let userAgentName in rules2['user_agents'] ) {
			if ( typeof newRules['user_agents'][ userAgentName ] === 'undefined' ) {
				newRules['user_agents'][ userAgentName ] = rules2['user_agents'][ userAgentName ];
			} else {
				if ( 0 !== parseInt( rules2['user_agents'][ userAgentName ]['crawl_delay'], 10 ) ) {
					newRules['user_agents'][ userAgentName ]['crawl_delay'] = rules2['user_agents'][ userAgentName ]['crawl_delay'];
				}
				newRules['user_agents'][ userAgentName ]['path_rules'] = Object.assign( {}, newRules['user_agents'][ userAgentName ]['path_rules'] );
				newRules['user_agents'][ userAgentName ]['path_rules'] = arrayMerge( newRules['user_agents'][ userAgentName ]['path_rules'], rules2['user_agents'][ userAgentName ]['path_rules'] );
			}
		}

		return newRules;
	}

	/* **________******************************************************************************************************/
	/* _/ COMMON \____________________________________________________________________________________________________*/

	// TODO Move/Create to `common-functions.js`.
	// Ignoring for now until code can be refactored to use a common JS file for functions.
	/* jshint ignore:start */
	/* eslint-disable no-unused-vars */
	/**
	 * Array Unique.
	 *
	 * @since 1.0.0
	 *
	 * @param {Array[]} arr
	 * @returns {Array[]}
	 */
	function arrayUnique( arr ) {
		arr = [... new Set(arr)];

		return arr;
	}
	/* jshint ignore:end */
	/* eslint-enable */

	/**
	 * Array Merge.
	 *
	 * @since 1.0.0
	 *
	 * @param {Array[]} arr1
	 * @param {Array[]} arr2
	 */
	function arrayMerge( arr1, arr2 ) {
		if ( Array.isArray( arr1 ) ) {
			if ( Array.isArray( arr2 ) ) {
				return [...arr1, ...arr2];
			}
		} else if ( typeof arr1 === 'object' ) {
			// let newArr = arr1;
			let newArr = Object.assign( {}, arr1 );
			let key;
			for ( key in arr2 ) {
				newArr[ key ] = arr2[ key ];
			}

			return newArr;
		}
	}

}
