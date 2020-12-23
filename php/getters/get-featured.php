<?php

if (!defined('ROOT')) {
	define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
}

require_once ROOT . 'wp-load.php';

require_once __DIR__ . '/shared-functions.php';

global $wpdb;

$return = [];

$results = $wpdb->get_results("SELECT `post_id` FROM {$wpdb->prefix}postmeta WHERE `meta_key` = 'featured_post' AND `meta_value` = 1", OBJECT);

if (is_array($results) && count($results)) {

	$ids = [];
	foreach ($results as $result)
		$ids[] = $result->post_id;
	$ids = implode(',', array_map('absint', $ids));

	$query = "FROM 
			{$wpdb->prefix}posts 
		WHERE 
			`ID` IN ($ids) AND 
			`post_type` = 'post' AND
			`post_status` = 'publish'
		ORDER BY 
			RAND()
		LIMIT 0, 4";

	$results = $wpdb->get_results("SELECT `ID`, `post_title`, `post_content` $query", OBJECT);

	foreach ($results as $post) {

		$item = (object) [
			'id'    => $post->ID,
			'title' => $post->post_title,
			'readTime' => read_time($post->post_content),
			'image' => parse_url(get_the_post_thumbnail_url($post->ID, 'medium'), PHP_URL_PATH),
			'link'  => parse_url(get_permalink($post->ID), PHP_URL_PATH)
		];

		$return[] = $item;
	}
}

echo json_encode($return);
