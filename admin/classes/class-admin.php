<?php

/**
 * remotemanager Admin Class
 * @since 0.1
 */

class RemoteManagerAdmin
{

    private $settings;

    function __construct()
    {
        add_action('admin_menu', array($this, 'remotemanager_options_page')); // options page
        add_action('admin_init', array($this, 'remotemanager_admin_settings_init')); // settings

        // $this->set_admin_settings();
    }


    /**
     * Plugin Settings
     * Add a menu item called theme Settings
     * @since 0.1
     */        
    function remotemanager_options_page()
    {
        add_menu_page(
            'remotemanager', // PAGE TITLE
            'Remote Manager', // MENU TITLE
            'manage_options', // CAPABILITY
            'remotemanager', // MENU SLUG
            // array($this, 'remotemanager_settings_view'), // FUNCTION NAME
            array($this, 'load_admin_step'), // FUNCTION NAME
            'dashicons-networking', // ICON URL
            33 // POSITION
        );

        add_submenu_page( 'remotemanager', 'Site List', 'Site List', 'manage_options', 'remotemanager' );
        add_submenu_page( 'remotemanager', 'Add Site', 'New Site', 'manage_options', 'remotemanager&action=add', array($this, 'remotemanager_add_site') );
    }


    /**
     * register settings that will appear in the options page
     * @since 0.1
     */
    function remotemanager_admin_settings_init()
    {
        if (false == get_option( 'remotemanager_display_options' )) {
            add_option( 'remotemanager_display_options' );
        }
        add_settings_section(
            'remotemanager_options_info', // ID
            'Remote Site Manager', // TITLE
            array($this, 'remotemanager_options_instructions'), // CB
            'remotemanager_options_info' // PAGE
        );
        add_settings_section(
            'remotemanager_options', // ID
            '', // TITLE
            '', // CB
            'remotemanager_display_options' // PAGE
        );
        add_settings_field( 
            'site_list', // ID
            '', // TITLE
            array($this, 'site_list'), // CB
            'remotemanager_display_options', // PAGE
            'remotemanager_options' // SECTION ID
        );
        register_setting( 
            'remotemanager_display_options', // OPTION GROUP 
            'remotemanager_display_options',// OPTION NAME
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
                $this->remotemanager_add_edit_site();
                break;
            case 'update':
                $this->remotemanager_update_site();
                break;
            case 'delete':
                $this->remotemanager_delete();
            default:
                $this->remotemanager_site_list();
                break;
        }
    }


    /**
     * load the admin list HTML
     * @since 0.1
     */
    function remotemanager_site_list()
    {
        if ( !current_user_can('manage_options') ) return;
        $auto_check_site_manager = new remotemanager_List_Table();
        $auto_check_site_manager->prepare_items();

        include ABS_FOLDER  . '/admin/views/site-list.php';
    }


    /**
     * load the admin add new form
     * @since 0.1
     */
    function remotemanager_add_edit_site()
    {
        if ( !current_user_can('manage_options') ) return;

        $site = '';

        if ( ! empty( $_REQUEST['id'] ) ) {
            $id = absint( $_REQUEST['id'] );
            $site = new remotemanager_CRUD();
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
    function remotemanager_update_site()
    {
        if ( ! empty( $_REQUEST['id'] ) ) {
            $id = absint( $_REQUEST['id'] );
            $site = new remotemanager_CRUD();
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
    public function remotemanager_options_instructions()
    {
        echo '
            Please enter a URL of a site you would like to manage.
            <br /><br />';
            
        $options = get_option( 'remotemanager_display_options' );
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
		$params = array( 'page' => 'remotemanager' ) + wp_parse_args( $params );
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

        $crud = new remotemanager_CRUD();

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
    function remotemanager_delete()
    {
        if ( empty( $_GET['id'] ) ) return;

        $id = $_GET['id'];

        if ( ! current_user_can( 'delete_post', $id ) ) {
            wp_die( 'you can not delete a post', 403 );
        }

        $crud = new remotemanager_CRUD();
        $crud->get( $id );

        if ( ! $crud->delete( $id ) ) {
			$message = 'Invalid consumer ID';
			wp_die( $message );
			return;
		}
    }
}

