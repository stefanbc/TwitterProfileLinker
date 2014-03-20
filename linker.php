<?
    /*
        Plugin Name: Twitter Profile Linker
        Plugin URI: https://github.com/stefanbc/TwitterProfileLinker
        Description: Automatically generate links, to Twitter profiles, when a WordPress post is published or updated.
        Version: 0.6
        Author: Stefan Cosma
        Author URI: http://coderbits.com/stefanbc
        License: GPLv2 or later
        License URI: http://www.gnu.org/licenses/gpl-2.0.html
    */
    
    // Create submenu page in the WordPress Settings menu
    function profile_linker() {
        add_submenu_page('options-general.php', 'Twitter Profile Linker', 'Twitter Profile Linker', 'edit_posts', 'profile_linker', 'profile_linker_options');
    }
    add_action('admin_menu','profile_linker');
    
    // Add settings link to plugin on plugins list
    function profile_linker_add_settings_link($links) {
        $settings_link = '<a href="options-general.php?page=profile_linker">Settings</a>';
        array_push($links, $settings_link);
        return $links;
    }
    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_$plugin", 'profile_linker_add_settings_link');
    
    // Settings page
    function profile_linker_options() {
        
        // Add the needed options to the wp_options table
        add_option('twitterHovercards', $twitterHovercards);
        add_option('twitterHovercards_oAuthAccessToken', $twitterHovercards_oAuthAccessToken);
        add_option('twitterHovercards_oAuthAccessTokenSecret', $twitterHovercards_oAuthAccessTokenSecret);
        add_option('twitterHovercards_consumerKey', $twitterHovercards_consumerKey);
        add_option('twitterHovercards_consumerKeySecret', $twitterHovercards_consumerKeySecret);
        
        echo '<label for="twitterHovercards">'.__('Enable Twitter Hovercards?' , 'twitterHovercards' ).'</label>';
        echo '<input type="checkbox" id="twitterHovercards" name="twitterHovercards" value="1" ' . checked(1, get_option('twitterHovercards'), false) . '/>';
    
        echo '<label for="twitterHovercards_oAuthAccessToken">'.__('Twitter oAuth Access Token' , 'twitterHovercards' ).'</label>';
        echo '<input type="text" id="twitterHovercards_oAuthAccessToken" name="twitterHovercards_oAuthAccessToken" value="' . get_option('twitterHovercards_oAuthAccessToken') . '"/>';
        
        echo '<label for="twitterHovercards_oAuthAccessTokenSecret">'.__('Twitter oAuth Access Token Secret' , 'twitterHovercards' ).'</label>';
        echo '<input type="text" id="twitterHovercards_oAuthAccessTokenSecret" name="twitterHovercards_oAuthAccessTokenSecret" value="' . get_option('twitterHovercards_oAuthAccessTokenSecret') . '"/>';
        
        echo '<label for="twitterHovercards_consumerKey">'.__('Twitter Consumer Key' , 'twitterHovercards' ).'</label>';
        echo '<input type="text" id="twitterHovercards_consumerKey" name="twitterHovercards_consumerKey" value="' . get_option('twitterHovercards_consumerKey') . '"/>';
        
        echo '<label for="twitterHovercards_consumerKeySecret">'.__('Twitter Consumer Key Secret' , 'twitterHovercards' ).'</label>';
        echo '<input type="text" id="twitterHovercards_consumerKeySecret" name="twitterHovercards_consumerKeySecret" value="' . get_option('twitterHovercards_consumerKeySecret') . '"/>';
        
    }
    
    // Register styles and scripts to front for the Twitter Hovercards
    function twitterHovercards_styles() {
        // Check if hovercards are active
        $hovercards = get_option('twitterHovercards');
        // If the option is ticked then we enqueue the style and script
        if($hovercards){
            // Register the styles
            wp_register_style('twitterHovercards_css', plugins_url('hovercards.css', __FILE__ ));
            // Register the script
            wp_register_script('twitterHovercards_script', plugins_url('hovercards.js' , __FILE__ ), array('jquery'));
            
            // Enqueue styles
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
        
        // First we check if we already applied the links
        $checkLinker = preg_match('/linkerElementWrapper/', $content);
        
        if (!$checkLinker) {
            // Get all the handles using the pattern
            preg_match_all('/(?<!\w)@\w+/', $content, $matches);
    
            foreach($matches as $match){
                foreach($match as $user) {
                    // Get the array value minus the @
                    $handle = substr($user, 1);
                    // Check if hovercards are active
                    $hovercards = get_option('twitterHovercards');
                    // If the option is ticked then we add a class and data from js to the link
                    $hovercardsOutput = ($hovercards) ? "class='twitterHovercard linkerElement' data-handle='" . $handle . "'" : "class='linkerElement'";
                    // Create the link
                    $handleOutput = "<span class='linkerElementWrapper'><a href='https://twitter.com/" . $handle . "' " . $hovercardsOutput . " title='" . $handle . "' target='_blank'>@" . $handle . "</a></span>";
                    // Replace in content
                    $content = preg_replace('/@' . $handle . '/i', $handleOutput, $content);
                }
            }
            
            if (!wp_is_post_revision($post_ID)){
                // Unhook this function so it doesn't loop infinitely
                remove_action('publish_post', 'linker_action_publish_post');
                // Update post
                $updatedPost = array(
                    'ID'           => $post_ID,
                    'post_content' => $content
                );
                // Update the post
                wp_update_post($updatedPost);
                // Re-hook this function
                add_action('publish_post', 'linker_action_publish_post');
            }
        }
    }
    // Add the action on post publish
    add_action('publish_post', 'linker_action_publish_post');
?>