<?php

/**
 * Theme Pusher Settings Admin Options Page
 */

?>

<div class="wrap">

    <form action="options.php" method="post">
        <div class="site-list">
        <?php 
            settings_fields( 'theme_pusher_display_options' );
            do_settings_sections('theme_pusher_display_options'); 
            submit_button( 'Save Site Info' );
        ?>
        </div>
        
        <div class="loader-wrap">
            <div class="loader">
                <!--<div class="square square1"></div> -->
                <div class="square square1"></div>
                <div class="square square2"></div>
                <div class="square square3"></div>
            </div>
        </div>
    </form>

</div>