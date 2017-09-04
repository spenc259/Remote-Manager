<?php

/**
 * Theme Pusher Main Class
 * @since 1.0
 */
class ThemePusher
{
    private static $instance;
    private $directory;
    
    function __construct()
    {
        $this->set_directory_value();

        add_action( 'admin_enqueue_scripts', array($this, 'add_scripts'));
        add_action( 'wp_ajax_nopriv_theme_pusher', array($this, 'theme_pusher'));
        add_action( 'wp_ajax_theme_pusher', array($this, 'theme_pusher'));

        // Plugin settings
        add_action('admin_init', array($this, 'theme_pusher_admin_settings_init')); // settings
        add_action('admin_menu', array($this, 'theme_pusher_options_page')); // options page
    }


    /**
     * Instance loader
     * @since 1.0
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
     * @since 1.0
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
     * @since 1.0
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
     * @since 1.0
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
     * Plugin Settings
     * Add a menu item called theme Settings
     * @params for add_menu_page: 
     * string $page_title, string $menu_title, string $capability, 
     * string $menu_slug, callable $function = '', string $icon_url = '', int $position = null
     */        
    function theme_pusher_options_page()
    {
        add_menu_page(
            'Theme Pusher', // PAGE TITLE
            'Theme Pusher', // MENU TITLE
            'manage_options', // CAPABILITY
            'theme_pusher', // MENU SLUG
            array($this, 'theme_pusher_settings_view'), // FUNCTION NAME
            'dashicons-networking', // ICON URL
            33 // POSITION
        );
    }


    /**
     * Callback to load the admin view HTML
     */
    function theme_pusher_settings_view()
    {
        if ( !current_user_can('manage_options') ) return;

        include ABS_FOLDER . '/admin/theme_pusher_settings_view.php';
    }


    /**
     * register settings
     */
    function theme_pusher_admin_settings_init()
    {
        if (false == get_option( 'theme_pusher_display_options' )) {
            add_option( 'theme_pusher_display_options' );
        }
        // register a new section in the "theme_pusher" page
        add_settings_section(
            'pusher_sites', // ID
            'Manage Sites', // TITLE
            array($this, 'pusher_sites_instructions'), // CB
            'theme_pusher_display_options' // PAGE
        );

        add_settings_field( 
            'site_one', // ID
            'Site 1', // TITLE
            array($this, 'site_one'), // CB
            'theme_pusher_display_options', // PAGE
            'pusher_sites' // SECTION ID
        );

        add_settings_field( 
            'site_two', // ID
            'Site 2', // TITLE
            array($this, 'site_two'), // CB
            'theme_pusher_display_options', // PAGE
            'pusher_sites' // SECTION ID
        );

        add_settings_field( 
            'site_three', // ID
            'Site 3', // TITLE
            array($this, 'site_three'), // CB
            'theme_pusher_display_options', // PAGE
            'pusher_sites' // SECTION ID
        );

        register_setting( 
            'theme_pusher_display_options', // OPTION GROUP 
            'theme_pusher_display_options',// OPTION NAME
            array($this, 'sanitize' ) // SANITIZE CB
        );
        
    }

    public function pusher_sites_instructions()
    {
        echo "Please use the push updates button to transfer the changes from this theme to the satellites.";
    }

    public function site_one()
    {
        $options = get_option( 'theme_pusher_display_options' );
        echo '<div class="site">
                <div class="site-title info">
                    <input type="text" name="theme_pusher_display_options[site_one][site_url]" value="' . (!empty($options)) ? $options['site_one']['site_url'] : '' . '" />
                </div>
                <div class="version info">
                    <input type="text" name="theme_pusher_display_options[site_one][version_no]" value="' . $options[site_one]['version_no'] . '" />
                </div>
                <div class="update info">
                    <button type="submit" data-url="' . $options[site_one]['site_url'] . '" class="button base-push-updates">Push Updates</button>
                </div>
            </div>';
    }

    public function site_two()
    {
        $options = get_option( 'theme_pusher_display_options' );
        echo '<div class="site">
                <div class="site-title info">
                    <input type="text"name="theme_pusher_display_options[site_two][site_url]" value="' . $options[site_two]['site_url'] . '" />
                </div>
                <div class="version info">
                    <input type="text" name="theme_pusher_display_options[site_two][version_no]" value="' . $options[site_two]['version_no'] . '" />
                </div>
                <div class="update info">
                    <button type="submit" data-url="' . $options[site_two]['site_url'] . '" class="button base-push-updates">Push Updates</button>
                </div>
            </div>';
    }

    public function site_three()
    {
        $options = get_option( 'theme_pusher_display_options' );
        echo '<div class="site">
                <div class="site-title info">
                    <input type="text" name="theme_pusher_display_options[site_three][site_url]" value="' . $options[site_three]['site_url'] . '" />
                </div>
                <div class="version info">
                    <input type="text" name="theme_pusher_display_options[site_three][version_no]" value="' . $options[site_three]['version_no'] . '" />
                </div>
                <div class="update info">
                    <button type="submit" data-url="' . $options[site_three]['site_url'] . '" class="button base-push-updates">Push Updates</button>
                </div>
            </div>';
    }


    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
    	$new_input = array('test');
        
    	if( isset($input['site_one']['site_url']) )
    		$new_input['site_one']['site_url'] = $input['site_one']['site_url'];
        if( isset($input['site_one']['version_no']) )
    		$new_input['site_one']['version_no'] = $input['site_one']['version_no'];
        
    	if( isset($input['site_two']['site_url']) )
    		$new_input['site_two']['site_url'] = $input['site_two']['site_url'];
        if( isset($input['site_two']['version_no']) )
    		$new_input['site_two']['version_no'] = $input['site_two']['version_no'];
        
    	if( isset($input['site_three']['site_url']) )
    		$new_input['site_three']['site_url'] = $input['site_three']['site_url'];
        if( isset($input['site_three']['version_no']) )
    		$new_input['site_three']['version_no'] = $input['site_three']['version_no'];

    	return $new_input;
    }


    /**
     * Set the directory
     */
    public function set_directory_value(){
        $this->directory = plugins_url() . '/' . BASE_FOLDER;
    }

    /**
     * testing functions
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
}
