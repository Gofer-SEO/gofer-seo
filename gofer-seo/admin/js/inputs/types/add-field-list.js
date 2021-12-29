/**
 * Input Type - Add Item List
 *
 * @summary     Handle an input's 'conditions' OR 'item_conditions'.
 *
 * @package     Gofer SEO
 * @since       1.0.0
 */

window.addEventListener('load', function() {
	let goferSeoData = {
		inputNameTypesets: gofer_seo_l10n_add_field_list.input_name_typesets
	};

	for ( let inputName in goferSeoData.inputNameTypesets ) {
		goferSeoInputAddInputListHandler( inputName, goferSeoData.inputNameTypesets [ inputName ] );
	}
});

/**
 *
 * @param {string}  inputName
 * @param {Array[]} inputTypesets
 */
function goferSeoInputAddInputListHandler( inputName, inputTypesets ) {
	this.inputName     = inputName;
	this.inputTypesets = inputTypesets;

	addListeners( this.inputName, this.inputTypesets );
	setList( this.inputName, getData( this.inputName ) );

	/**
	 * Add Listeners.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} inputName
	 * @param {array}  inputTypesets
	 */
	function addListeners( inputName, inputTypesets ) {
		let buttonAddItem = document.getElementsByClassName( 'gofer-seo-input-name-' + inputName )[0].querySelector( '.gofer-seo-add-item-list-button' );
		buttonAddItem.addEventListener( 'click', function(event) { handleAddItemButton( event, inputName, inputTypesets ); }, false );
	}

	/**
	 * Handle Add Item Button.
	 *
	 * @since 1.0.0
	 *
	 * @param {Event}   event
	 * @param {string}  inputName
	 * @param {Array[]} inputTypesets
	 */
	function handleAddItemButton( event, inputName, inputTypesets ) {
		event.stopPropagation();
		event.preventDefault();

		let inputFieldData = getFieldsValue( inputName, inputTypesets );

		// TODO vvv ? Create function getFieldsDefaults().
		let defaultFieldsValue = [];
		let inputTypesetName;
		for ( inputTypesetName in inputTypesets ) {
			defaultFieldsValue[ inputTypesetName ] = '';
			if (
					typeof inputTypesets[ inputTypesetName ]['attrs'] !== 'undefined' &&
					typeof inputTypesets[ inputTypesetName ]['attrs']['value'] !== 'undefined'
			) {
				defaultFieldsValue[ inputTypesetName ] = inputTypesets[ inputTypesetName ]['attrs']['value'];
			}
		}
		// TODO ^^^ ? Create function getFieldsDefaults().

		// Set input fields back to default.
		setFieldsValue( inputName, inputTypesets, defaultFieldsValue );

		// Add data to Input.
		let inputData = getData( inputName );
		inputData[ inputData.length ] = inputFieldData;
		setData( inputName, inputTypesets, inputData );

		// Add Item to List.
		setList( inputName, inputData );
	}

	/**
	 * Handle Item Remote (Trash) Button.
	 *
	 * @since 1.0.0
	 *
	 * @param {Event}   event
	 * @param {string}  inputName
	 * @param {Array[]} inputTypesets
	 * @param {string} listIndex
	 */
	function handleItemRemoveButton( event, inputName, inputTypesets, listIndex ) {
		let inputData = getData( inputName );

		delete inputData[ listIndex ];

		// inputData.filter(function (item) { return item !== undefined }).join();
		inputData = inputData.filter(function (item) { return typeof item !== 'undefined'; });

		setData( inputName, inputTypesets, inputData  );

		setList( inputName, inputData );
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
	 * @param {string}  inputName
	 * @param {Array[]} inputTypesets
	 * @param {Array}   inputData
	 */
	function setData( inputName, inputTypesets, inputData ) {
		let validEntries = [];

		// Only allow fields set in typeset.
		// TODO Check performance.
		let inputFieldsDataIndex;
		let inputTypesetName;
		for ( inputFieldsDataIndex in inputData ) {
			let validEntry = [];
			for ( inputTypesetName in inputTypesets ) {
				if ( typeof inputData[ inputFieldsDataIndex ][ inputTypesetName ] !== 'undefined' ) {
					validEntry[ inputTypesetName ] = inputData[ inputFieldsDataIndex ][ inputTypesetName ];
				} else if (
						typeof inputTypesets[ inputTypesetName ]['attrs'] !== 'undefined' &&
						typeof inputTypesets[ inputTypesetName ]['attrs']['value'] !== 'undefined'
				) {
					validEntry[ inputTypesetName ] = inputTypesets[ inputTypesetName ]['attrs']['value'];
				} else {
					validEntry[ inputTypesetName ] = '';
				}
			}
			validEntries[ inputFieldsDataIndex ] = Object.assign( {}, validEntry );
		}

		document.getElementsByName( inputName )[0].value = JSON.stringify( validEntries );
	}

	/**
	 * Set Table List.
	 *
	 * @since 1.0.0
	 *
	 * @param {string}  inputName
	 * @param {Array[]} inputData
	 */
	function setList( inputName, inputData ) {
		let elementWrap  = document.getElementsByClassName( 'gofer-seo-input-name-' + inputName )[0];
		let elementTable = elementWrap.querySelector( 'table.gofer-seo-add-field-list-table' );
		elementTable.removeChild( elementTable.querySelector( 'tbody' ) );
		// elementTable.createTBody( inputData ); // TODO Test if array automatically inserts cells.
		let elementTableBody = elementTable.createTBody();

		if ( 0 < inputData.length ) {
			elementTable.style.display = 'table';

			// let dataIndex;
			for ( let dataIndex in inputData ) {
				let row = elementTableBody.insertRow();

				let trashButton          = document.createElement( 'a' );
				trashButton.className    = 'dashicons dashicons-trash';
				trashButton.style.cursor = 'pointer';
				trashButton.style.color  = '#e46363';
				trashButton.addEventListener( 'click', function(event) { handleItemRemoveButton( event, inputName, inputTypesets, dataIndex ); }, false );

				row.insertCell().appendChild( trashButton );

				let typeIndex;
				for ( typeIndex in inputData[ dataIndex ] ) {
					let cell = row.insertCell();
					cell.appendChild( document.createTextNode( inputData[ dataIndex ][ typeIndex ] ) );
				}
			}
		} else {
			elementTable.style.display = 'none';
		}
	}

}
