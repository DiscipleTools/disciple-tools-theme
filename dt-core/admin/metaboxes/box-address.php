<?php
/**
 * Disciple_Tools_Metabox_Address
 *
 * @class   Disciple_Tools_Metabox_Address
 * @version 0.1.0
 * @since   0.1.0
 * @package Disciple_Tools
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * @return \Disciple_Tools_Metabox_Address
 */
function dt_address_metabox()
{
    $object = new Disciple_Tools_Metabox_Address();

    return $object;
}

/**
 * Class Disciple_Tools_Metabox_Address
 */
class Disciple_Tools_Metabox_Address
{

    /**
     * Constructor function.
     *
     * @access public
     * @since  0.1.0
     */
    public function __construct()
    {
    } // End __construct()

    /**
     * Add Address fields html for adding a new contact channel
     *
     * @usage Added to the bottom of the Contact Details Metabox.
     */
    public function add_new_address_field()
    {
        global $post;

        echo '<p><a href="javascript:void(0);" onclick="jQuery(\'#new-address\').toggle();"><strong>+ Address Detail</strong></a></p>';
        echo '<table class="form-table" id="new-address" style="display: none;"><tbody>' . "\n";

        $address_types = $this->get_address_type_list( $post->post_type );

        echo '<tr><th>
                <select name="new-key-address" class="edit-input"><option value=""></option> ';
        foreach ( $address_types as $type => $value ) {

            $key = "address_" . $type;

            echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value["label"] ) . '</option>';
        }
        echo '</select></th>';
        echo '<td>
                <input type="text" name="new-value-address" id="new-address" class="edit-input" placeholder="i.e. 888 West Street, Los Angelos CO 90210" />
            </td>
            <td>
                <button type="submit" class="button">Save</button>
            </td>
            </tr>';

        echo '</tbody></table>';
    }

    /**
     * Helper function to create the unique metakey for contacts channels.
     *
     * @param  $channel
     *
     * @return string
     */
    public function create_channel_metakey( $channel )
    {
        return $channel . '_' . $this->unique_hash(); // build key
    }

    /**
     * Creates 3 digit random hash
     *
     * @return string
     */
    public function unique_hash()
    {
        return substr( md5( rand( 10000, 100000 ) ), 0, 3 ); // create a unique 3 digit key
    }

    /**
     * Selectable values for different channels of contact information.
     *
     * @return array
     */
    public function get_address_type_list( $post_type )
    {

        switch ( $post_type ) {
            case 'contacts':
                $addresses = [
                    "home"  => [ "label" => __( 'Home', 'disciple_tools' ) ],
                    "work"  => [ "label" => __( 'Work', 'disciple_tools' ) ],
                    "other" => [ "label" => __( 'Other', 'disciple_tools' ) ],
                ];

                return $addresses;
                break;
            case 'groups':
                $addresses = [
                    "main"      => [ "label" => __( 'Main', 'disciple_tools' ) ],
                    "alternate" => [ "label" => __( 'Alternate', 'disciple_tools' ) ],
                ];

                return $addresses;
                break;
            case 'locations':
                $addresses = [
                    "main" => [ "label" => __( 'Main', 'disciple_tools' ) ],
                ];

                return $addresses;
                break;
            default:
                break;
        }
    }

    /**
     * Field: Contact Fields
     *
     * @return array
     */
    public function address_fields( $post_id )
    {
        global $wpdb, $post;

        $fields = [];
        $current_fields = [];

        $id = $post->ID ?? $post_id;
        if ( isset( $id ) ) {
            $current_fields = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT
                        meta_key
                    FROM
                        `$wpdb->postmeta`
                    WHERE
                        post_id = %d
                        AND meta_key LIKE 'address_%'
                    ORDER BY
                        meta_key DESC",
                    $id
                ),
                ARRAY_A
            );
        }

        foreach ( $current_fields as $value ) {
            if ( strpos( $value["meta_key"], "_details" ) == false ) {
                $details = get_post_meta( $id, $value['meta_key'] . "_details", true );
                $name = "";
                if ( $details && isset( $details["type"] ) ) {
                    $name = $details["type"];
                }
                $fields[ $value['meta_key'] ] = [
                    'name' => $name,
                ];
            }
        }

        return $fields;
    }

}
