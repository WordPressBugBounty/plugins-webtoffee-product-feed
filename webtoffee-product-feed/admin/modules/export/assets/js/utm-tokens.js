/**
 * UTM token selector + full-URL live preview for the Advanced step of the feed wizard.
 *
 * Markup contract:
 *   - [+ token] button after utm_campaign / utm_content inputs, rendered by
 *     Wt_Pf_Utm_Tracking::add_utm_fields().
 *   - Single preview box at the bottom of the UTM section:
 *       <div id="wt_pf_utm_full_preview">
 *         <div class="wt_pf_utm_full_preview_note">...</div>
 *         <div class="wt_pf_utm_full_preview_url"></div>
 *       </div>
 *
 * Sample product values come from wt_pf_utm_preview_params (localized in export.php).
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var sample = ( typeof window.wt_pf_utm_preview_params !== 'undefined' )
			? window.wt_pf_utm_preview_params
			: {};

		function getFeedName() {
			var $feedInput = $( '#wt_pf_export_catalog_name' );
			if ( $feedInput.length && $feedInput.val() ) {
				return $feedInput.val();
			}
			return 'my-feed';
		}

		function getChannel() {
			// Scripts are enqueued on page load, before $this->to_export is populated,
			// so sample.channel is usually ''. The preview DIV carries the real slug via
			// data-wt-pf-utm-channel once the Advanced step is rendered.
			//
			// Use .attr() not .data(): jQuery's .data() auto-coerces numeric / 'true' /
			// 'false' / 'null' strings into their typed equivalents, so a future channel
			// slug like '0' or 'false' would collapse the truthy-check below and fall
			// back to sample.channel.
			var $preview = $( '#wt_pf_utm_full_preview' );
			if ( $preview.length ) {
				var channel = $preview.attr( 'data-wt-pf-utm-channel' ) || '';
				if ( '' !== channel ) {
					return channel;
				}
			}
			return sample.channel || '';
		}

		function getSampleMap() {
			return {
				'{feed_name}':  getFeedName(),
				'{channel}':    getChannel(),
				'{product_id}': sample.product_id || '',
				'{sku}':        sample.sku        || ''
			};
		}

		function substitute( value ) {
			if ( ! value ) {
				return '';
			}
			var map = getSampleMap();
			return value.replace( /\{feed_name\}|\{channel\}|\{product_id\}|\{sku\}/g, function ( match ) {
				return ( typeof map[ match ] !== 'undefined' ) ? map[ match ] : match;
			} );
		}

		// Mirror PHP's wp_strip_all_tags + trim so the preview matches the exported
		// feed when a user pastes HTML (e.g. a copy from Google Sheets).
		function stripTagsTrim( value ) {
			if ( ! value ) {
				return '';
			}
			return String( value ).replace( /<[^>]*>/g, '' ).replace( /^\s+|\s+$/g, '' );
		}

		function buildQuery( pairs ) {
			var parts = [];
			for ( var i = 0; i < pairs.length; i++ ) {
				var key = pairs[ i ][ 0 ];
				var val = pairs[ i ][ 1 ];
				if ( '' === val || null === val || typeof val === 'undefined' ) {
					continue;
				}
				parts.push( encodeURIComponent( key ) + '=' + encodeURIComponent( val ) );
			}
			return parts.join( '&' );
		}

		function renderFullPreview() {
			var $preview = $( '#wt_pf_utm_full_preview' );
			if ( ! $preview.length ) {
				return;
			}

			// Mirror the PHP export: empty input = no query param (no silent placeholder default).
			// stripTagsTrim mirrors wp_strip_all_tags + trim so pasted HTML renders the
			// same in preview as in the final feed.
			var source   = stripTagsTrim( $( '#wt_pf_utm_source' ).val()   || '' );
			var medium   = stripTagsTrim( $( '#wt_pf_utm_medium' ).val()   || '' );
			var campaign = stripTagsTrim( substitute( $( '#wt_pf_utm_campaign' ).val() || '' ) );
			var content  = stripTagsTrim( substitute( $( '#wt_pf_utm_content' ).val()  || '' ) );

			var query = buildQuery( [
				[ 'utm_source',   source ],
				[ 'utm_medium',   medium ],
				[ 'utm_campaign', campaign ],
				[ 'utm_content',  content ]
			] );

			var productUrl = sample.product_url || '';
			if ( '' === productUrl ) {
				$preview.find( '.wt_pf_utm_full_preview_url' ).text( '' );
				return;
			}

			var fullUrl = productUrl;
			if ( '' !== query ) {
				fullUrl += ( productUrl.indexOf( '?' ) === -1 ? '?' : '&' ) + query;
			}

			$preview.find( '.wt_pf_utm_full_preview_url' ).text( fullUrl );
		}

		// Prevent the token button / menu items from stealing focus on mousedown so that
		// the input's caret position (selectionStart/End) stays intact for the click handler.
		$( document ).on( 'mousedown', '.wt_pf_utm_token_button, .wt_pf_utm_token_menu li', function ( e ) {
			e.preventDefault();
		} );

		// Token menu toggle.
		$( document ).on( 'click', '.wt_pf_utm_token_button', function ( e ) {
			e.preventDefault();
			var targetId = $( this ).data( 'wt-pf-utm-target' );
			if ( ! targetId ) {
				return;
			}
			var $menu = $( '.wt_pf_utm_token_menu[data-wt-pf-utm-target="' + targetId + '"]' );
			$( '.wt_pf_utm_token_menu' ).not( $menu ).attr( 'hidden', 'hidden' );
			if ( $menu.is( '[hidden]' ) ) {
				$menu.removeAttr( 'hidden' );
			} else {
				$menu.attr( 'hidden', 'hidden' );
			}
		} );

		// Token insertion — inserts at the current caret position, or at the end if the
		// input never received focus (selectionStart is unreliable in that case).
		$( document ).on( 'click', '.wt_pf_utm_token_menu li', function ( e ) {
			e.preventDefault();
			var $li      = $( this );
			var $menu    = $li.closest( '.wt_pf_utm_token_menu' );
			var targetId = $menu.data( 'wt-pf-utm-target' );
			var token    = $li.data( 'wt-pf-utm-token' );
			var $input   = $( '#' + targetId );
			if ( $input.length && token ) {
				var input   = $input.get( 0 );
				var current = $input.val() || '';
				var start   = ( typeof input.selectionStart === 'number' ) ? input.selectionStart : current.length;
				var end     = ( typeof input.selectionEnd   === 'number' ) ? input.selectionEnd   : current.length;
				var next    = current.slice( 0, start ) + token + current.slice( end );
				$input.val( next ).trigger( 'input' );
				$input.focus();
				var caret = start + token.length;
				if ( typeof input.setSelectionRange === 'function' ) {
					input.setSelectionRange( caret, caret );
				}
			}
			$menu.attr( 'hidden', 'hidden' );
		} );

		// Close menus on outside click.
		$( document ).on( 'click', function ( e ) {
			if ( ! $( e.target ).closest( '.wt_pf_utm_token_wrap' ).length
				&& ! $( e.target ).closest( '.wt_pf_utm_token_button' ).length ) {
				$( '.wt_pf_utm_token_menu' ).attr( 'hidden', 'hidden' );
			}
		} );

		// Re-render preview whenever any UTM field or the feed name changes.
		$( document ).on(
			'input change',
			'#wt_pf_utm_source, #wt_pf_utm_medium, #wt_pf_utm_campaign, #wt_pf_utm_content, #wt_pf_export_catalog_name',
			function () {
				renderFullPreview();
			}
		);

		// The Advanced step is AJAX-loaded after document.ready fires, so the plugin's
		// wt_pf_form_toggler.Set() has already run against a DOM that didn't contain
		// our toggle. Re-run the toggler against the UTM subtree so the child fields
		// start in the right collapsed/expanded state and respond to the switch.
		function syncUtmToggler() {
			if ( 'undefined' !== typeof window.wt_pf_form_toggler
				&& 'function' === typeof window.wt_pf_form_toggler.toggle
				&& $( '#wt_pf_utm_enabled' ).length ) {
				window.wt_pf_form_toggler.toggle( $( '#wt_pf_utm_enabled' ), 1 );
			}
		}

		// Delegated click — the plugin's own change handler was bound directly at
		// document.ready and misses our AJAX-loaded checkbox.
		$( document ).on( 'click change', '#wt_pf_utm_enabled', function () {
			syncUtmToggler();
		} );

		// Initial render — works for the default channel landing on Advanced directly.
		renderFullPreview();
		syncUtmToggler();

		// Re-render + re-sync toggler when the Advanced step becomes visible. In edit
		// mode the step is AJAX-loaded after this script fires, and the initial calls
		// above return early because the preview DOM did not exist yet.
		var advancedStep = document.querySelector( '.wt_pf_export_step_advanced' );
		if ( advancedStep && 'undefined' !== typeof MutationObserver ) {
			var observer = new MutationObserver( function () {
				if ( $( advancedStep ).is( ':visible' ) ) {
					renderFullPreview();
					syncUtmToggler();
				}
			} );
			observer.observe( advancedStep, { attributes: true, attributeFilter: [ 'style', 'class' ], childList: true, subtree: false } );
		}
	} );

} )( jQuery );
