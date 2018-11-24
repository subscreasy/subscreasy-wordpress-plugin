( function( $ ) {
	$( document ).ready( function() {
		$( '#subscreasyOptions input' ).on( 'change', function() {
			$( '#testConnectivity' ).attr( 'disabled', 'disabled' );
			$( '#testData' ).html( subscreasyAdminParams.textUnsaved );
		} );

		$( '#testConnectivity' ).on( 'click', function( e ) {
			e.preventDefault();
			
			var data = {
				'action': 'subscreasy_test_connectivity',
			};

			$.ajax( {
				url: subscreasyAdminParams.ajaxURL,
				data: data,
				beforeSend: function() {
					$( '#subscreasy-overlay' ).show().css( 'display', 'table' );
				},
				success: function( response ) {
					$( '#subscreasy-overlay' ).hide();
					response = JSON.parse( response );

					if ( '401' == response.status && 'Unauthorized' == response.error ) {
						$( '#testData' ).html( subscreasyAdminParams.textUnauth );
						return;
					}

					var testData = response.map( function( i ) {
						return { id: i.id,  name: i.name, price: i.price };
					} );
					
					var testOutput = '<table class="wp-list-table widefat fixed striped posts"><thead><tr>' +
						'<th>' + subscreasyAdminParams.textId + '</th>' +
						'<th>' + subscreasyAdminParams.textName + '</th>' +
						'<th>'+ subscreasyAdminParams.textPrice + '</th>' +
						'<th>'+ subscreasyAdminParams.buttonShortCode + '</th></tr></thead>';
					testData.forEach( function( i ) {
						testOutput += '<tr>' +
							'<td>' + i.id + '</td>' +
							'<td>' + i.name + '</td>' +
							'<td>' + i.price + ' ' + subscreasyAdminParams.textCurr + '</td>' +
							"<td>[subscreasy_button title='Subscribe Now!' offer_id='" + i.id + "']</td>" +
							'</tr>';
					} );

					testData += '</table>';

					$( '#testData' ).html( testOutput );
				},
				error: function() {
					if ( 200 != xhr.status) {
						$( '#testData' ).html( subscreasyAdminParams.textErr );
					}
				}
			} );
		} );
	} );
} )( jQuery );