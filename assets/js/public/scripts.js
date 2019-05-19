( function( $ ) {
	$( document ).ready( function() {
		$( '.subscreasy-button' ).on( 'click', function( e ) {
			e.preventDefault();
			var offerID = $( this ).data( 'offer-id' );
			var url = subscreasyParams.apiURL + "&offerID=" + offerID;
            if( subscreasyParams.isLoggedIn ) {
				$.redirect(
					url,
					{
						'offer.id': offerID,
						'subscriber.name': subscreasyParams.name,
						'subscriber.surname': subscreasyParams.surname,
						'subscriber.email': subscreasyParams.email,
						'subscriber.phoneNumber': subscreasyParams.phoneNumber,
					},
					'POST',
				);
			} else {
                console.log("location before login: " + window.location);
				Cookies.set( 'subscreasy_offer', offerID, { expires: 365 } );
				window.location = subscreasyParams.loginURL;
			}

            // If cookie set.
            // if( undefined !== Cookies.get( 'subscreasy_offer' ) ) {
            //     var offerID = Cookies.get( 'subscreasy_offer' );
            //     Cookies.remove( 'subscreasy_offer' );
			//
            //     $.redirect(
            //         subscreasyParams.apiURL,
            //         {
            //             'offer.id': offerID,
            //             'subscriber.name': subscreasyParams.name,
            //             'subscriber.surname': subscreasyParams.surname,
            //             'subscriber.email': subscreasyParams.email,
            //             'subscriber.phoneNumber': subscreasyParams.phoneNumber,
            //         },
            //         'POST',
            //     );
            // }
		});

	} );
} )( jQuery );

$ = jQuery.noConflict(true);