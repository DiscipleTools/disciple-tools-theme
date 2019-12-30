<?php
global $post;
?>

<div class="reveal" id="tasks-modal" data-reveal xmlns="http://www.w3.org/1999/html">

    <h3><?php esc_html_e( 'Tasks', 'disciple_tools' )?></h3>


    <form class="js-add-task-form">
        <div>
            <p style="color: red" class="error-text"></p>
            <h5><?php esc_html_e( "Task description", "disciple_tools" ); ?></h5>
            <ul class="ul-no-bullets">
                <li>
                    <label>
                        <input type="radio" name="task-type" value="reminder" checked><?php esc_html_e( 'Reminder', 'disciple_tools' ); ?>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="task-type" value="attempt"><?php esc_html_e( 'Attempt Contact', 'disciple_tools' ); ?>
                    </label>
                </li>
                <li>
                    <label style="display: flex; align-items: baseline">
                        <input type="radio" name="task-type" value="custom"><?php esc_html_e( 'Custom', 'disciple_tools' ); ?>:&nbsp;
                        <input type="text" id="task-custom-text" style="">
                    </label>
                </li>
            </ul>

            <label><?php esc_html_e( "Due Date", "disciple_tools" ); ?>
                <input id="create-task-date" name="task-date" type="text" class="" required autocomplete="off" style="width: 200px">
            </label>
            <button class="button loader" type="submit" id="create-task">
                <?php esc_html_e( 'Create Task', 'disciple_tools' ); ?>
            </button>
        </div>

        <hr>
        <div>
            <h5><?php esc_html_e( 'Existing tasks:', 'disciple_tools' ); ?></h5>
            <ul class="existing-tasks">
                <li><?php esc_html_e( 'No task created', 'disciple_tools' ); ?></li>
            </ul>
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
