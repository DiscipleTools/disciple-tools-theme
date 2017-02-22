/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );
	// Header text color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			if ( 'blank' === to ) {
				$( '.site-title, .site-description' ).css( {
					'clip': 'rect(1px, 1px, 1px, 1px)',
					'position': 'absolute'
				} );
			} else {
				$( '.site-title, .site-description' ).css( {
					'clip': 'auto',
					'color': to,
					'position': 'relative'
				} );
			}
		} );
	} );

	// google fonts
	wp.customize('drm_google_fonts_body_font', function(value) {
			value.bind(function(to) {
					var font = to.replace(' ', '+');
					WebFontConfig = {
							google: { families: [font + '::latin'] }
					};
					(function() {
							var wf = document.createElement('script');
							wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
									'://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
							wf.type = 'text/javascript';
							wf.async = 'true';
							var s = document.getElementsByTagName('script')[0];
							s.parentNode.insertBefore(wf, s);
					})();

					// style the text
					if (to == 'none') {
							$('body').attr('style', '');
					} else {
							var myVar = setInterval(function() {
									if (typeof WebFont != 'undefined') {
											WebFont.load({
													google: {
															families: [font]
													}
											});
											clearInterval(myVar);
									}
							}, 100);

							$('body, p, span, small, input, li, li a, .block_cont_in :not(h1,h2,h3,h4,h5,.fa,h1 a, h2 a, h3 a, h4 a, h5 a), .banner_left .text a, .profile_cont :not(h1,h2,h3,h4,h5), .herotext, .herobuttons .button').attr("style", 'font-family:"' + to + '" !important');
					}
			});
	});
	wp.customize('drm_google_fonts_heading_font', function(value) {
			value.bind(function(to) {
					var font = to.replace(' ', '+');
					WebFontConfig = {
							google: { families: [font + '::latin'] }
					};
					(function() {
							var wf = document.createElement('script');
							wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
									'://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
							wf.type = 'text/javascript';
							wf.async = 'true';
							var s = document.getElementsByTagName('script')[0];
							s.parentNode.insertBefore(wf, s);
					})();

					// style the text
					if (to == 'none') {
							$('h1,h2,h3,h4,h5,h6, h1 a, h2 a, h3 a, h4 a, h5 a, h6 a').attr("style", '');
					} else {
							var myVar = setInterval(function() {
									if (typeof WebFont != 'undefined') {
											WebFont.load({
													google: {
															families: [font]
													}
											});
											clearInterval(myVar);
									}
							}, 100);

							$('h1,h2,h3,h4,h5,h6, h1 a, h2 a, h3 a, h4 a, h5 a, h6 a').attr("style", 'font-family:"' + to + '" !important');
					}
			});
	});

} )( jQuery );
