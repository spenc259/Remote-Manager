<?php 
/**
 * Site Manager
 * @since 0.1
 */

do_settings_sections('themepusher_options_info');
?>
<div class="wrap">
    <form action="" method="post">
        <div class="form-wrap">
            <table class="form">
                <tr class="tr-row">
                    <th class="theading">Name</th>
                    <th class="theading">URL</th>
                </tr>
                <tr class="tr-row">
                    <td>
                        <div class="input text url">
                            <input type="text" name="name" data-url="<?php echo ( !empty($site->post_title) ) ? $site->post_title : ''; ?>" value="<?php echo ( !empty($site->post_title) ) ? $site->post_title : ''; ?>" />
                        </div>
                    </td>
                    <td>
                        <div class="input text url">
                            <input type="text" name="site_url" data-url="<?php echo ( !empty($url) ) ? $url : ''; ?>" value="<?php echo ( !empty($url) ) ? $url : ''; ?>" />
                        </div>
                    </td>
                    <input type="hidden" name="ID" data-url="<?php echo ( !empty($_GET['id']) ) ? $_GET['id'] : ''; ?>" value="<?php echo ( !empty($_GET['id']) ) ? $_GET['id'] : ''; ?>" />
                </tr>
            </table>
            <table class="form">
                <tr class="tr-row">
                    <td><?php submit_button( 'Save Site Info' ); ?></td>
                </tr>
            </table>
        </div>
        <div class="loader-wrap">
            <div class="loader">
                <div class="square square1"></div>
                <div class="square square2"></div>
                <div class="square square3"></div>
            </div>
        </div>
    </form>
</div>