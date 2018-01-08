<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Sanitize image file name
 * https://wordpress.org/plugins/wp-hash-filename/
 *
 * This process is in an effort to harden the files uploaded to the uploads area, so they are not recognizable through the file name.
 *
 * @param $filename
 *
 * @return string
 */
function dt_make_filename_hash( $filename )
{
    $info = pathinfo( $filename );
    $ext = empty( $info['extension'] ) ? '' : '.' . $info['extension'];
    $name = basename( $filename, $ext );

    return md5( $name ) . $ext;
}
//add_filter( 'sanitize_file_name', 'dt_make_filename_hash', 10 ); //todo Hash process turned off. Determine if it is still necessary

/**
 * Add Categories to Attachments
 */
function dt_add_categories_to_attachments()
{
    register_taxonomy_for_object_type( 'category', 'attachment' );
}
add_action( 'init', 'dt_add_categories_to_attachments' );






