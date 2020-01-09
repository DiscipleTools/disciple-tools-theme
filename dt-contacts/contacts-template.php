<?php
/**
 * Presenter template for theme support
 *
 * @package  Disciple_Tools
 * @category Plugin
 * @author   Chasm.Solutions & Kingdom.Training
 * @since    0.1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/** Functions to output data for the theme. @see Buddypress bp-members-template.php or bp-groups-template.php for an example of the role of this page  */



/**
 * Prints the name of the Group or User
 * Used in the loop to get a friendly name of the 'assigned_to' field of the contact
 *
 * If $return is true, then return the name instead of printing it. (Similar to
 * the $return argument in var_export.)
 *
 * @param  int  $contact_id
 * @param  bool $return
 * @return string
 */
function dt_get_assigned_name( int $contact_id, bool $return = false ) {

    $metadata = get_post_meta( $contact_id, $key = 'assigned_to', true );

    if ( !empty( $metadata )) {
        $meta_array = explode( '-', $metadata ); // Separate the type and id
        $type = $meta_array[0];
        $id = $meta_array[1];

        if ($type == 'user') {
            $value = get_user_by( 'id', $id );
            $rv = $value->display_name;
        } else {
            $value = get_term( $id );
            $rv = $value->name;
        }
        if ($return) {
            return $rv;
        } else {
            echo esc_html( $rv );
        }
    }

}

/**
 * @param $contact_id
 *
 * @return array|mixed
 */
function dt_get_users_shared_with( $contact_id ) {
    return Disciple_Tools_Contacts::get_shared_with_on_contact( $contact_id );
}



function render_field_for_display( $field_key, $fields, $post ){
    if ( isset( $fields[$field_key]["type"] ) ){
        $field_type = $fields[$field_key]["type"];
        ?>
        <div class="section-subheader">
            <?php echo esc_html( $fields[$field_key]["name"] )?>
        </div>
        <?php
        if ( $field_type === "key_select" ) : ?>
            <select class="select-field" id="<?php echo esc_html( $field_key ); ?>">
                <?php foreach ($fields[$field_key]["default"] as $option_key => $option_value):
                    $selected = isset( $post[$field_key]["key"] ) && $post[$field_key]["key"] === $option_key; ?>
                    <option value="<?php echo esc_html( $option_key )?>" <?php echo esc_html( $selected ? "selected" : "" )?>>
                        <?php echo esc_html( $option_value["label"] ) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php elseif ( $field_type === "multi_select" ) : ?>
            <div class="small button-group" style="display: inline-block">
                <?php foreach ( $fields[$field_key]["default"] as $option_key => $option_value ): ?>
                    <?php
                    $class = ( in_array( $option_key, $post[$field_key] ?? [] ) ) ?
                        "selected-select-button" : "empty-select-button"; ?>
                    <button id="<?php echo esc_html( $option_key ) ?>" data-field-key="<?php echo esc_html( $field_key ) ?>"
                            class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button ">
                        <?php echo esc_html( $fields[$field_key]["default"][$option_key]["label"] ) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php elseif ( $field_type === "text" ) :?>
            <input id="<?php echo esc_html( $field_key ) ?>" type="text"
                   class="text-input"
                   value="<?php echo esc_html( $post[$field_key] ?? "" ) ?>"/>
        <?php elseif ( $field_type === "date" ) :?>
            <input type="text" class="date-picker dt_date_picker"
                   id="<?php echo esc_html( $field_key ) ?>"
                   autocomplete="off"
                   value="<?php echo esc_html( isset( $post[$field_key] ) ? $post[$field_key]["formatted"] : '' )?>">
        <?php elseif ( $field_type === "connection" ) :?>
            <div id="<?php echo esc_attr( $field_key . '_connection' ) ?>" class="dt_typeahead">
                <var id="<?php echo esc_html( $field_key ) ?>-result-container" class="result-container"></var>
                <div id="<?php echo esc_html( $field_key ) ?>_t" name="form-<?php echo esc_html( $field_key ) ?>" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-<?php echo esc_html( $field_key ) ?>"
                                       name="<?php echo esc_html( $field_key ) ?>[query]" placeholder="<?php echo esc_html_x( "Search", 'input field placeholder', 'disciple_tools' ); echo esc_html( ' ' . $fields[$field_key]['name'] )?>  "
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ( $field_type === "location" ) :?>
            <div class="dt_location_grid">
                <var id="location_grid-result-container" class="result-container"></var>
                <div id="location_grid_t" name="form-location_grid" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-location_grid input-height"
                                       name="location_grid[query]" placeholder="<?php echo esc_html_x( "Search Locations", 'input field placeholder', 'disciple_tools' ) ?>"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif;
    }
}
