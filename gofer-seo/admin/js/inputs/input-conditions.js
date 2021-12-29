/**
 * Input Conditional Show
 *
 * TODO Remove remaining jQuery.
 *
 * @summary     Handle an input's 'conditions' OR 'item_conditions'.
 *
 * @package     Gofer SEO
 * @since       1.0.0
 * @requires    jQuery
 */

window.addEventListener( 'load', function() {
	let goferSeoData = {
		inputConditions: gofer_seo_l10n_data.input_conditions
	};

	for ( let inputName in goferSeoData.inputConditions) {
		// TODO Add Debugger log.
		// console.log(inputName, goferSeoData.inputConditions[inputName]);

		new goferSeoInputConditionsHandler(inputName, goferSeoData.inputConditions[inputName]);
	}

	/*
	Object.keys(goferSeoData.inputConditions).forEach(inputName => {
		//console.log(inputName, goferSeoData.inputConditions[inputName]);

		let goferShowHandler = new goferSeoInputConditionsHandler(inputName, goferSeoData.inputConditions[inputName]);
	});
	 */
});

/**
 * (Gofer SEO) Input Conditions Handler.
 *
 * @since 1.0.0
 *
 * @param inputName
 * @param inputConditions
 */
function goferSeoInputConditionsHandler(inputName, inputConditions) {
	this.inputName     = inputName;
	this.conditions    = inputConditions;

	addListeners( this.inputName, this.conditions );
	handleConditionalInput( this.inputName, this.conditions );

	/**
	 * Add Listens to Inputs.
	 *
	 * @since 1.0.0
	 *
	 * @listens change
	 * @listens keyup
	 *
	 * @param {string} inputName                   The element/input name to apply an action to.
	 * @param {object} conditions                  The conditional input's conditions to meet.
	 * @param {string} conditions.action           The action to take. Accepts 'enable', 'disable', 'hide', and 'show'.
	 * @param {string} conditions.relations        Whether to match one or all. Accepts 'AND', and 'OR'.
	 * @param {object} conditions.{inputCondition} The object values to compare an input.
	 */
	function addListeners( inputName, conditions ) {
		for ( let conditionInputName in conditions ) {
			if ( 'relation' === conditionInputName || 'action' === conditionInputName ) {
				continue;
			}
			if ( conditionInputName.match(/\[\]/) ) {
				let conditionalEle = document.getElementsByName( conditionInputName );
				if ( 0 >= conditionalEle.length ) {
					// TODO Console Error when error_log set to true.
					// console.log( 'No elements by Name: ' + conditionInputName );
				} else {
					for ( let i = 0; i < conditionalEle.length; i++ ) {
						conditionalEle[ i ].addEventListener( 'change', function(e) { handleConditionalInput( inputName, conditions ); }.bind( this ), false );
						conditionalEle[ i ].addEventListener( 'keyup', function(e) { handleConditionalInput( inputName, conditions ); }.bind( this ), false );
					}
				}
			} else {
				let conditionalEle = document.getElementsByName( conditionInputName );
				if ( 0 >= conditionalEle.length ) {
					// TODO Console Error when error_log set to true.
					// console.log( 'No elements by Name: ' + conditionInputName );
				} else if ( 'radio' === conditionalEle[0].type ) {
					for ( var i = 0; i < conditionalEle.length; i++ ) {
						conditionalEle[ i ].addEventListener( 'change', function() { handleConditionalInput( inputName, conditions ); }.bind( this ), false );
						conditionalEle[ i ].addEventListener( 'keyup', function() { handleConditionalInput( inputName, conditions ); }.bind( this ), false );
					}
				} else {
					conditionalEle[0].addEventListener( 'change', function() { handleConditionalInput( inputName, conditions ); }.bind( this ), false );
					conditionalEle[0].addEventListener( 'keyup', function() { handleConditionalInput( inputName, conditions ); }.bind( this ), false );
				}
			}
		}
	}

	/**
	 *
	 * Handles Conditional Inputs.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} inputName                   The element/input name to apply an action to.
	 * @param {object} conditions                  The conditional input's conditions to meet.
	 * @param {string} conditions.action           The action to take. Accepts 'enable', 'disable', 'hide', and 'show'.
	 * @param {string} conditions.relations        Whether to match one or all. Accepts 'AND', and 'OR'.
	 * @param {object} conditions.{inputCondition} The object values to compare an input.
	 */
	function handleConditionalInput( inputName, conditions ) {
		if ( checkConditions( conditions ) ) {
			switch ( conditions.action ) {
				case 'readonly':
					document.getElementsByName( inputName )[0].setAttribute( 'readonly', 'readonly' );
					break;
				case 'disable':
					document.getElementsByName( inputName )[0].setAttribute( 'disabled', '' );
					break;
				case 'enable':
					document.getElementsByName( inputName )[0].removeAttribute( 'disabled' );
					break;
				case 'hide':
					document.getElementsByClassName( 'gofer-seo-input-condition-' + inputName )[0].style.display = 'none';
					break;
				case 'show': // jshint ignore:line
				default: {
					let eleInput = document.getElementsByClassName( 'gofer-seo-input-condition-' + inputName )[0];
					if ( 'TD' === eleInput.nodeName || 'TH' === eleInput.nodeName ) {
						eleInput.style.display = 'table-cell';
					} else {
						eleInput.style.display = 'block';
					}
				}
			}
		} else {
			switch ( conditions.action ) {
				case 'readonly':
					document.getElementsByName( inputName )[0].removeAttribute( 'readonly' );
					break;
				case 'disable':
					document.getElementsByName( inputName )[0].removeAttribute( 'disabled' );
					break;
				case 'enable':
					document.getElementsByName( inputName )[0].setAttribute( 'disabled', '' );
					break;
				case 'hide': {
					let eleInput = document.getElementsByClassName( 'gofer-seo-input-condition-' + inputName )[0];
					if ( 'TD' === eleInput.nodeName || 'TH' === eleInput.nodeName ) {
						eleInput.style.display = 'table-cell';
					} else {
						eleInput.style.display = 'block';
					}
					break;
				}
				case 'show': // jshint ignore:line
				default:
					document.getElementsByClassName( 'gofer-seo-input-condition-' + inputName )[0].style.display = 'none';
			}
		}
	}

	/**
	 * Check Conditions of Inputs.
	 *
	 * @since 1.0.0
	 *
	 * @param {object} conditions                  The conditional input's conditions to meet.
	 * @param {string} conditions.action           The action to take. Accepts 'enable', 'disable', 'hide', and 'show'.
	 * @param {string} conditions.relations        Whether to match one or all. Accepts 'AND', and 'OR'.
	 * @param {object} conditions.{inputCondition} The object values to compare an input.
	 */
	function checkConditions( conditions ) {
		let showInput;
		let relation      = conditions.relation;

		let conditionsObj = Object.assign( {}, conditions );
		delete conditionsObj.relation;
		delete conditionsObj.action;

		if ( 'OR' === relation ) {
			// foreach if ... true.
			showInput = false;
			let conditionInputName;
			for ( conditionInputName in conditionsObj ) {
				if ( checkCondition( conditionsObj[ conditionInputName ] ) ) {
					showInput = true;
					break;
				}
			}
		} else {
			// foreach if ... false.
			showInput = true;
			let conditionInputName;
			for ( conditionInputName in conditionsObj ) {
				if ( ! checkCondition( conditionsObj[ conditionInputName ] ) ) {
					showInput = false;
					break;
				}
			}
		}

		return showInput;
	}

	/**
	 * Check Show Condition.
	 *
	 * @since 1.0.0
	 *
	 * @param {object} condition The condition (object) to check an input as.
	 */
	function checkCondition( condition ) {
		let leftValue = getInputValue( condition.left_var );
		let operator  = condition.operator;

		if ( typeof condition.right_var !== 'undefined' ) {
			condition.right_value = getInputValue( condition.right_var );
		}
		let resultValue = condition.right_value;

		if ( Array.isArray( leftValue ) ) {
			let leftIndex;
			for ( leftIndex in leftValue ) {
				if ( Array.isArray( resultValue ) ) {
					let resultIndex;
					for ( resultIndex in resultValue ) {
						if ( compareValues( leftValue[ leftIndex ], operator, resultValue[ resultIndex ] ) ) {
							return true;
						}
					}
				} else {
					if ( compareValues( leftValue[ leftIndex ], operator, resultValue ) ) {
						return true;
					}
				}
			}
		} else {
			if ( Array.isArray( resultValue ) ) {
				let resultIndex;
				for ( resultIndex in resultValue ) {
					if ( compareValues( leftValue, operator, resultValue[ resultIndex ] ) ) {
						return true;
					}
				}
			} else {
				if ( compareValues( leftValue, operator, resultValue ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Compare Values.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} leftValue  Left value
	 * @param {string} operator   Comparative operator to use.
	 * @param {string} rightValue Right value (aka resultValue).
	 * @return {boolean}
	 */
	function compareValues( leftValue, operator, rightValue ) {
		switch ( operator ) {
			case 'TRUE':
				return ( leftValue );
			case 'FALSE':
				return ( ! leftValue );
			case 'AND':
				return ( leftValue && rightValue );
			case 'OR' :
				return ( leftValue || rightValue );
			case '==' :
				// jshint ignore:start
				/* eslint-disable eqeqeq */
				return ( leftValue == rightValue );
				// jshint ignore:end
				/* eslint-enable eqeqeq */
			case '===' :
				return ( leftValue === rightValue );
			case '!=' :
				// jshint ignore:start
				/* eslint-disable eqeqeq */
				return ( leftValue != rightValue );
				// jshint ignore:end
				/* eslint-enable eqeqeq */
			case '!==' :
				return ( leftValue !== rightValue );
			case '<' :
				return ( leftValue < rightValue );
			case '>' :
				return ( leftValue > rightValue );
			case '<=' :
				return ( leftValue <= rightValue );
			case '>=' :
				return ( leftValue >= rightValue );
			// TODO Add additional operations here.
			case 'regex':
			case 'match': {
				let regexTest = new RegExp( rightValue );
				return leftValue.match( regexTest );
			}
			case 'inArray':
			case 'checked':
			case 'selected': // jshint ignore:line
			default:
				return false;
		}
	}

	/**
	 * Get value(s) from Input (Name).
	 *
	 * TODO Possibly remove jQuery operations.
	 *
	 * @since 1.0.0
	 *
	 * @param {string} inputName The HTML name attribute.
	 * @returns {string|array|null}
	 */
	function getInputValue( inputName ) {
		let inputValue;
		let inputNameJQ = inputName.replace( /\[\]/, '\\[\\]' );
		let inputEle    = jQuery( '[name=' + inputNameJQ + ']' );

		if ( 0 === inputEle.length ) {
			return null;
		}

		let inputType = inputEle.attr('type');
		if ( inputType === 'checkbox' ) {
			if ( inputNameJQ.match(/\\\[\\\]/) ) {
				inputValue = [];
				jQuery( 'input[name=' + inputNameJQ + ']:checked' ).each( function () {
					let checkboxInputValue = jQuery( this ).val();
					if ( 'on' === checkboxInputValue || 'true' === checkboxInputValue ) {
						inputValue.push( true );
					} else {
						inputValue.push( checkboxInputValue );
					}
				});
			} else {
				inputValue = false;
				if ( 0 < jQuery( 'input[name=' + inputNameJQ + ']:checked' ).length ) {
					inputValue = true;
				}
			}
		} else if ( inputType === 'radio' ) {
			inputValue = jQuery( 'input[name=' + inputNameJQ + ']:checked' ).val();
		} else {
			inputValue = inputEle.val();
		}

		return inputValue;
	}
}
