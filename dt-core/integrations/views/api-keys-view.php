<div class="wrap">
  <div id="poststuff">
    <h1>API Keys</h1>
    <p>Developers can use API keys to grant limited access to the Disciple Tools
      API from external websites and applications. To get an API key, fill in what what you want to call it below.
      We will generate Client Token and Client Id base on the name.
    </p>
    <?php /* This warning may be worded too strongly, we might want to review
    it after we've done a security review of the API. */ ?>
    <p><strong>Do not give access to anyone or anything</strong> you do not
    trust with all the data stored in this website.</p>


      <form action="" method="post">
        <?php wp_nonce_field( 'api-keys-view', 'api-key-view-field' ); ?>
        <h2>Token Generator</h2>
        <table class="widefat striped" style="margin-bottom:50px">
          <tr>
            <th>
              <label for="application">Name</label>
            </th>
            <td>
              <input type="text" id="application" name="application">
              <button type="submit" class="button">Generate Token</button>
            </td>
          </tr>
        </table>
      <h2>Existing Keys</h2>
      <table class="widefat striped">
        <thead>
        <tr>
          <th>Client ID</th>
          <th>Client Token</th>
            <th></th>
        </tr>
        </thead>
        <?php foreach ( $keys as $id => $key): ?>
          <tbody>
          <tr>
            <td>
            <?php echo esc_html( $key["client_id"] ); ?>
            </td>
            <td>
            <?php echo esc_html( $key["client_token"] ); ?>
            </td>
            <td>
              <button type="submit" class="button button-delete" name="delete" value="<?php echo esc_attr( $id ); ?>">Delete <?php echo esc_html( $id ); ?></button>
            </td>
          </tr>
          </tbody>
        <?php endforeach; ?>
      </table>
    </form>
  </div>
</div>
