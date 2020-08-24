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


/**
 * Accepts types: key_select, multi_select, text, date, connection, location
 *
 * @param $field_key
 * @param $fields
 * @param $post
 */
function render_field_for_display( $field_key, $fields, $post ){
    if ( isset( $fields[$field_key]["type"] ) ){
        $field_type = $fields[$field_key]["type"];
        $allowed_types = [ 'key_select', 'multi_select', 'date', 'text', 'number', 'connection', 'location', 'communication_channel' ];
        if ( !in_array( $field_type, $allowed_types ) ){
            return;
        }
        ?>
        <div class="section-subheader">
            <?php if ( isset( $fields[$field_key]["icon"] ) ) : ?>
                <img src="<?php echo esc_url( $fields[$field_key]["icon"] ) ?>">
            <?php endif;
            echo esc_html( $fields[$field_key]["name"] );
            if ( $field_type === "communication_channel" ) : ?>
                 <button data-list-class="<?php echo esc_html( $field_key ) ?>" class="add-button" type="button">
                    <img src="<?php echo esc_html( get_template_directory_uri() . '/dt-assets/images/small-add.svg' ) ?>"/>
                </button>
            <?php endif ?>
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
                    <button id="<?php echo esc_html( $option_key ) ?>" type="button" data-field-key="<?php echo esc_html( $field_key ) ?>"
                            class="dt_multi_select <?php echo esc_html( $class ) ?> select-button button ">
                        <?php echo esc_html( $fields[$field_key]["default"][$option_key]["label"] ) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php elseif ( $field_type === "text" ) :?>
            <input id="<?php echo esc_html( $field_key ) ?>" type="text"
                   class="text-input"
                   value="<?php echo esc_html( $post[$field_key] ?? "" ) ?>"/>
        <?php elseif ( $field_type === "number" ) :?>
            <input id="<?php echo esc_html( $field_key ) ?>" type="number"
                   class="text-input"
                   value="<?php echo esc_html( $post[$field_key] ?? "" ) ?>"/>
        <?php elseif ( $field_type === "date" ) :?>
            <div class="<?php echo esc_html( $field_key ) ?> input-group">
                <input id="<?php echo esc_html( $field_key ) ?>" class="input-group-field dt_date_picker" type="text" autocomplete="off"
                        value="<?php echo esc_html( $post[$field_key]["timestamp"] ?? '' ) ?>" >
                <div class="input-group-button">
                    <button id="<?php echo esc_html( $field_key ) ?>-clear-button" class="button alert clear-date-button" data-inputid="<?php echo esc_html( $field_key ) ?>" title="Delete Date" type="button">x</button>
                </div>
            </div>
        <?php elseif ( $field_type === "connection" ) :?>
            <div id="<?php echo esc_attr( $field_key . '_connection' ) ?>" class="dt_typeahead">
                <var id="<?php echo esc_html( $field_key ) ?>-result-container" class="result-container"></var>
                <div id="<?php echo esc_html( $field_key ) ?>_t" name="form-<?php echo esc_html( $field_key ) ?>" class="scrollable-typeahead typeahead-margin-when-active">
                    <div class="typeahead__container">
                        <div class="typeahead__field">
                            <span class="typeahead__query">
                                <input class="js-typeahead-<?php echo esc_html( $field_key ) ?>" data-field="<?php echo esc_html( $field_key ) ?>"
                                       data-post_type="<?php echo esc_html( $fields[$field_key]["post_type"] ) ?>"
                                       data-field_type="connection"
                                       name="<?php echo esc_html( $field_key ) ?>[query]"
                                       placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
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
                                       data-field="<?php echo esc_html( $field_key ) ?>"
                                       data-field_type="location"
                                       name="location_grid[query]"
                                       placeholder="<?php echo esc_html( sprintf( _x( "Search %s", "Search 'something'", 'disciple_tools' ), $fields[$field_key]['name'] ) )?>"
                                       autocomplete="off">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif ( $field_type === "communication_channel" ) : ?>
            <ul id="edit-<?php echo esc_html( $field_key ) ?>" >
                <?php foreach ( $post[$field_key] ?? [] as $field_value ) : ?>
                    <input id="<?php echo esc_html( $field_value["key"] ) ?>"
                           type="text"
                           data-type="<?php echo esc_html( $field_key ) ?>"
                           value="<?php echo esc_html( $field_value["value"] ) ?>"
                           class="dt-communication-channel">
                <?php endforeach;
                if ( empty( $post[$field_key] ) ?? [] ): ?>
                    <input data-type="<?php echo esc_html( $field_key ) ?>"
                           type="text"
                           class="dt-communication-channel">
                <?php endif ?>
            </ul>

        <?php endif;
    }
}
