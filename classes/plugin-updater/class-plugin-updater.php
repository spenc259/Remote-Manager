<?php
/**
 * Plugin Updater
 * Holds the main plugin functionality
 * @since 0.1
 */
class PluginUpdater
{
    private static $instance;

    public function __construct()
    {
        add_action( 'wp_ajax_nopriv_get_updates', array($this, 'get_updates') );
        add_action( 'wp_ajax_get_updates', array($this, 'get_updates') );
        add_action( 'wp_ajax_nopriv_bulk_update', array($this, 'bulk_update') );
        add_action( 'wp_ajax_bulk_update', array($this, 'bulk_update') );
        add_action( 'wp_ajax_nopriv_single_update', array($this, 'single_update') );
        add_action( 'wp_ajax_single_update', array($this, 'single_update') );
    }

    /**
     * get all plugins that needs updated from remote site
     * @since 0.1
     */
    public function get_updates()
    {
        $this->auto_check('get_updates');
    }

    /**
     * bulk update plugins from remote site
     * @since 0.1
     */
    public function bulk_update()
    {
        $this->auto_check('bulk_update');
    }

    /**
     * Auto Checker
     * make a HTTP POST request
     * to a remote site with the beacon installed
     * @since 0.1
     * @param string $action
     * @return void - exits on completion
     */
    public function auto_check($action)
    {  
        $data = array();
        
        ( isset( $_POST['additional'] ) ) ? $additional = $_POST['additional'] : $additional = "";

        $args = array(
            'headers' => array(),
            'body' => array(
                $action => $action,
                'additional' => $additional
            )    
        );
        $url = $_POST['url'];
        $request = wp_remote_post( $url, $args );
        $body = wp_remote_retrieve_body( $request );
        $responses = json_decode( $body );
        foreach ( $responses as $response ) {
            $data['plugins'] = $response;
        }
        wp_send_json_success( $data );
        exit;
    }
}