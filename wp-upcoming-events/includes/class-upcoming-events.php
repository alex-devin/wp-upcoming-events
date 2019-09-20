<?php

class UPCOMING_EVENTS {

    protected $UPCOMING_EVENTS_settings;
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct($options) {

        $this->UPCOMING_EVENTS_settings = $options['settings'];
        $this->plugin_name = $options['plugin_name'];
        $this->version = UPCOMING_EVENTS_VERSION;

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_wp_crons();
    }

    private function load_dependencies() {

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-upcoming-events-loader.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-upcoming-events-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/public/class-upcoming-events-public.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/cron/class-upcoming-events-cron.php';

        $this->loader = new UPCOMING_EVENTS_LOADER();
    }

    private function define_admin_hooks() {

        $plugin_admin = new UPCOMING_EVENTS_ADMIN( array(
            'settings' => $this->UPCOMING_EVENTS_settings,
            'plugin_name' => $this->plugin_name,
            'version' => $this->version
        ));

        // ACTIONS
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'UPCOMING_EVENTS_admin_menu', 15, 0 );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'UPCOMING_EVENTS_enqueue_styles', 15, 1 );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'UPCOMING_EVENTS_register_admin_script', 15, 1 );
    }

    private function define_public_hooks() {

        $plugin_public = new UPCOMING_EVENTS_PUBLIC( array(
            'settings' => $this->UPCOMING_EVENTS_settings,
            'plugin_name' => $this->plugin_name,
            'version' => $this->version
        ));

        // ACTIONS
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'UPCOMING_EVENTS_register_public_script', 15, 1 );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'UPCOMING_EVENTS_register_public_style', 15, 1 );

        // SHORTCODES
        $this->loader->add_shortcode( 'UPCOMING_EVENTS', $plugin_public, 'UPCOMING_EVENTS_do_shortcode' ); // [UPCOMING_EVENTS]
    }

    private function define_wp_crons(){

        $plugin_cron = new UPCOMING_EVENTS_CRON( array(
            'settings' => $this->UPCOMING_EVENTS_settings,
            'plugin_name' => $this->plugin_name,
            'version' => $this->version
        ));


        // CRON 1: FETCH EVENTS DATA EVERY HOUR AND PUT IT INTO THE TABLE
        $task_hook = 'fetch_events_data';
        $this->loader->add_action( $task_hook, $plugin_cron, 'upcoming_events_update_data_cron', 15,1);
        if ( ! wp_next_scheduled( $task_hook ) ) {

            wp_schedule_event( time(), 'hourly', $task_hook );
        }

        // CRON 2: TODO...
        
    }

    public function run() {

        $this->loader->run();
    }
}
