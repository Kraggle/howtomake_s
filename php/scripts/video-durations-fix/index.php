<?php

// ------------------------------------------------------------------

include('../../vendor/autoload.php');

if(!defined( 'ABSPATH' )){
	require_once("../../../../../../wp-load.php");
}

require_once __DIR__ . '/../../include/functions.php';

$query = "SELECT `ID`, `post_title`, `post_content`
    FROM 
		{$wpdb->prefix}posts 
	WHERE 
		`post_type` = 'video' 
	ORDER BY 
        `post_date` DESC";
        
//die($query);
$results = $wpdb->get_results($query, OBJECT);
echo count($results) . " posts found. processing..." . $nl;
foreach ($results as $post) {

    $duration = get_post_meta($post->ID, 'video_duration')[0];

    if ($duration){

        $duration = $duration * 60;

        update_post_meta($post->ID, 'video_duration', $duration);
        
        echo "+";
    }
    

}

