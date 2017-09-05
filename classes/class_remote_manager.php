<?php
/**
 * Remote Manager Main Class
 * @since 0.1
 */
class RemoteManager
{
    private static $instance;
    private $directory;
    public $pusher, $updater;
    
    function __construct()
    {
        $this->set_directory_value();
        $this->loader();

        add_action( 'init', array( $this, 'remotemanager_post_type') );
        add_action( 'admin_enqueue_scripts', array($this, 'add_scripts'));
    }


    /**
     * Instance loader
     * @since 0.1
     * @param string $file current file
     * @param string $version current version number
     * @return object $instance singleton instance of the class
     */
    public static function instance( $file = '', $version = '1.0')
    {
        if ( is_null(self::$instance) ) {
            self::$instance = new self( $file, $version );
        }
        return self::$instance;
    }


    /**
     * Class Loader
     * @since 0.1
     */
    public function loader()
    {
        $this->pusher = new ThemePusher();
        $this->updater = new PluginUpdater();
    }


    /**
     * scripts and styles
     * @since 0.1
     * Load all the scripts and styles
     **/
    public function add_scripts()
    {
        // enqueue the script
        wp_enqueue_script( 
            'push-js', 
            $this->directory . '/assets/js/scripts.js', 
            array('jquery') 
        );
        wp_enqueue_script( 
            'update-js', 
            $this->directory . '/assets/js/plugin-updater-scripts.js', 
            array('jquery') 
        );
        wp_enqueue_style( 
            'push-css', 
            $this->directory . '/assets/css/styles.css' 
        );
        // data array to pass to localize scripts
        $data = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'remote_manager' )
        );
        wp_localize_script( 'push-js', 'zip_theme', $data );

        $data = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'pluginupdater' )
        );
        wp_localize_script( 'update-js', 'plugin_updater', $data );
    }


    /**
     * Set the directory
     */
    public function set_directory_value(){
        $this->directory = plugins_url() . '/' . BASE_FOLDER;
    }


    /**
     * register post type
     * @since 0.1
     * @return void
     */
    function remotemanager_post_type() {
        register_post_type( 'sites_remotemanager', array(
            'labels' => array(
                'name' => __( 'Site', 'remotemanager' ),
                'singular_name' => __( 'Sites', 'remotemanager' ),
            ),
            'public' => false,
            'hierarchical' => false,
            'rewrite' => false,
        ) );
    }
}
