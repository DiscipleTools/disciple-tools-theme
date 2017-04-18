
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
        var author = 1;
        var author_email = 'chris@chasm.solutions';
        var author_ip = '192.168.2.1';
        var content = $( '#post-comment-content' ).val();
        var post = 65;
        var status = 'publish';



        var data = {
            // author: author,
            // author_email: author_email,
            // author_ip: author_ip,
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
