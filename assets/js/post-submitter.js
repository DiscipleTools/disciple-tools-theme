
jQuery( document ).ready( function ( $ ) {
    $( '#post-submission-form' ).on( 'submit', function(e) {
        e.preventDefault();
        var title = $( '#post-submission-title' ).val();
        var excerpt = $( '#post-submission-excerpt' ).val();
        var content = $( '#post-submission-content' ).val();
        var status = 'publish';

        var data = {
            title: title,
            excerpt: excerpt,
            content: content,
            status: status
        };

        $.ajax({
            method: "POST",
            url: POST_SUBMITTER.root + 'wp/v2/posts',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', POST_SUBMITTER.nonce );
            },
            success : function( response ) {
                console.log( response );
                alert( POST_SUBMITTER.success );
            },
            fail : function( response ) {
                console.log( response );
                alert( POST_SUBMITTER.failure );
            }

        });

    });

} );

jQuery( document ).ready( function ( $ ) {
    $( '#post-comment-form' ).on( 'submit', function(e) {
        e.preventDefault();
        var content = $( '#post-comment-content' ).val();
        var post = $( '#post-comment-id' ).val();
        var status = 'publish';

        var data = {
            content: content,
            post: post,
            status: status

        };

        $.ajax({
            method: "POST",
            url: COMMENT_SUBMITTER.root + 'wp/v2/comments',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', COMMENT_SUBMITTER.nonce );
            },
            success : function( response ) {
                console.log( response );
                alert( COMMENT_SUBMITTER.success );
            },
            fail : function( response ) {
                console.log( response );
                alert( COMMENT_SUBMITTER.failure );
            }

        });

    });

} );
