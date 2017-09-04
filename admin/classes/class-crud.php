<?php
/**
 * Theme Pusher CRUD Operations
 * @since 0.1
 */

class themepusher_CRUD
{
    /**
     * Create a New Site
     * @param array $data new site entry
     * @since 0.1
     */
    function create( $params )
    {
        $defaults = array(
            'name' => '',
            'meta' => array()
        );
        $params = wp_parse_args( $params, $defaults );
        $data = array(
            'post_title' => $params['name'],
            'post_type' => 'sites_themepusher'
        );
        $ID = wp_insert_post( $data );
        if ( is_wp_error( $ID ) ) {
            return $ID;
        }
        $meta = $params['meta'];
        foreach ($meta as $key => $value) {
            update_post_meta( $ID, $key, $value );
        }
        return true;
    }

    /**
     * Get a site
     * @since 0.1
     */
    function get( $id )
    {
        $site = get_post( $id );

        if ( empty($id) || $site->post_type !== 'sites_themepusher' ) {
            return new WP_Error( 'themepusher_invalid_id', __( 'Not a Valid Site', 'themepusher' ), array( 'status' => 404 ) );
        }
        
        return $site;
    }

    /**
     * Update a site
     * @since 0.1
     */
    function update( $data, $post )
    {
        $postarr = array(
            'ID'         => $data['ID'],
            'post_title' => $data['name'],
            'post_type'  => 'sites_themepusher'
        );
        $meta = $data['meta'];
        foreach ($meta as $key => $value) {
            update_post_meta( $data['ID'], $key, $value );
        }
        return wp_update_post( $postarr );
    }

    /**
     * Delete a Site
     * @since 0.1
     */
    function delete( $id )
    {
        return wp_delete_post( $id, true );
    }
}
