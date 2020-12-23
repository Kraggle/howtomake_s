<?php
if (!defined('ROOT')) {
	define('ROOT', $_SERVER['DOCUMENT_ROOT'] . '/');
}

require_once ROOT . 'wp-load.php';

require_once __DIR__ . '/shared-functions.php';

$args = array(
	'taxonomy' => $_GET['taxonomy'],
	'orderby' => 'name',
	'order' => 'ASC'
);

$return = get_terms($args);

foreach ($return as $cat) {
	$cat->link = parse_url(get_category_link($cat->term_id), PHP_URL_PATH);

	// get the logo from the database
	$imageId = get_term_meta($cat->term_id, 'actual_logo', true);
	// if it's not in the database, get it from youtube
	if (!$imageId) $imageId = get_channel_logo(get_term_meta($cat->term_id, 'yt_channel_id', true), $cat->term_id);

	$cat->image = parse_url(wp_get_attachment_image_url($imageId, 'medium'), PHP_URL_PATH);
}

echo json_encode($return);
