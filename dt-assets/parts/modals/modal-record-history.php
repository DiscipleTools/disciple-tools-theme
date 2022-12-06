<?php
global $post;
?>

<div class="large reveal" id="record_history_modal" data-reveal data-reset-on-close style="padding-bottom: 50px;">
    <h3><?php echo esc_html( sprintf( _x( '%s Record History', 'Record History', 'disciple_tools' ), $post->post_title ) ); ?></h3>
    <hr>

    <div class="grid-container">
        <div class="grid-x">
            <div class="cell small-4">
                <table style="border: none;  max-width: 300px;">
                    <tbody style="border: none;">
                    <tr>
                        <td colspan="2">
                            <input type="text" id="record_history_calendar"/>
                        </td>
                    </tr>
                    <tr style="border: none;">
                        <td style="vertical-align: top;">
                            <span><?php echo esc_html( _x( 'Show All', 'Show All', 'disciple_tools' ) ) ?></span>
                        </td>
                        <td>
                            <div class="switch tiny">
                                <input class="switch-input" id="record_history_all_activities_switch" type="checkbox"
                                       name="record_history_all_activities_switch" checked>
                                <label class="switch-paddle" for="record_history_all_activities_switch">
                        <span
                            class="show-for-sr"><?php echo esc_html( _x( 'Show All', 'Show All', 'disciple_tools' ) ) ?></span>
                                </label>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <div class="cell small-8">
                <div id="record_history_activities" style="display: none;"></div>
            </div>
        </div>
    </div>
    <br>
    <hr>

    <button class="close-button" data-close aria-label="Close modal" type="button">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
