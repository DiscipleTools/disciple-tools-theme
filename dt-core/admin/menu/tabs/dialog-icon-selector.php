<dialog id="dt_icon_selector_dialog">
    <table>
        <tbody>
        <tr>
            <td>
                <input id="dialog_icon_selector_filter_input"
                       type="search"
                       placeholder="<?php esc_html_e( 'Search Icons...', 'disciple_tools' ) ?>">
            </td>
        </tr>
        </tbody>
    </table>
    <br>
    <div style="text-align: center;">
        <span id="dialog_icon_selector_icons_search_spinner" class="loading-spinner" style="display: none;"></span>
        <span id="dialog_icon_selector_icons_search_msg" style="display: none;"></span>
    </div>
    <div id="dialog_icon_selector_icons_div">
        <table id="dialog_icon_selector_icons_table">
            <tbody></tbody>
        </table>
    </div>
    <div id="dialog_icon_selector_icons_sandbox_div"></div>
</dialog>
