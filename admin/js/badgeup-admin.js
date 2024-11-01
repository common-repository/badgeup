jQuery( function( $ ) {
	'use strict';

	var
		validateKeyToI,
		$keyValidResp = $( '#badgeup_api_key_valid' );

	function validateKey( key ) {
		$keyValidResp
			.addClass( 'loading' )
			.html( '' );
		validateKeyToI = null;

		$.post(
			ajaxurl,
			{
				action: 'badgeup_verify_key',
				key: key,
			},
			function ( resp ) {
				$keyValidResp
					.removeClass( 'loading' )
					.html( resp );
			}
		);
	}

	$( '#badgeup_api_key' ).keyup( function() {
		var key = this.value;
		if ( validateKeyToI ) {
			clearTimeout( validateKeyToI );
		}
		validateKeyToI = setTimeout( function() {
			validateKey( key );
		}, 500 );
	} );

} );
