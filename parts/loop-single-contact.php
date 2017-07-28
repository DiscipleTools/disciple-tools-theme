<article id="post-<?php the_ID(); ?>"  role="article" >


        <header class="article-header">
            <h1 class="entry-title single-title" itemprop="headline"><?php the_title(); ?></h1>
        </header> <!-- end article header -->

        <section class="entry-content" itemprop="articleBody">
            <?php
            $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
            $meta = get_post_meta( get_the_ID() );
            ?><h3>Phone Numbers</h3>
            <ul>
            <?php
            foreach($contact->fields[ "phone_numbers" ] as $field => $value){
                echo '<li>' . $value[0] . '</li>';
            }?>
            </ul>
            <h3>Email Addresses</h3>
            <ul>
            <?php
            foreach($contact->fields[ "emails" ] as $value){
                echo '<li>' . $value[0] . '</li>';
            }
            ?>
            </ul>
            <h3>Mailing Address</h3>
            <ul>
            <?php
            foreach($contact->fields[ "address" ] as $value){
                echo '<li>' . $value[0] . '</li>';
            }?>
            </ul>
        </section> <!-- end article section -->

        <footer class="article-footer">
        </footer> <!-- end article footer -->


</article> <!-- end article -->


