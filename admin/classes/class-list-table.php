<?php

/**
 * A Table to list the sites to manage remote updates for in the admin area
 * @since 0.1
 */
class themepusher_List_Table extends WP_List_Table
{
    public $items;

    function __construct()
    {
        parent::__construct( array(
            'singular' => 'wp_list_text_update',
            'plural' => 'wp_list_available_updates',
            'ajax' => false
        ));
    }

    /**
     * Prepare the table and assign items to the table
     * @since 0.1
     */
    function prepare_items()
    {
        $paged = $this->get_pagenum();
		$args = array(
			'post_type' => 'sites_themepusher',
			'post_status' => 'any',
			'meta_query' => array(
				array(
					'key' => 'url',
				),
			),
			'paged' => $paged,
		);
		$query = new WP_Query( $args );
		$this->items = $query->posts;
        // Register Columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
		$pagination_args = array(
			'total_items' => $query->found_posts,
			'total_pages' => $query->max_num_pages,
			'per_page' => $query->get('posts_per_page')
		);
		$this->set_pagination_args( $pagination_args );
    }

    /**
     * define sortable columns
     * @since 0.1
     */
    function get_sortable_columns()
    {
        return $sortable = array(
            'col_name' => 'name'
        );
    }

    /**
     * define the columns that are in the table
     * @since 0.1
     * @return array $columns
     */
    function get_columns()
    {
        return $columns = array(
            'cb'          => '<input type="checkbox" />',
            'name' => __( 'Name' ),
            'site_url' => __( 'Site URL' ),
			'update' => __( 'Update' )
        );
    }

	/**
	 * column cb callback
     * @since 0.1
	 * @param obj $item
	 * @return void
	 */
    public function column_cb( $item ) {
		?>
		<label class="screen-reader-text"
			for="cb-select-<?php echo esc_attr( $item->ID ) ?>"><?php esc_html_e( 'Select consumer', 'rest_oauth1' ); ?></label>
		<input id="cb-select-<?php echo esc_attr( $item->ID ) ?>" type="checkbox"
			name="consumers[]" value="<?php echo esc_attr( $item->ID ) ?>" />
		<?php
	}

	/**
	 * column name callback
     * @since 0.1
	 *
	 * @param obj $item
	 * @return string
	 */
	protected function column_name( $item ) {
		$title = get_the_title( $item->ID );
		if ( empty( $title ) ) {
			$title = '<em>' . esc_html__( 'Untitled', 'themepusher' ) . '</em>';
		}
		$edit_link = add_query_arg(
			array(
				'page'   => 'themepusher',
				'action' => 'edit',
				'id'     => $item->ID,
			),
			admin_url( 'admin.php' )
		);
		$delete_link = add_query_arg(
			array(
				'page'   => 'themepusher',
				'action' => 'delete',
				'id'     => $item->ID,
			),
			admin_url( 'admin.php' )
		);
		$delete_link = wp_nonce_url( $delete_link, 'rest-oauth1-delete:' . $item->ID );
		$actions = array(
			'edit' => sprintf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html__( 'Edit', 'themepusher' ) ),
			'delete' => sprintf( '<a href="%s">%s</a>', esc_url( $delete_link ), esc_html__( 'Delete', 'themepusher' ) ),
		);
		$action_html = $this->row_actions( $actions );
		return $title . ' ' . $action_html;
	}

	/**
	 * column site url callback
     * @since 0.1
	 * @param obj $item
	 * @return string
	 */
    function column_site_url( $item ) {
        return get_post_meta( $item->ID, 'url', true );
	}

	/**
	 * Collumn update callback
     * @since 0.1
	 * @param obj $item
	 * @return string
	 */
	function column_update( $item ) {
		$update_link = add_query_arg(
			array(
				'page'   => 'themepusher',
				'action' => 'update',
				'id'     => $item->ID,
			),
			admin_url( 'admin.php' )
		);
		return sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $update_link ), 'custombtn no-margin', esc_html__( 'Update', 'themepusher' ) );
	}
}
