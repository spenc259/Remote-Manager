<?php

class ThemePusher 
{
    public function __construct()
    {
        add_action( 'wp_ajax_nopriv_theme_pusher', array($this, 'theme_pusher'));
        add_action( 'wp_ajax_theme_pusher', array($this, 'theme_pusher'));
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
        // $dest = 'base';

        preg_match("/\w*.\z/", $folder, $output);
        
        $dest = $output[0];

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
}
