<?php
global $post;
?>

<div class="large reveal" id="record_history_modal" data-reveal data-reset-on-close style="padding-bottom: 50px;">
    <h2><?php echo esc_html( sprintf( _x( '%s Record History', 'Record History', 'disciple_tools' ), $post->post_title ) ); ?></h2>
    <hr>

    <div class="grid-container">
        <div class="grid-x">
            <div class="cell small-4">
                <table style="border: none;  max-width: 300px;">
                    <tbody style="border: none;">
                    <tr>
                        <td colspan="2">
                            <h4><?php esc_html_e( 'Filter To Date', 'disciple_tools' ) ?></h4>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <select id="record_history_calendar">
                                <option value="" selected disabled>--- <?php esc_html_e( 'select date to filter by', 'disciple_tools' ) ?> ---</option>
                            </select>
                        </td>
                    </tr>
                    <tr style="border: none;">
                        <td style="vertical-align: top;">
                            <span><?php esc_html_e( 'show all', 'disciple_tools' ) ?></span>
                        </td>
                        <td>
                            <div class="switch tiny">
                                <input class="switch-input" id="record_history_all_activities_switch" type="checkbox"
                                       name="record_history_all_activities_switch" checked>
                                <label class="switch-paddle" for="record_history_all_activities_switch">
                        <span
                            class="show-for-sr"><?php esc_html_e( 'show all', 'disciple_tools' ) ?></span>
                                </label>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <div class="cell small-8">
                <span id="record_history_progress_spinner" class="loading-spinner" style="display: table; margin: 0 auto 10px;"></span>
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
