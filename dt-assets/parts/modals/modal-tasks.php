<?php
global $post;
?>

<div class="reveal" id="tasks-modal" data-reveal xmlns="http://www.w3.org/1999/html">

    <h3><?php echo esc_html__( 'Tasks', 'disciple_tools' )?></h3>


    <form class="js-add-task-form">
        <p style="color: red" class="error-text"></p>
        <p><?php echo esc_html__( 'Set a reminder or a task with a note and receive a notification on the due date.', 'disciple_tools' ); ?></p>
        <strong><?php echo esc_html__( "Task Type", "disciple_tools" ); ?></strong>
        <ul class="ul-no-bullets">
            <li>
                <label>
                    <input type="radio" name="task-type" value="reminder" checked><?php echo esc_html__( 'Reminder', 'disciple_tools' ); ?>
                </label>
            </li>
            <li>
                <label style="display: flex; align-items: baseline">
                    <input type="radio" name="task-type" value="custom"><?php echo esc_html__( 'Custom', 'disciple_tools' ); ?>:&nbsp;
                    <input type="text" id="task-custom-text" style="">
                </label>
            </li>
        </ul>

        <label><strong><?php echo esc_html__( "Due Date", "disciple_tools" ); ?></strong></label>
        <input id="create-task-date" name="task-date" type="text" class="" required autocomplete="off" >

        <button class="button loader" type="submit" id="create-task">
            <?php echo esc_html__( 'Create Task', 'disciple_tools' ); ?>
        </button>
    </form>

    <hr>
    <div>
        <h5><?php echo esc_html__( 'Existing tasks for this record:', 'disciple_tools' ); ?><span id="tasks-spinner" class="loading-spinner"></span></h5>
        <ul class="existing-tasks"></ul>
    </div>


    <div class="grid-x">
        <hr size="1px">
        <button class="button clear" data-close aria-label="Close reveal" type="button">
            <?php echo esc_html__( 'Close', 'disciple_tools' )?>
        </button>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>


    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>

</div>
