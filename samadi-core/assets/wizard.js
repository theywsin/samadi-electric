/**
 * Samadi Core setup wizard: installs plugins, then runs the demo import step by step.
 */
( function () {
	'use strict';

	var cfg = window.samadiCore;
	if ( ! cfg ) {
		return;
	}

	var pluginList = document.getElementById( 'sc-plugins' );
	var stepList = document.getElementById( 'sc-steps' );
	var start = document.getElementById( 'sc-start' );
	var remove = document.getElementById( 'sc-remove' );
	var status = document.getElementById( 'sc-status' );
	var done = document.getElementById( 'sc-done' );

	function row( list, id, label, state, note ) {
		var li = document.getElementById( id );
		if ( ! li ) {
			li = document.createElement( 'li' );
			li.id = id;
			li.innerHTML = '<span class="sc-dot"></span><span class="sc-label"></span><span class="sc-note"></span>';
			list.appendChild( li );
		}
		li.className = 'is-' + state;
		li.querySelector( '.sc-label' ).textContent = label;
		li.querySelector( '.sc-note' ).textContent = note || '';
	}

	cfg.plugins.forEach( function ( p ) {
		row( pluginList, 'sc-p-' + p.slug, p.name, 'active' === p.status ? 'done' : 'todo', 'active' === p.status ? cfg.i18n.active : '' );
	} );
	cfg.steps.forEach( function ( s ) {
		row( stepList, 'sc-s-' + s.key, s.label, 'todo', '' );
	} );

	function post( data ) {
		var body = new FormData();
		body.append( 'nonce', cfg.nonce );
		Object.keys( data ).forEach( function ( k ) {
			body.append( k, data[ k ] );
		} );
		return fetch( cfg.ajax, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( r ) {
				return r.text();
			} )
			.then( function ( text ) {
				var json;
				try {
					// Some plugins print output before JSON; take the last JSON object.
					json = JSON.parse( text.slice( text.indexOf( '{"success"' ) ) );
				} catch ( e ) {
					throw new Error( text.replace( /<[^>]+>/g, ' ' ).trim().slice( 0, 300 ) || 'Invalid response' );
				}
				if ( ! json.success ) {
					throw new Error( ( json.data && json.data.message ) || cfg.i18n.failed );
				}
				return json.data;
			} );
	}

	function runPlugins( i ) {
		if ( i >= cfg.plugins.length ) {
			return Promise.resolve();
		}
		var p = cfg.plugins[ i ];
		if ( 'active' === p.status ) {
			return runPlugins( i + 1 );
		}
		row( pluginList, 'sc-p-' + p.slug, p.name, 'running', cfg.i18n.installing );
		return post( { action: 'samadi_core_plugin', slug: p.slug } ).then( function () {
			p.status = 'active';
			row( pluginList, 'sc-p-' + p.slug, p.name, 'done', cfg.i18n.active );
			return runPlugins( i + 1 );
		}, function ( err ) {
			row( pluginList, 'sc-p-' + p.slug, p.name, 'error', err.message );
			throw err;
		} );
	}

	function runSteps( i ) {
		if ( i >= cfg.steps.length ) {
			return Promise.resolve();
		}
		var s = cfg.steps[ i ];
		row( stepList, 'sc-s-' + s.key, s.label, 'running', cfg.i18n.running );
		return post( { action: 'samadi_core_step', step: s.key } ).then( function ( data ) {
			row( stepList, 'sc-s-' + s.key, s.label, 'done', data.message );
			return runSteps( i + 1 );
		}, function ( err ) {
			row( stepList, 'sc-s-' + s.key, s.label, 'error', err.message );
			throw err;
		} );
	}

	if ( start ) {
		start.addEventListener( 'click', function () {
			start.disabled = true;
			status.textContent = '';
			runPlugins( 0 )
				.then( function () {
					return runSteps( 0 );
				} )
				.then( function () {
					status.textContent = cfg.i18n.done;
					done.hidden = false;
					start.disabled = false;
				} )
				.catch( function ( err ) {
					status.textContent = cfg.i18n.failed + ': ' + err.message;
					start.disabled = false;
				} );
		} );
	}

	if ( remove ) {
		remove.addEventListener( 'click', function () {
			if ( ! window.confirm( cfg.i18n.confirm ) ) {
				return;
			}
			remove.disabled = true;
			post( { action: 'samadi_core_remove' } ).then( function ( data ) {
				status.textContent = cfg.i18n.removed + ' ' + data.message;
				window.setTimeout( function () {
					window.location.reload();
				}, 1200 );
			}, function ( err ) {
				status.textContent = err.message;
				remove.disabled = false;
			} );
		} );
	}
}() );
