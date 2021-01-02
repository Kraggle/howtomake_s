<?php

if (!defined('ROOT')) {
	define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
}

require_once ROOT . 'wp-load.php';

require_once __DIR__ . '/shared-functions.php';

global $wpdb;

$return = (object) [
	"count" => null,
	"items" => []
];

$id = $_GET['termId'];
$results = $wpdb->get_results("SELECT `object_id` FROM {$wpdb->prefix}term_relationships WHERE `term_taxonomy_id` = $id");

$ids = [];
foreach ($results as $value)
	$ids[] = $value->object_id;
$ids = implode(',', array_map('absint', $ids));

$query = "FROM 
		{$wpdb->prefix}posts 
	WHERE 
		`ID` IN ($ids) AND 
		`post_type` = '{$_GET['type']}' AND
		`post_status` = 'publish'
	ORDER BY 
		`post_date` DESC";

if (filter_var($_GET['getCount'], FILTER_VALIDATE_BOOLEAN))
	$return->count = $wpdb->get_results("SELECT COUNT(*) AS `count` $query", OBJECT)[0]->count;

$page = $_GET['page'];
$results = $wpdb->get_results("SELECT `ID`, `post_title`, `post_content` $query LIMIT $page, 3", OBJECT);

foreach ($results as $post) {

	$type = $_GET['type'] === 'video';

	if ($type) {
		$duration = get_post_meta($post->ID, 'video_duration')[0];

		if (!$duration) {
			$id = get_post_meta($post->ID, 'youtube_video_id')[0];

			$interval = get_duration($id);
			$seconds = ceil(($interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s));
			if ($seconds){
				add_post_meta($post->ID, 'video_duration', $seconds);

				add_post_meta($id, 'video_duration_h', $interval->h);// hour portion
				add_post_meta($id, 'video_duration_m', $interval->i);// minutes portion
				add_post_meta($id, 'video_duration_s', $interval->s);// seconds portion
			}else{
				$duration = 0;
			}
		}
	}

	$item = (object) [
		'id'    => $post->ID,
		'title' => $post->post_title,
		'readTime' => $type ? $duration : read_time($post->post_content),
		'image' => parse_url(get_the_post_thumbnail_url($post->ID, 'medium'), PHP_URL_PATH),
		'link'  => parse_url(get_permalink($post->ID), PHP_URL_PATH)
	];

	$return->items[] = $item;
}

echo json_encode($return);
