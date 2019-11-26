<?php
global $post;
?>

<div class="reveal" id="reminders-modal" data-reveal xmlns="http://www.w3.org/1999/html">

    <p class="lead"><?php esc_html_e( 'Reminders', 'disciple_tools' )?></p>


    <form class="js-add-reminder-form">
        <div>
            <p style="color: red" class="error-text"></p>
            <label>
                <?php esc_html_e( "Title or description of reminder", "disciple_tools" ); ?>
                <textarea id="create-reminder-note" name="reminder-note" placeholder="<?php esc_html_e( "Call contact", "disciple_tools" ); ?>" required></textarea>
            </label>

            <label><?php esc_html_e( "Reminder Date", "disciple_tools" ); ?>
                <input id="create-reminder-date" name="reminder-date" type="text" class="" required>
            </label>
            <button class="button loader" type="submit" id="create-reminder">
                <?php esc_html_e( 'Create Reminder', 'disciple_tools' ); ?>
            </button>
        </div>

        <div>
            <p><?php esc_html_e( 'Existing reminders:', 'disciple_tools' ); ?></p>
            <ul class="existing-reminders"></ul>
        </div>


        <div class="grid-x">
            <hr size="1px">
            <button class="button clear" data-close aria-label="Close reveal" type="button">
                <?php esc_html_e( 'Close', 'disciple_tools' )?>
            </button>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

    </form>
    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>

</div>
