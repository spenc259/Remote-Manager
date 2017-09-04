<?php

/**
 * Theme Pusher Main Class
 * @since 0.1
 */
class ThemePusher
{
    private static $instance;
    private $directory;
    
    function __construct()
    {
        $this->set_directory_value();

        add_action( 'init', array( $this, 'themepusher_post_type') );

        add_action( 'admin_enqueue_scripts', array($this, 'add_scripts'));
        add_action( 'wp_ajax_nopriv_theme_pusher', array($this, 'theme_pusher'));
        add_action( 'wp_ajax_theme_pusher', array($this, 'theme_pusher'));
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
     * Recursive Directory Archiver
     * @since 0.1
     * @param object $zip, string $folder, string $dest
     * @return void
     */

    public function zipDir( $zip, $folder, $dest )
    {
        $baseFolder = $dest;
        $files = scandir( $folder );
        $files = array_diff( $files, array('..','.') );

        foreach ($files as $file) {
            if ( is_dir( $folder . '/' . $file ) ) {
                $zip->addEmptyDir( $baseFolder . '/' . $file ); // works
                $this->zipDir( $zip, $folder . '/' . $file, $dest . '/' . $file );
            }
            else { 
                $zip->addFile($folder . '/' . $file, $baseFolder . '/' . $file); // works
            }
        }
    }


    /**
     * Theme Pusher
     * Zip up a given theme folder and make a HTTP POST request
     * to a remote satellite site
     * @since 0.1
     * @return void - exits on completion
     */
    public function theme_pusher()
    {  
        $data = array();
        $folder = get_stylesheet_directory(); // '/var/www/vhosts/icl1.co.uk/httpdocs/wp-content/themes/base';
        $dest = 'base';

        // create a zip of the theme files
        $zip = new ZipArchive;
        $open = $zip -> open( $folder . '.zip', ZipArchive::OVERWRITE );
        
        if ($open){
            $this->zipDir( $zip, $folder, $dest );
        }

        $zip -> close();

        $outputLink = site_url( '/wp-content/themes/base.zip' );
        $args = array(
            'headers' => array(),
            'body' => array(
                'file_url' => $outputLink
            )    
        );

        $url = $_POST['url'];
        $request = wp_remote_post( $url, $args );
        
        $data['status'] = 'Archiving is complete!';
        $data['output'] = $outputLink;
        $data['open'] = $open;
        $data['url'] = $url;
        $data['folder'] = $folder;
        $data['request'] = $request;

        wp_send_json_success( $data );

        exit;
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

        wp_enqueue_style( 
            'push-css', 
            $this->directory . '/assets/css/styles.css' 
        );

        // data array to pass to localize scripts
        $data = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'theme_pusher' )
        );
        wp_localize_script( 'push-js', 'zip_theme', $data );
    }


    /**
     * Set the directory
     */
    public function set_directory_value(){
        $this->directory = plugins_url() . '/' . BASE_FOLDER;
    }


    /**
     * testing functions
     * @since 0.0.1
     */
    function test_theme_pusher()
    {  
        $data = array();
        $folder = '/var/www/vhosts/icl1.co.uk/httpdocs/wp-content/themes/base';
        $dest = 'base';

        // create a zip of the theme files
        $zip = new ZipArchive; // $data['zip'] = $zip;
        $open = $zip -> open('/var/www/vhosts/icl1.co.uk/httpdocs/wp-content/themes/test.zip', ZipArchive::OVERWRITE);
        
        if ($open){
            $this->TESTzipDir( $zip, $folder, $dest);
        }

        $zip -> close();

    }

    function TESTzipDir( $zip, $folder, $dest )
    {
        $baseFolder = $dest;
        $files = scandir( $folder );
        $files = array_diff( $files, array('..','.') );

        echo "CURRENT FOLDER: " . $folder . "<br>";
        
        foreach ($files as $file) {
            if ( is_dir( $folder . '/' . $file ) ) {
                echo "<br>ZIP DIR: " . $baseFolder . '/' . $file . '<br>';
                $this->TESTzipDir( $zip, $folder . '/' . $file, $dest . '/' . $file );
            }
            else { 
                echo "ZIP FILE: " . $baseFolder . '/' . $file . '<br><br>';
            }
        }

    }

    /**
     * register post type
     * @since 0.1
     * @return void
     */
    function themepusher_post_type() {
        register_post_type( 'sites_themepusher', array(
            'labels' => array(
                'name' => __( 'Site', 'themepusher' ),
                'singular_name' => __( 'Sites', 'themepusher' ),
            ),
            'public' => false,
            'hierarchical' => false,
            'rewrite' => false,
        ) );
    }
}
