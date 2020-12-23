<?php

if (!defined('ROOT')) {
	define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
}

require_once ROOT . 'wp-load.php';

// require_once __DIR__ . '/shared-functions.php';

global $wpdb;

$return = [];

$results = $wpdb->get_results("SELECT `postid` AS 'post_id' FROM {$wpdb->prefix}popularpostssummary ORDER BY `view_datetime` DESC LIMIT 0, 500", OBJECT);

if (is_array($results) && count($results)) {

	$ids = array();
	foreach ($results as $result) {
		$id = $result->post_id;
		if (!$ids[$id]) $ids[$id] = 0;
		$ids[$id]++;
	}

	$a = [];
	do {
		$key = array_keys($ids, max($ids))[0];
		$a[] = $key;
		unset($ids[$key]);
	} while (count($a) < 4);
	$ids = $a;

	// $return = $ids;

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
			'link'  => parse_url(get_permalink($post->ID), PHP_URL_PATH)
		];

		$return[] = $item;
	}
}

echo json_encode($return);
