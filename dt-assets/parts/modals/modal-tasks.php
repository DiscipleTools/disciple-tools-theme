<?php
global $post;
?>

<div class="reveal" id="tasks-modal" data-reveal xmlns="http://www.w3.org/1999/html">

    <p class="lead"><?php esc_html_e( 'Tasks', 'disciple_tools' )?></p>


    <form class="js-add-task-form">
        <div>
            <p style="color: red" class="error-text"></p>
            <label>
                <?php esc_html_e( "Title or description of task", "disciple_tools" ); ?>
                <textarea id="create-task-note" name="task-note" placeholder="<?php esc_html_e( "Call contact", "disciple_tools" ); ?>" required></textarea>
            </label>

            <label><?php esc_html_e( "Task Date", "disciple_tools" ); ?>
                <input id="create-task-date" name="task-date" type="text" class="" required autocomplete="off">
            </label>
            <button class="button loader" type="submit" id="create-task">
                <?php esc_html_e( 'Create Task', 'disciple_tools' ); ?>
            </button>
        </div>

        <div>
            <p><?php esc_html_e( 'Existing tasks:', 'disciple_tools' ); ?></p>
            <ul class="existing-tasks"></ul>
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
