<?php

/**
 * A simple function that adds the actions for the ajax calls.
 * Will only work if there is no different function for admin.
 * 
 * @param string $name The name of the function
 * @return void 
 */
function add_ajax_action($name) {
	add_action("wp_ajax_{$name}", "htm_{$name}");
	add_action("wp_ajax_nopriv_{$name}", "htm_{$name}");
}

/**
 * Converts the returned SQL query into an array or string of ids.
 * 
 * @param array $a      The SQL result
 * @param string $id    The key of the id
 * @param string $type  The return type (array|string)
 * @return array|string Depending on the value of $type
 */
function list_ids($a, $id = 'ID', $type = 'array') {
	$ids = [];
	foreach ($a as $value)
		$ids[] = $value->$id;
	return $type == 'array' ? $ids : implode(',', array_map('absint', $ids));
}

function get_image_id_by_filename($name) {
	global $wpdb;

	$results = $wpdb->get_results(
		"SELECT `post_id`
		FROM `wp_postmeta`
		WHERE 
			`meta_key` LIKE '_wp_attached_file' AND
			`meta_value` LIKE '{$name}'"
	);

	if (count($results)) {
		return $results[0]->post_id;
	}
	return null;
}

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
add_ajax_action('custom_search');

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
add_ajax_action('get_posts');

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
add_ajax_action('get_categories');

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
add_ajax_action('get_featured');

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
add_ajax_action('get_trending');

/**
 * Used by How to Make - Media Editor to get the ids and quantity 
 * of the video featured images
 * 
 * @return void 
 */
function htm_get_post_content_media_category() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit('Well, that was wrong!');

	global $wpdb;

	$result = $wpdb->get_results(
		"SELECT `ID`, `post_content` 
		FROM `wp_posts`
		WHERE `post_type` LIKE 'post'"
	);

	// $break_at = 50;
	// $break_on = 0;

	$ids = [];
	foreach ($result as $post) {
		// if ($break_at === $break_on) break;
		// $break_on++;

		preg_match_all('/img [^>]+/i', $post->post_content, $matches);

		if ($matches[0]) {
			foreach ($matches[0] as $match) {

				preg_match_all('/wp-image-(\d+)/i', $match, $mID);
				if ($mID[1]) {
					$id = $mID[1];
				} else {
					preg_match_all('/src="[^"]+\/(?=\d{4})([^"]+)/i', $match, $name);
					if ($name[1][0]) {
						$iName = preg_replace('/-\d+x\d+\./i', '.', $name[1][0]);
						$id = get_image_id_by_filename($iName);
					}
				}

				$ids[] = (object) [
					'parent' => $post->ID,
					'id' => $id
				];
			}
		}
	}

	$sIDS = list_ids($ids, 'id', 'string');

	$result = $wpdb->get_results(
		"SELECT a.ID 
		FROM `wp_posts` a
		WHERE 
			a.ID IN ($sIDS) AND 
			(a.post_parent = '' OR 
			a.ID NOT IN
				(SELECT b.object_id 
				FROM `wp_terms` c, `wp_term_relationships` b
				WHERE 
					c.slug LIKE 'post-images' AND
					b.term_taxonomy_id = c.term_id))"
	);

	$nIDS = list_ids($result);
	$return = (object) [
		'ids' => []
	];
	foreach ($ids as $id) {
		if (in_array($id->id, $nIDS))
			$return->ids[] = $id;
	}

	$return->count = count($return->ids);
	echo json_encode($return);
	// echo json_encode(['success' => true]);
}
add_ajax_action('get_post_content_media_category');

/**
 * Used by How to Make - Media Editor to set the video featured 
 * images categories
 * 
 * @return void 
 */
function htm_set_post_content_media_category() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit('Well, that was wrong!');

	global $wpdb;

	$ids = $_REQUEST['data']['ids'];

	$term_id = intval($wpdb->get_results("SELECT `term_id` FROM {$wpdb->prefix}terms
		WHERE `slug` = 'post-images'")[0]->term_id);

	foreach ($ids as $v) {
		$id = intval($v['id']);
		$pID = intval($v['parent']);

		wp_update_post(array(
			'ID' => $id,
			'post_parent' => $pID
		));

		wp_set_object_terms($id, $term_id, 'attachment_category', true);
	}

	echo json_encode(['success' => true]);
}
add_ajax_action('set_post_content_media_category');

/**
 * Used by How to Make - Media Editor to get the ids and quantity 
 * of the video featured images
 * 
 * @return void 
 */
function htm_get_video_featured_media_category() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit('Well, that was wrong!');

	global $wpdb;

	$return = (object) [
		'ids' => $wpdb->get_results(
			"SELECT a.ID 
			FROM `wp_posts` a, `wp_posts` b
			WHERE 
				a.post_type LIKE 'attachment' AND 
				a.post_parent = b.ID AND
				b.post_type LIKE 'video' AND 
				a.ID NOT IN
					(SELECT d.object_id 
					FROM `wp_terms` c, `wp_term_relationships` d
					WHERE 
						c.slug LIKE 'video-featured-images' AND
						d.term_taxonomy_id = c.term_id)"
		)
	];

	$return->ids = list_ids($return->ids);
	$return->count = count($return->ids);
	echo json_encode($return);
}
add_ajax_action('get_video_featured_media_category');

/**
 * Used by How to Make - Media Editor to set the video featured 
 * images categories
 * 
 * @return void 
 */
function htm_set_video_featured_media_category() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit('Well, that was wrong!');

	global $wpdb;

	$ids = $_REQUEST['data']['ids'];

	$term_id = $wpdb->get_results("SELECT `term_id` FROM {$wpdb->prefix}terms
		WHERE `slug` = 'video-featured-images'")[0]->term_id;

	foreach ($ids as $id) {
		wp_set_object_terms($id, intval($term_id), 'attachment_category');
	}

	echo json_encode(['success' => true]);
}
add_ajax_action('set_video_featured_media_category');

/**
 * Used by How to Make - Media Editor to get the missing authors 
 * quantity
 * 
 * @return void 
 */
function htm_get_missing_authors() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit('Well, that was wrong!');

	global $wpdb;

	$results = $wpdb->get_results(
		"SELECT 
			count(*) as `count`
		FROM
			{$wpdb->prefix}posts 
		WHERE 
			`post_type` = 'attachment' AND
			`post_author` = 0"
	);

	echo json_encode($results[0]);
}
add_ajax_action('get_missing_authors');

/**
 * Used by How to Make - Media Editor to set the missing authors
 * 
 * @return void 
 */
function htm_set_missing_authors() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit('Well, that was wrong!');

	global $wpdb;

	$results = ['success' => false];

	if ($wpdb->query(
		"UPDATE
			{$wpdb->prefix}posts 
		SET
			`post_author` = 1
		WHERE 
			`post_type` = 'attachment' AND
			`post_author` = 0"
	)) {

		$results = $wpdb->get_results(
			"SELECT 
				count(*) as `count`
			FROM
				{$wpdb->prefix}posts 
			WHERE 
				`post_type` = 'attachment' AND
				`post_author` = 0"
		)[0];

		$results->success = true;
	}

	echo json_encode($results);
}
add_ajax_action('set_missing_authors');
