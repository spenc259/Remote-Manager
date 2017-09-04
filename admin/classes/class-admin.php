<?php

/**
 * themepusher Admin Class
 * @since 0.1
 */

class ThemePusherAdmin
{

    private $settings;

    function __construct()
    {
        add_action('admin_menu', array($this, 'themepusher_options_page')); // options page
        add_action('admin_init', array($this, 'themepusher_admin_settings_init')); // settings

        // $this->set_admin_settings();
    }


    /**
     * Plugin Settings
     * Add a menu item called theme Settings
     * @since 0.1
     */        
    function themepusher_options_page()
    {
        add_menu_page(
            'themepusher', // PAGE TITLE
            'Theme Pusher', // MENU TITLE
            'manage_options', // CAPABILITY
            'themepusher', // MENU SLUG
            // array($this, 'themepusher_settings_view'), // FUNCTION NAME
            array($this, 'load_admin_step'), // FUNCTION NAME
            'dashicons-networking', // ICON URL
            33 // POSITION
        );

        add_submenu_page( 'themepusher', 'Site List', 'Site List', 'manage_options', 'themepusher' );
        add_submenu_page( 'themepusher', 'Add Site', 'New Site', 'manage_options', 'themepusher&action=add', array($this, 'themepusher_add_site') );
    }


    /**
     * register settings that will appear in the options page
     * @since 0.1
     */
    function themepusher_admin_settings_init()
    {
        if (false == get_option( 'themepusher_display_options' )) {
            add_option( 'themepusher_display_options' );
        }
        add_settings_section(
            'themepusher_options_info', // ID
            'Remote Site Manager', // TITLE
            array($this, 'themepusher_options_instructions'), // CB
            'themepusher_options_info' // PAGE
        );
        add_settings_section(
            'themepusher_options', // ID
            '', // TITLE
            '', // CB
            'themepusher_display_options' // PAGE
        );
        add_settings_field( 
            'site_list', // ID
            '', // TITLE
            array($this, 'site_list'), // CB
            'themepusher_display_options', // PAGE
            'themepusher_options' // SECTION ID
        );
        register_setting( 
            'themepusher_display_options', // OPTION GROUP 
            'themepusher_display_options',// OPTION NAME
            array($this, 'sanitize' ) // SANITIZE CB
        );
    }

    /**
     * Check current action
     * @since 0.1
     */
    function get_current_action()
    {
        return isset( $_GET['action'] ) ? $_GET['action'] : '';
    }


    /**
     * callback to load a settings page based on action
     * @since 0.1
     */
    function load_admin_step()
    {
        switch ( $this->get_current_action() ) {
            case 'add':
            case 'edit':
                $this->themepusher_add_edit_site();
                break;
            case 'update':
                $this->themepusher_update_site();
                break;
            case 'delete':
                $this->themepusher_delete();
            default:
                $this->themepusher_site_list();
                break;
        }
    }


    /**
     * load the admin list HTML
     * @since 0.1
     */
    function themepusher_site_list()
    {
        if ( !current_user_can('manage_options') ) return;
        $auto_check_site_manager = new themepusher_List_Table();
        $auto_check_site_manager->prepare_items();

        include ABS_FOLDER  . '/admin/views/site-list.php';
    }


    /**
     * load the admin add new form
     * @since 0.1
     */
    function themepusher_add_edit_site()
    {
        if ( !current_user_can('manage_options') ) return;

        $site = '';

        if ( ! empty( $_REQUEST['id'] ) ) {
            $id = absint( $_REQUEST['id'] );
            $site = new themepusher_CRUD();
            $site = $site->get( $id );
            echo '<pre>'; print_r($site); echo '</pre>';
            $url = get_post_meta( $id, 'url', true);
        }
        
        if ( ! empty( $_POST['submit'] ) ) {
            $this->form_handler( $this->get_current_action(), $site );
        }

        include ABS_FOLDER  . '/admin/views/add-edit-site.php';
        
    }


    /**
     * load the admin edit form
     * @since 0.1
     */
    function themepusher_update_site()
    {
        if ( ! empty( $_REQUEST['id'] ) ) {
            $id = absint( $_REQUEST['id'] );
            $site = new themepusher_CRUD();
            $site = $site->get( $id );
            // echo '<pre>'; print_r($site); echo '</pre>';
            $url = get_post_meta( $id, 'url', true);
        }
        if ( !current_user_can('manage_options') ) return;
        include ABS_FOLDER  . '/admin/views/update-site.php';
    }


    /**
     * Callback to display the description on the options page
     * @since 0.1
     * @return void
     */
    public function themepusher_options_instructions()
    {
        echo '
            Please enter a URL of a site you would like to manage.
            <br /><br />';
            
        $options = get_option( 'themepusher_display_options' );
        //print_r($options);
    }


    /**
	 * Get the URL for an admin page.
     * @since 0.1
	 * @param array|string $params Map of parameter key => value, or wp_parse_args string.
	 * @return string Requested URL.
	 */
	public function get_url( $params = array() ) {
		$url = admin_url( 'admin.php' );
		$params = array( 'page' => 'themepusher' ) + wp_parse_args( $params );
		return add_query_arg( urlencode_deep( $params ), $url );
	}


    /**
     * Handle the form submissions
     * @since 0.1
     */
    function form_handler( $action = '', $site )
    {
        $params['action'] = $action;

        if ( isset($_POST) ) {
            foreach ( $_POST as $key => $value ) {
                $params[$key] = $value;
            }

            $data = array(
                'ID'   => $params['ID'],
                'name' => $params['name'],
                'meta' => array(
                    'url' => $params['site_url']
                )
            );
        }

        $crud = new themepusher_CRUD();

        if ( $action == 'edit' ) {
            $crud->update( $data, $site );
        } else {
            $crud->create( $data );
        }
    }


    /**
     * Delete a Site
     * @since 0.1
     */
    function themepusher_delete()
    {
        if ( empty( $_GET['id'] ) ) return;

        $id = $_GET['id'];

        if ( ! current_user_can( 'delete_post', $id ) ) {
            wp_die( 'you can not delete a post', 403 );
        }

        $crud = new themepusher_CRUD();
        $crud->get( $id );

        if ( ! $crud->delete( $id ) ) {
			$message = 'Invalid consumer ID';
			wp_die( $message );
			return;
		}
    }
}

