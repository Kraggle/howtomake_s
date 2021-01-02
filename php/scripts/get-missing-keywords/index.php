<?php

$processAmount = 100;

// ------------------------------------------------------------------

include('../../vendor/autoload.php');

if(!defined( 'ABSPATH' )){
	require_once("../../../../../../wp-load.php");
}

require_once __DIR__ . '/../../include/functions.php';


// Google API init
$client = new Google_Client();
$client->setApplicationName($appName);
$client->setDeveloperKey($apiKey);





$query = "SELECT `ID`, `post_title`, `post_content`
    FROM 
		{$wpdb->prefix}posts 
        
	WHERE 
		`post_type` = 'video' 
        AND ID NOT IN (SELECT object_id FROM {$wpdb->prefix}term_relationships tr INNER JOIN {$wpdb->prefix}term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = 'post_tag' )
	ORDER BY 
        `post_date` DESC
    LIMIT $processAmount";
        
    
//die($query);
$results = $wpdb->get_results($query, OBJECT);
echo "Processing " . count($results) . " videos..." . $nl;

$ids = [];
foreach ($results as $post) {

    $tags = wp_get_post_tags( $post->ID);

    if(!$tags || (is_array($tags) && !count($tags) )){
        $yt_video_id = get_post_meta($post->ID, 'youtube_video_id')[0];
        $ids[$yt_video_id] = $post->ID;
        //print_r([$yt_video_id => $post->ID]);
        echo $yt_video_id . " -> " . $post->ID . $nl;
    }
}
//var_dump($ids);exit;
getExtraYoutubeInfo($ids, getYoutubeService());
