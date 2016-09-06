/**
 * @file A WordPress-like hook system for JavaScript.
 *
 * This file demonstrates a simple hook system for JavaScript based on the hook
 * system in WordPress. The purpose of this is to make your code extensible and
 * allowing other developers to hook into your code with their own callbacks.
 *
 * There are other ways to do this, but this will feel right at home for
 * WordPress developers.
 *
 * @author Rheinard Korf
 * @license GPL2 (https://www.gnu.org/licenses/gpl-2.0.html)
 *
 */

/**
 * Hooks object
 *
 * This object needs to be declared early so that it can be used in code.
 * Preferably at a global scope.
 */
var Hooks = Hooks || {}; // Extend Hooks if exists or create new Hooks object.

Hooks.actions = Hooks.actions || {}; // Registered actions
Hooks.filters = Hooks.filters || {}; // Registered filters

/**
 * Add a new Action callback to Hooks.actions
 *
 * @param {string} tag The tag specified by do_action()
 * @param {{}} callback The callback function to call when do_action() is called
 * @param {number} priority The order in which to call the callbacks. Default: 10 (like WordPress)
 * @returns {void}
 */
Hooks.add_action = function( tag, callback, priority ) {
	'use strict';

	var _priority = priority;
	if ( 'undefined' === typeof priority ) {
		_priority = 10;
	}

	// If the tag doesn't exist, create it.
	Hooks.actions[tag] = Hooks.actions[tag] || [];
	Hooks.actions[tag].push( { priority: _priority, callback: callback } );
};

/**
 * Add a new Filter callback to Hooks.filters
 *
 * @param {string} tag The tag specified by apply_filters()
 * @param {{}} callback The callback function to call when apply_filters() is called
 * @param {number} priority Priority of filter to apply. Default: 10 (like WordPress)
 * @returns {void}
 */
Hooks.add_filter = function( tag, callback, priority ) {
	'use strict';

	var _priority = priority;
	if ( 'undefined' === typeof priority ) {
		_priority = 10;
	}

	// If the tag doesn't exist, create it.
	Hooks.filters[tag] = Hooks.filters[tag] || [];
	Hooks.filters[tag].push( { priority: _priority, callback: callback } );
};

/**
 * Remove an Action callback from Hooks.actions
 *
 * Must be the exact same callback signature.
 * Warning: Anonymous functions can not be removed.
 *
 * @param {string} tag The tag specified by do_action()
 * @param {{}} callback The callback function to remove
 * @returns {void}
 */
Hooks.remove_action = function( tag, callback ) {
	'use strict';
	var spliceSize = 1;

	Hooks.actions[tag] = Hooks.actions[tag] || [];

	Hooks.actions[tag].forEach( function( filter, i ) {
		if ( filter.callback === callback ) {

			Hooks.actions[tag].splice( i, spliceSize );
		}
	} );
};

/**
 * Remove a Filter callback from Hooks.filters
 *
 * Must be the exact same callback signature.
 * Warning: Anonymous functions can not be removed.
 *
 * @param {string} tag The tag specified by apply_filters()
 * @param {{}} callback The callback function to remove
 * @returns {void}
 */
Hooks.remove_filter = function( tag, callback ) {
	'use strict';
	var spliceSize = 1;

	Hooks.filters[tag] = Hooks.filters[tag] || [];

	Hooks.filters[tag].forEach( function( filter, i ) {
		if ( filter.callback === callback ) {
			Hooks.filters[tag].splice( i, spliceSize );
		}
	} );
};

/**
 * Calls actions that are stored in Hooks.actions for a specific tag or nothing
 * if there are no actions to call.
 *
 * @param {string} tag A registered tag in Hook.actions
 * @param {{}} options Optional JavaScript object to pass to the callbacks
 * @return {void}
 */
Hooks.do_action = function( tag, options ) {
	'use strict';
	var actions = [];

	/*eslint no-magic-numbers: ["error", { "ignore": [0] }]*/
	if ( 'undefined' !== typeof Hooks.actions[tag] && Hooks.actions[tag].length > 0 ) {

		Hooks.actions[tag].forEach( function( hook ) {

			actions[hook.priority] = actions[hook.priority] || [];
			actions[hook.priority].push( hook.callback );

		} );

		actions.forEach( function( hooks ) {

			hooks.forEach( function( callback ) {
				callback( options );
			} );

		} );
	}

};

/**
 * Calls filters that are stored in Hooks.filters for a specific tag or return
 * original value if no filters exist.
 *
 * @param {string} tag  A registered tag in Hook.filters
 * @param {*} value Value to pass to filter
 * @param {{}} options Optional JavaScript object to pass to the callbacks
 * @returns {*} Filtered value
 */
Hooks.apply_filters = function( tag, value, options ) {
	'use strict';

	var filters = [],
		_value = value;

	if ( 'undefined' !== typeof Hooks.filters[tag] && Hooks.filters[tag].length > 0 ) {

		Hooks.filters[tag].forEach( function( hook ) {
			filters[hook.priority] = filters[hook.priority] || [];
			filters[hook.priority].push( hook.callback );
		} );

		filters.forEach( function( hooks ) {
			hooks.forEach( function( callback ) {
				/* eslint-disable */
				_value = callback( _value, options );
				/* eslint-enable */
			} );
		} );
	}

	return _value;
};
