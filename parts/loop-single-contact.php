<?php $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true ); ?>
<?php $contact_fields = Disciple_Tools_Contacts::get_channel_list(); ?>
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
                    foreach($contact->fields[ "phone_numbers" ] ?? [] as $field => $value){
                        echo '<li>' . esc_html( $value["number"] ) . '</li>';
                    }?>
                </ul>
                <strong>Email</strong>
                <ul>
                    <?php
                    foreach($contact->fields[ "emails" ] ?? [] as $value){
                        echo '<li>' . esc_html( $value["email"] ) . '</li>';
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
                <strong>Social Links</strong>
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
        <strong>Phone</strong><button onclick="addNumber(<?php echo get_the_ID()?>)"><i class="fi-plus"></i></button>

        <ul id="number-list">
            <?php
            foreach($contact->fields[ "phone_numbers" ] ?? [] as $field => $value){
                echo '<li><input id="' . esc_attr( $field ) . '" value="' . esc_attr( $value[0] ) . '" onchange="save_field('. esc_attr( get_the_ID() ) . ', \'' . esc_attr( $field ) . '\')"></li>';
            }?>
        </ul>
    </div>


</section> <!-- end article -->


