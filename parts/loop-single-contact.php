<section id="post-<?php the_ID(); ?>" >


  <header class="article-header">
    <h2><?php the_title(); ?></h2>
  </header> <!-- end article header -->

  <section class="row" itemprop="articleBody" >
	  <?php
	  $contact = Disciple_Tools_Contacts::get_contact( get_the_ID(), true );
	  ?>
    <div class="medium-4 columns">
      <strong>Phone</strong>
      <ul>
      <?php
      foreach($contact->fields[ "phone_numbers" ] as $field => $value){
        echo '<li>' . $value[0] . '</li>';
      }?>
      </ul>
      <strong>Email</strong>
      <ul>
      <?php
      foreach($contact->fields[ "emails" ] as $value){
        echo '<li>' . $value[0] . '</li>';
      }
      ?>
      </ul>
    </div>
    <div class="medium-4 columns">
      <strong>Locations</strong>
      <ul>
	    <?php
	    foreach($contact->fields[ "locations" ] as $value){
		    echo '<li><a href="' . $value->permalink . '">'. $value->post_title .'</a></li>';
	    }?>
      </ul>
      <strong>Address</strong>
      <ul>
      <?php
      foreach($contact->fields[ "address" ] as $value){
        echo '<li>' . $value[0] . '</li>';
      }?>
      </ul>
    </div>
    <div class="medium-4 columns">
      <strong>Social Links</strong>
    </div>
  </section> <!-- end article section -->

  <footer class="article-footer">

  </footer> <!-- end article footer -->


</section> <!-- end article -->


