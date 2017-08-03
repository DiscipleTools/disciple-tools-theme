<?php $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true ); ?>
<section id="post-<?php the_ID(); ?>" >

    <div class="row item-details-header-row">
        <div class="medium-12 columns">
            <i class="fi-torso large"></i><span class="item-details-header"><?php the_title(); ?></span>
            <span class="button alert label">
              Status: <?php echo $contact->fields["overall_status"]["label"] ?>
            </span>
            <button class="tiny button">Pause</button>
            <button class="tiny button">Close</button>
        </div>
    </div>

    <div class="row">

        <div class="medium-4 columns">
            <strong>Phone</strong>
            <ul>
                <?php
                foreach($contact->fields[ "phone_numbers" ] ?? [] as $field => $value){
                    echo '<li>' . $value[0] . '</li>';
                }?>
            </ul>
            <strong>Email</strong>
            <ul>
                <?php
                foreach($contact->fields[ "emails" ] ?? [] as $value){
                    echo '<li>' . $value[0] . '</li>';
                }
                ?>
            </ul>
        </div>
        <div class="medium-4 columns">
            <strong>Locations</strong>
            <ul>
                <?php
                foreach($contact->fields[ "locations" ] ?? [] as $value){
                    echo '<li><a href="' . $value->permalink . '">'. $value->post_title .'</a></li>';
                }?>
            </ul>
            <strong>Address</strong>
            <ul>
                <?php
                foreach($contact->fields[ "address" ]  ?? [] as $value){
                    echo '<li>' . $value[0] . '</li>';
                }?>
            </ul>
        </div>
        <div class="medium-4 columns">
            <strong>Social Links</strong>
        </div>
    </div> <!-- end article section -->

</section> <!-- end article -->


