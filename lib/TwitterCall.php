<?php
    require_once('TwitterAPI.php');
    
    // Set access tokens here - see: https://dev.twitter.com/apps/
    $settings = array(
        'oauth_access_token' => "",
        'oauth_access_token_secret' => "",
        'consumer_key' => "",
        'consumer_secret' => ""
    );
    
    // Perform a GET request and echo the response
    // Note: Set the GET field BEFORE calling buildOauth();
    $url = 'https://api.twitter.com/1/users/lookup.json';
    $getfield = '?screen_name=';
    $requestMethod = 'GET';
    
    $twitter = new TwitterAPIExchange($settings);
    
    echo $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();
    
?>