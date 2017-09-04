<?php
/**
 * themepusher Settings Admin Options Page
 * @since 0.1
 */
?>
<div class="wrap">

    <h2>
        <?php
        esc_html_e( 'Site List', 'themepusher' );

        if ( current_user_can( 'create_users' ) ): ?>
            <a href="<?php echo esc_url( self::get_url( 'action=add' ) ) ?>"
                class="add-new-h2"><?php echo esc_html_x( 'Add New', 'site', 'themepusher' ); ?></a>
        <?php
        endif;
        ?>
    </h2>

    <p>Select a site to manage</p>

    <form action="options.php" method="get">
        <?php $auto_check_site_manager->search_box( __( 'Search Sites', 'themepusher' ), 'themepusher' ); ?>
        <?php $auto_check_site_manager->display(); ?>
    </form>

</div>