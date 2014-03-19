<?
    /*
        Plugin Name: Twitter Profile Linker
        Plugin URI: https://github.com/stefanbc/TwitterProfileLinker
        Description: Generates links automatically, in a WordPress post, to Twitter profiles.
        Version: 0.1
        Author: Stefan Cosma
        Author URI: http://coderbits.com/stefanbc
        License: GPLv2 or later
        License URI: http://www.gnu.org/licenses/gpl-2.0.html
    */
    
    // Add option in General Settings for Twitter hovecards
    $new_general_setting = new new_general_setting();
    // Create the checkbox in General settings
    class new_general_setting {
        function new_general_setting( ) {
            add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
        }
        function register_fields() {
            register_setting( 'general', 'twitterHovercards', 'esc_attr' );
            add_settings_field('twitterHovercards', '<label for="twitterHovercards">'.__('Enable Twitter Hovercards?' , 'twitterHovercards' ).'</label>' , array(&$this, 'fields_html') , 'general' );
        }
        function fields_html() {
            echo '<input type="checkbox" id="twitterHovercards" name="twitterHovercards" value="1" ' . checked(1, get_option('twitterHovercards'), false) . '/>';
        }
    }
    
    // Register styles and scripts to front for the Twitter Hovercards
    function twitterHovercards_styles() {
        // Check if hovercards are active
        $hovercards = get_option('twitterHovercards');
        // If the option is ticked then we enqueue the style and script
        if($hovercards){
            // Register the styles
            wp_register_style('twitterHovercards_font', 'https://fonts.googleapis.com/css?family=Roboto:300,400');
            wp_register_style('twitterHovercards_css', plugins_url('hovercards.css', __FILE__ ));
            // Register the script
            wp_register_script('twitterHovercards_script', plugins_url('hovercards.js' , __FILE__ ));
            
            // Enqueue styles
            wp_enqueue_style('twitterHovercards_font');
            wp_enqueue_style('twitterHovercards_css');
            // Enqueue the script
            wp_enqueue_script('twitterHovercards_script');
        }
    }
    // Add styles to admin area
    add_action('wp_enqueue_scripts', 'twitterHovercards_styles');

    // This function replaces all the handles annotated with an @ with a link to profile on Twitter
    function linker_action_publish_post($post_ID) {
    
        // Get the post content
        $content_post = get_post($post_ID);
        $content = $content_post->post_content;

        // Get all the handles using the pattern
        preg_match_all('/(?<!\w)@\w+/', $content, $matches);

        foreach($matches as $match){
            foreach($match as $user) {
                // Get the array value minus the @
                $handle = substr($user, 1);
                // Check if hovercards are active
                $hovercards = get_option('twitterHovercards');
                // If the option is ticked then we add a wrapper to the link
                if($hovercards){
                    // Create the link
                    $handleOutput = "<a href='https://twitter.com/" . $handle . "' title='" . $handle . "' class='twitterHovercard' data-handle='" . $handle . "' target='_blank'>@" . $handle . "</a>";
                } else {
                    // Create the link
                    $handleOutput = "<a href='https://twitter.com/" . $handle . "' title='" . $handle . "'>@" . $handle . "</a> ";
                }
                // Replace in content
                $content = preg_replace('/@' . $handle . '/i', $handleOutput, $content);
            }
        }
        
        // Update post
        $updatedPost = array(
            'ID'           => $post_ID,
            'post_content' => $content
        );
        // Update the post into the database
        $updated = wp_update_post($updatedPost);
    }
    // Add the action on post publish
    add_action('publish_post', 'linker_action_publish_post');
?>