<?php

// ------------------------------------------------------------------
$limit = 50;
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../../vendor/autoload.php');

if(!defined( 'ABSPATH' )){
	require_once("../../../../../../wp-load.php");
}

require_once __DIR__ . '/../../include/functions.php';

$query = "SELECT DISTINCT p.`ID`
    FROM 
		{$wpdb->prefix}posts p
    INNER JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
	WHERE p.ID NOT IN (SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = 'video_duration_raw')
	AND p.`post_type` = 'video' 

    LIMIT $limit
    ";
        
//die($query);
$results = $wpdb->get_results($query, OBJECT);
echo  count($results) . " posts found. processing..." . $nl;


$chunks = array_chunk($results, 50,true);



foreach ($chunks as $chunk) {// Chunks of 50

    $videoIds = [];

    foreach($chunk as $post){
       
        delete_post_meta($post->ID, 'video_duration');
        delete_post_meta($post->ID, 'video_duration_h');
        delete_post_meta($post->ID, 'video_duration_m');
        delete_post_meta($post->ID, 'video_duration_s');
        
        //$duration_raw = get_post_meta($post->ID, 'video_duration_raw');
        
        //if (!isset($duration_raw[0])){
            $yt_video_id = get_post_meta($post->ID, 'youtube_video_id')[0];
            $videoIds[$yt_video_id] = $post->ID;
            echo "+";
        //}
        
    }
    echo "$nl Loading $limit videos from Youtube API... ";

    getExtraYoutubeInfo($videoIds, ['duration']);
}


echo "Done. $nl";