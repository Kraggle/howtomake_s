<?php

/**
 * Used by the search page to get results for the incoming query.
 * 
 * @return void 
 */
function htm_custom_search() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'custom_search_nonce'))
		exit('Well, that was wrong!');

	$query = $_REQUEST['query'] ?: array();
	$query['post_status'] = 'publish';

	$query = new WP_Query($query);
	if ($query->have_posts()) {
		$posts = [];

		while ($query->have_posts()) {
			$query->the_post();

			$posts[] = load_template_part('views/category/search', get_post_type());
		}

		$return = [
			"success" => true,
			"posts" => $posts,
			"count" => $query->post_count,
			"found" => $query->found_posts
		];
	} else {
		$return = [
			"success" => false
		];
	}

	echo json_encode($return);
}
add_action('wp_ajax_custom_search', 'htm_custom_search');
add_action('wp_ajax_nopriv_custom_search', 'htm_custom_search');

/**
 * Used by the main menu to get posts and videos
 * 
 * @return void 
 */
function htm_get_posts() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'main_menu_nonce'))
		exit('Well, that was wrong!');

	global $wpdb;

	$return = (object) [
		"count" => null,
		"items" => []
	];

	$id = $_REQUEST['termId'];
	$results = $wpdb->get_results("SELECT `object_id` FROM {$wpdb->prefix}term_relationships WHERE `term_taxonomy_id` = $id");

	$ids = [];
	foreach ($results as $value)
		$ids[] = $value->object_id;
	$ids = implode(',', array_map('absint', $ids));

	$query = "FROM 
		{$wpdb->prefix}posts 
	WHERE 
		`ID` IN ($ids) AND 
		`post_type` = '{$_REQUEST['type']}' AND
		`post_status` = 'publish'
	ORDER BY 
		`post_date` DESC";

	if (filter_var($_REQUEST['getCount'], FILTER_VALIDATE_BOOLEAN))
		$return->count = $wpdb->get_results("SELECT COUNT(*) AS `count` $query", OBJECT)[0]->count;

	$page = $_REQUEST['page'];
	$results = $wpdb->get_results("SELECT `ID`, `post_title`, `post_content` $query LIMIT $page, 3", OBJECT);

	foreach ($results as $post) {

		$type = $_REQUEST['type'] === 'video';

		$item = (object) [
			'id'    => $post->ID,
			'title' => $post->post_title,
			'readTime' => $type ? get_duration($post->ID) : get_read_time($post->ID),
			'image' => parse_url(get_the_post_thumbnail_url($post->ID, 'medium'), PHP_URL_PATH),
			'link'  => parse_url(get_permalink($post->ID), PHP_URL_PATH)
		];

		$return->items[] = $item;
	}

	echo json_encode($return);
}
add_action('wp_ajax_get_posts', 'htm_get_posts');
add_action('wp_ajax_nopriv_get_posts', 'htm_get_posts');

/**
 * Used by the main menu to get the categories and channels
 * 
 * @return void 
 */
function htm_get_categories() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'main_menu_nonce'))
		exit('Well, that was wrong!');

	$args = array(
		'taxonomy' => $_REQUEST['taxonomy'],
		'orderby' => 'name',
		'order' => 'ASC'
	);

	$return = get_terms($args);

	foreach ($return as $cat) {
		$cat->link = parse_url(get_category_link($cat->term_id), PHP_URL_PATH);
		$cat->image = parse_url(get_channel_logo($cat->term_id), PHP_URL_PATH);
	}

	echo json_encode($return);
}
add_action('wp_ajax_get_categories', 'htm_get_categories');
add_action('wp_ajax_nopriv_get_categories', 'htm_get_categories');

/**
 * Used by the main menu to get the featured posts
 * 
 * @return void 
 */
function htm_get_featured() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'main_menu_nonce'))
		exit('Well, that was wrong!');

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
				'readTime' => get_read_time($post->ID),
				'image' => parse_url(get_the_post_thumbnail_url($post->ID, 'medium'), PHP_URL_PATH),
				'link'  => parse_url(get_permalink($post->ID), PHP_URL_PATH)
			];

			$return[] = $item;
		}
	}

	echo json_encode($return);
}
add_action('wp_ajax_get_featured', 'htm_get_featured');
add_action('wp_ajax_nopriv_get_featured', 'htm_get_featured');

/**
 * Used by the main menu to get the trending posts
 * 
 * @return void 
 */
function htm_get_trending() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'main_menu_nonce'))
		exit('Well, that was wrong!');


	$return = [];

	global $wpdb;

	$results = $wpdb->get_results("SELECT `postid` AS 'post_id' FROM {$wpdb->prefix}popularpostssummary ORDER BY `view_datetime` DESC LIMIT 0, 1000", OBJECT);

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
}
add_action('wp_ajax_get_trending', 'htm_get_trending');
add_action('wp_ajax_nopriv_get_trending', 'htm_get_trending');
