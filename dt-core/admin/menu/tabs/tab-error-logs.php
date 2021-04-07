<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class Disciple_Tools_Tab_Logs
 */
class Disciple_Tools_Tab_Logs extends Disciple_Tools_Abstract_Menu_Base
{
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    /**
     * Constructor function.
     *
     * @access  public
     * @since   0.1.0
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_submenu'], 125);
        add_action('dt_utilities_tab_menu', [$this, 'add_tab'], 125, 1);
        add_action('dt_utilities_tab_content', [$this, 'content'], 125, 1);

        parent::__construct();
    } // End __construct()

    public function add_submenu()
    {
        add_submenu_page('edit.php?post_type=logs', __('Import', 'disciple_tools'), __('Import', 'disciple_tools'), 'manage_dt', 'dt_utilities&tab=logs', ['Disciple_Tools_Settings_Menu', 'content']);
        add_submenu_page('dt_utilities', __('Error Logs', 'disciple_tools'), __('Error Logs', 'disciple_tools'), 'manage_dt', 'dt_utilities&tab=logs', ['Disciple_Tools_Settings_Menu', 'content']);
    }

    public function add_tab($tab)
    {
        echo '<a href="' . esc_url(admin_url()) . 'admin.php?page=dt_utilities&tab=logs" class="nav-tab ';
        if ($tab == 'logs') {
            echo 'nav-tab-active';
        }
        echo '">' . esc_attr__('Error Logs') . '</a>';
    }

    public function content($tab)
    {
        if ('logs' == $tab) :

            $this->template('begin');

            $this->admin_tab_table();

            $this->template('right_column');

            $this->template('end');

        endif;
    }

    private function admin_tab_table()
    {
        global $wpdb;

        // Obtain list of recent error logs
        $logs = $wpdb->get_results("SELECT hist_time, object_type, object_name, object_note FROM wp_dt_activity_log WHERE action = 'error_log' ORDER BY hist_time DESC LIMIT 20");

        $this->box('top', 'Error Logs', ["col_span" => 4]);

        ?>
        <table class="widefat striped">
            <tr>
                <th>Timestamp</th>
                <th>Type</th>
                <th>Name</th>
                <th>Note</th>
            </tr>
        <?php
        if (!empty($logs)) {
            foreach ($logs as $log) {
                echo '<tr>';
                echo '<td>' . date("Y-m-d h:i:sa", esc_attr($log->hist_time)) . '</td>';
                echo '<td>' . esc_attr($log->object_type) . '</td>';
                echo '<td>' . esc_attr($log->object_name) . '</td>';
                echo '<td>' . esc_attr($log->object_note) . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        $this->box('bottom');
    }
}

Disciple_Tools_Tab_Logs::instance();
