# DT-Metrics
The DT-Metrics module handles all the collection and presenting of system statistics. It is the chief location for
building reporting for the critical path from other parts of the system.


## Critical Path
### Prayer
Prayer Network is a total of people engaged through multiple channels.
1. __Email__ _(MailChimp, iContact, etc.)_ This is the current total of people subscribed to the email list. This does not factor the number of emails sent.
1. __Social Media__ _(Facebook, Twitter)_ This collects the current total of people who have 'liked' or 'followed'. This does not factor 'reach' or 'page views'.
1. __Website__ To track a website as a source for the prayer network, there needs to be a user registration. Website visits are not counted.
1. __Mobile App__ (aspirational) We hope to produce a mobile app that allows people to track and respond with prayer to new contacts and group starts.
1. __SMS/Texting__ _(EZTexting, etc)_ Texting list subscribers.
### Social Engagement

### Website Visits
### New Contacts
### Contacts Attempted
### Contacts Established
### First Meetings
### Baptisms
### Baptizers
### Active Groups
### Active Churches
### Chuch Planters


### Example of Metrics Menu Extension:
```$xslt
class Disciple_Tools_Metrics_Project extends Disciple_Tools_Metrics_Hooks_Base
{
    public function __construct() {
        add_filter( 'dt_templates_for_urls', [ $this, 'add_url' ] ); // add custom URL
        add_filter( 'dt_metrics_menu', [ $this, 'menu' ], 1 ); // add new menu items
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 999 ); // enqueue required scripts

        parent::__construct();
    }
        
    /**
     * Add New URL Endpoint
     * @see functions.php:213
     */
    public function add_url( $template_for_url ) {
        $template_for_url['metrics/project'] = 'template-metrics.php';
        return $template_for_url;
    }

    /**
     * Add menu items
     * @see template-metrics.php:21
     */
    public function menu( $content ) {
        $content .= '<li><a href="'. site_url( '/metrics/project/' ) .'#dt_overview" onclick="show_zume_project()">' .  esc_html__( 'Project', 'disciple_tools' ) . '</a>
            <ul class="menu vertical nested is-active">
              <li><a href="'. site_url( '/metrics/project/' ) .'#dt_overview" onclick="show_zume_project()">' .  esc_html__( 'Overview', 'disciple_tools' ) . '</a></li>
              <li><a href="'. site_url( '/metrics/project/' ) .'#dt_contacts" onclick="show_zume_locations()">' .  esc_html__( 'Contacts', 'disciple_tools' ) . '</a></li>
            </ul>
          </li>';
        return $content;
    }

    /**
     * Enqueue required scripts
     */
    public function scripts() {
        $url_path = trim( parse_url( add_query_arg( array() ), PHP_URL_PATH ), '/' );

        if ( 'metrics/project' === $url_path ) {
            wp_enqueue_script( 'dt_project_metrics_script', get_stylesheet_directory_uri() . '/dt-metrics/metrics.js', [
                'jquery',
                'jquery-ui-core',
            ], filemtime( get_theme_file_path() . '/dt-metrics/metrics.js' ), true );

            wp_localize_script(
                'dt_project_metrics_script', 'dtMetrics', [
                    'root' => esc_url_raw( rest_url() ),
                    'plugin_uri' => Disciple_Tools::instance()->theme_url,
                    'nonce' => wp_create_nonce( 'wp_rest' ),
                    'current_user_login' => wp_get_current_user()->user_login,
                    'current_user_id' => get_current_user_id(),
                    'map_key' => dt_get_option( 'map_key' ),
                    'translations' => [
                        "zume_project" => __( "Zúme Overview", "dt_zume" ),
                        "zume_groups" => __( "Zúme Groups", "dt_zume" ),
                        "zume_people" => __( "Zúme People", "dt_zume" ),
                        "zume_locations" => __( "Zúme Locations", "dt_zume" ),
                    ]
                ]
            );
        }
    }

    
}
```
