<?php $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true ); ?>
<?php $channel_list = Disciple_Tools_Contacts::get_channel_list(); ?>
<?php //var_dump($contact->fields) ?>
<section id="post-<?php the_ID(); ?>" >
    <span id="contact-id" style="display: none"><?php echo get_the_ID()?></span>

    <div class="row item-details-header-row">
            <i class="fi-torso large"></i><span class="item-details-header"><?php the_title_attribute(); ?></span>
            <span class="button alert label">
              Status: <?php echo esc_html( $contact->fields["overall_status"]["label"] ) ?>
            </span>
            <button class="tiny button">Pause</button>
            <button class="tiny button">Close</button>
            <button class="tiny button float-right" onclick="edit_fields()">Edit</button>
    </div>


    <div id="display-fields">
        <div class="row">

            <div class="medium-4 columns">
                <strong>Phone</strong>
                <i class="fa fa-plus"></i>
                <ul>
                    <?php
                    foreach($contact->fields[ "contact_phone" ] ?? [] as $field => $value){
                        echo '<li>' . esc_html( $value["value"] ) . '</li>';
                    }?>
                </ul>
                <strong>Email</strong>
                <ul>
                    <?php
                    foreach($contact->fields[ "contact_email" ] ?? [] as $value){
                        echo '<li>' . esc_html( $value["value"] ) . '</li>';
                    }
                    ?>
                </ul>
            </div>
            <div class="medium-4 columns">
                <strong>Assigned To</strong>
                <ul>
                    <li>
                    <?php
                    if ( isset( $contact->fields["assigned_to"] ) ){
                        echo esc_html( $contact->fields["assigned_to"]["display"] );
                    } else {
                        echo "None Assigned";
                    }
                    ?>
                    </li>
                </ul>
                <strong>Locations</strong>
                <ul>
                    <?php
                    foreach($contact->fields[ "locations" ] ?? [] as $value){
                        echo '<li><a href="' . esc_attr( $value->permalink ) . '">'. esc_html( $value->post_title ) .'</a></li>';
                    }?>
                </ul>

            </div>
            <div class="medium-4 columns">
<!--                <strong>Social Links</strong>-->
                <?php
                foreach($contact->fields as $field_key => $values){
                    if ( strpos( $field_key, "contact_" ) === 0 &&
                        isset( $channel_list[explode( '_', $field_key )[1]] ) &&
                        strpos( $field_key, "contact_phone" ) === false &&
                        strpos( $field_key, "contact_email" ) === false ){
                        if ( $values && sizeof( $values ) > 0 ){
                            echo "<strong>".$values[0]["type_label"]??$field_key."</strong>";
                        }
                        $html = "<ul>";
                        foreach( $values as $value ){
                            $html .= "<li>" . esc_html( $value["value"] ) . "</li>";

                        }
                        $html .= "</ul>";
                        echo $html;
//                        var_dump($field_key);
//                        var_dump($values);
                    }
                }
                ?>


            </div>
        </div>

        <div class="row">
            <div id="show-more-content" data-toggler
                 data-animate="fade-in fade-out" aria-expanded="false" style="display:none;">
                <div class="medium-4 columns">
                    <strong>Address</strong>
                    <ul>
                        <?php
                        foreach($contact->fields[ "address" ]  ?? [] as $value){
                            echo '<li>' . esc_html( $value[0] ) . '</li>';
                        }?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row show-more-button" style="text-align: center" >
            <button class="clear" data-toggle="show-more-button show-more-content show-content-button"  href="#">SHOW
                <span id="show-more-button" data-toggler data-animate="fade-in fade-out">MORE <i class="fi-plus"></i></span>
                <span id="show-content-button" data-toggler data-animate="fade-in fade-out" aria-expanded="false" style="display:none;">LESS <i class="fi-minus"></i></span>
            </button>
        </div>
    </div>
    <div id="edit-fields" style="display: none">

        <?php
        foreach( $contact->fields as $field_key => $values ){
            if ( strpos( $field_key, "contact_" ) === 0 ) {
                $type = explode( "_", $field_key )[1];
                if ( isset( $channel_list[$type] )){
                    $type_label = $channel_list[$type]["label"];
                    $new_input_id = "new-" . $type;
                    $list_id = $type . "-list";
                    ?>
                    <strong><?php echo $type_label?></strong>
                    <button onclick="add_contact_input(<?php echo get_the_ID() ?>, '<?php echo $new_input_id?>', '<?php echo $list_id?>' )">
                        <i class="fi-plus"></i>
                    </button>
                    <ul id="<?php echo $list_id?>">
                        <?php
                        foreach($contact->fields[ $field_key ] ?? [] as $value){
                            echo '<li>
                            <input id="' . esc_attr( $value["key"] ) . '" value="' . esc_attr( $value["value"] ) . '" onchange="save_field('. esc_attr( get_the_ID() ) . ', \'' . esc_attr( $value["key"] ) . '\')">
                            </li>';
                        }?>
                    </ul>

                    <?php
                }
            }
        }
        ?>
    </div>


</section> <!-- end article -->


