<?php

$outputLog = outputLogger::getInstance(get_template_directory() . '/logs/ajax-logs.txt');

define('FAILED_NONCE', json_encode([
	'success' => false,
	'message' => 'nonce missmatch'
]));

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
 * Used by the search page to get results for the incoming query.
 * 
 * @return void 
 */
function htm_custom_search() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'custom_search_nonce'))
		exit(FAILED_NONCE);

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
	exit;
}
add_ajax_action('custom_search');

/**
 * Used by the main menu to get posts and videos
 * 
 * @return void 
 */
function htm_get_posts() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'main_menu_nonce'))
		exit(FAILED_NONCE);

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
			'image' => parse_url(get_the_post_thumbnail_url($post->ID, 'menu'), PHP_URL_PATH),
			'link'  => parse_url(get_permalink($post->ID), PHP_URL_PATH)
		];

		$return->items[] = $item;
	}

	echo json_encode($return);
	exit;
}
add_ajax_action('get_posts');

/**
 * Used by the main menu to get the categories and channels
 * 
 * @return void 
 */
function htm_get_categories() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'main_menu_nonce'))
		exit(FAILED_NONCE);

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
	exit;
}
add_ajax_action('get_categories');

/**
 * Used by the main menu to get the featured posts
 * 
 * @return void 
 */
function htm_get_featured() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'main_menu_nonce'))
		exit(FAILED_NONCE);

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
				'image' => parse_url(get_the_post_thumbnail_url($post->ID, 'menu'), PHP_URL_PATH),
				'link'  => parse_url(get_permalink($post->ID), PHP_URL_PATH)
			];

			$return[] = $item;
		}
	}

	echo json_encode($return);
	exit;
}
add_ajax_action('get_featured');

/**
 * Used by the main menu to get the trending posts
 * 
 * @return void 
 */
function htm_get_trending() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'main_menu_nonce'))
		exit(FAILED_NONCE);


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
	exit;
}
add_ajax_action('get_trending');

/**
 * Used by How to Make - Media Editor to get the ids and quantity 
 * of the all media that does not have a category.
 * 
 * @return void 
 */
function htm_get_missing_category() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	global $wpdb;

	$return = (object) [
		'ids' => []
	];

	// Get the content images 
	// ↓ ------------------ ↓
	$contents = $wpdb->get_results(
		"SELECT `ID`, `post_content` 
		FROM `wp_posts`
		WHERE `post_type` IN ('post', 'page', 'video')"
	);

	// $break_at = 50;
	// $break_on = 0;

	$ids = [];
	foreach ($contents as $post) {
		// if ($break_at === $break_on) break;
		// $break_on++;

		preg_match_all('/img [^>]+/i', $post->post_content, $matches);

		if ($matches[0]) {
			foreach ($matches[0] as $match) {

				preg_match_all('/wp-image-(\d+)/i', $match, $mID);
				if ($mID[1][0]) {
					$id = $mID[1][0];
				} else {
					preg_match_all('/src="[^"]+\/(?=\d{4})([^"]+)/i', $match, $name);
					if ($name[1][0]) {
						$iName = preg_replace('/-\d+x\d+\./i', '.', $name[1][0]);
						$id = get_image_id_by_filename($iName);
					}
				}

				$ids[] = (object) [
					'type' => ['content-images'],
					'parent' => $post->ID,
					'id' => $id
				];
			}
		}
	}

	$sIDS = list_ids($ids, 'id', 'string');
	$contentIds = $wpdb->get_results(
		"SELECT a.ID 
		FROM `wp_posts` a
		WHERE 
			a.ID IN ($sIDS) AND 
			(a.post_parent = '' OR 
			a.ID NOT IN
				(SELECT b.object_id 
				FROM `wp_terms` c, `wp_term_relationships` b
				WHERE 
					c.slug LIKE 'content-images' AND
					b.term_taxonomy_id = c.term_id))"
	);

	$nIDS = list_ids($contentIds);
	foreach ($ids as $id) {
		if (in_array($id->id, $nIDS))
			$return->ids[] = $id;
	}
	// ↑ ------------------ ↑
	// Get the content images 

	// Get featured post images 
	// ↓ -------------------- ↓
	$featuredIds = $wpdb->get_results(
		"SELECT a.ID, b.ID AS 'parent'
		FROM `wp_posts` a, `wp_posts` b, `wp_postmeta` c
		WHERE 
			b.post_type LIKE 'post' AND 
			c.post_id = b.ID AND
			c.meta_key LIKE '_thumbnail_id' AND
			a.ID = c.meta_value AND (
				a.post_parent = '' OR
				a.ID NOT IN (
					SELECT d.object_id 
					FROM `wp_terms` e, `wp_term_relationships` d
					WHERE 
						e.slug LIKE 'post-featured-images' AND
						d.term_taxonomy_id = e.term_id
				)
			)"
	);

	foreach ($featuredIds as $id) {
		$i = array_search($id->ID, array_column($return->ids, 'id'));
		if ($i) {
			$return->ids[$i]->type[] = 'post-featured-images';
			$return->ids[$i]->parent = $id->parent;
		} else {
			$return->ids[] = (object) [
				'type' => ['post-featured-images'],
				'parent' => $id->parent,
				'id' => $id->ID
			];
		}
	}
	// ↑ -------------------- ↑
	// Get featured post images 

	// Get featured video images 
	// ↓ --------------------- ↓
	$videoIds = $wpdb->get_results(
		"SELECT a.ID, b.ID AS 'parent'
		FROM `wp_posts` a, `wp_posts` b, `wp_postmeta` c
		WHERE 
			b.post_type LIKE 'video' AND 
			c.post_id = b.ID AND
			c.meta_key LIKE '_thumbnail_id' AND
			a.ID = c.meta_value AND (
				a.post_parent = '' OR
				a.ID NOT IN (
					SELECT d.object_id 
					FROM `wp_terms` e, `wp_term_relationships` d
					WHERE 
						e.slug LIKE 'video-featured-images' AND
						d.term_taxonomy_id = e.term_id
				)
			)"
	);

	foreach ($videoIds as $id) {
		$i = array_search($id->ID, array_column($return->ids, 'id'));
		if ($i) {
			$return->ids[$i]->type[] = 'video-featured-images';
			$return->ids[$i]->parent = $id->parent;
		} else {
			$return->ids[] = (object) [
				'type' => ['video-featured-images'],
				'parent' => $id->parent,
				'id' => $id->ID
			];
		}
	}
	// ↑ --------------------- ↑
	// Get featured video images 

	// Get channel images 
	// ↓ -------------- ↓
	$channelIds = $wpdb->get_results(
		"SELECT b.ID
		FROM `wp_term_taxonomy` a, `wp_posts` b, `wp_termmeta` c
		WHERE 
			c.meta_value = b.ID AND
			c.meta_key LIKE 'actual_logo' AND
			a.term_id = c.term_id AND 
			b.ID NOT IN (
				SELECT d.object_id 
				FROM `wp_terms` e, `wp_term_relationships` d
				WHERE 
					e.slug LIKE 'video-channel-icons' AND
					d.term_taxonomy_id = e.term_id
			)"
	);

	foreach ($channelIds as $id) {
		$i = array_search($id->ID, array_column($return->ids, 'id'));
		if ($i) {
			$return->ids[$i]->type[] = 'video-channel-icons';
		} else {
			$return->ids[] = (object) [
				'type' => ['video-channel-icons'],
				'id' => $id->ID
			];
		}
	}
	// ↑ -------------- ↑
	// Get channel images 

	// Get unused images 
	// ↓ -------------- ↓
	$unusedIds = $wpdb->get_results(
		"SELECT a.ID
		FROM `wp_posts` a
		WHERE 
			a.post_type LIKE 'attachment' AND
			# Featured Images
			a.ID NOT IN (
				SELECT b.ID
				FROM `wp_posts` b, `wp_posts` c, `wp_postmeta` d
				WHERE (
						c.post_type LIKE 'post' OR 
						c.post_type LIKE 'video'
					) AND 
					d.post_id = c.ID AND
					d.meta_key LIKE '_thumbnail_id' AND
					b.ID = d.meta_value
			) AND 
			# Channel Images
			a.ID NOT IN (
				SELECT e.ID
				FROM `wp_posts` e, `wp_term_taxonomy` f, `wp_termmeta` g
				WHERE 
					g.meta_value = e.ID AND
					g.meta_key LIKE 'actual_logo' AND
					f.term_id = g.term_id
			) AND 
			# Content Images
			a.ID NOT IN ($sIDS) AND 
			# CSS Images
			a.ID NOT IN (
				SELECT i.object_id 
				FROM `wp_terms` h, `wp_term_relationships` i
				WHERE 
					h.slug LIKE 'css-images' AND
					i.term_taxonomy_id = h.term_id
			) AND 
			# Already set as unused
			a.ID NOT IN (
				SELECT k.object_id 
				FROM `wp_terms` j, `wp_term_relationships` k
				WHERE 
					j.slug LIKE 'unused-images' AND
					k.term_taxonomy_id = j.term_id
			)"
	);

	foreach ($unusedIds as $id) {
		$i = array_search($id->ID, array_column($return->ids, 'id'));
		if ($i) {
			logger("THIS IS AN ERROR! It shouldn't be in any existing arrays.");
		} else {
			$return->ids[] = (object) [
				'type' => ['unused-images'],
				'id' => $id->ID
			];
		}
	}
	// ↑ -------------- ↑
	// Get unused images 

	$return->count = count($return->ids);
	echo json_encode($return);
	exit;
	// echo json_encode(['success' => true]);
}
add_ajax_action('get_missing_category');

/**
 * Used by How to Make - Media Editor to set images categories
 * 
 * @return void 
 */
function htm_set_missing_category() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	global $wpdb;

	$ids = $_REQUEST['data']['ids'];

	$aTerms = $wpdb->get_results(
		"SELECT a.term_id, b.slug
		FROM `wp_term_taxonomy` a, `wp_terms` b
		WHERE 
			a.taxonomy LIKE 'attachment_category' AND
			a.term_id = b.term_id"
	);

	$terms = (object) [];
	foreach ($aTerms as $term) {
		$slug = $term->slug;
		$terms->$slug = intval($term->term_id);
	}

	global $refreshing_categories;
	$refreshing_categories = true;

	foreach ($ids as $v) {
		$id = intval($v['id']);
		$pID = isset($v['parent']) ? intval($v['parent']) : null;

		if ($pID) {
			wp_update_post(array(
				'ID' => $id,
				'post_parent' => $pID
			));
		}

		foreach ($v['type'] as $type) {
			wp_set_object_terms($id, $terms->$type, 'attachment_category', $type == 'unused-images' ? false : true);
		}
	}

	$refreshing_categories = false;

	echo json_encode(['success' => true]);
	exit;
}
add_ajax_action('set_missing_category');

/**
 * Used by How to Make - Media Editor to get the ids and quantity 
 * of the media that is unused for deletion.
 * 
 * @return void 
 */
function htm_get_media_to_delete() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	global $wpdb;

	$ids = $wpdb->get_results(
		"SELECT a.ID, c.slug
		FROM `wp_posts` a, `wp_terms` c, `wp_term_relationships` d
		WHERE 
			c.slug LIKE 'unused-images' AND
			d.term_taxonomy_id = c.term_id AND
			d.object_id = a.ID"
	);

	$return = (object) [
		'ids' => list_ids($ids)
	];
	$return->count = count($return->ids);
	echo json_encode($return);
	exit;
}
add_ajax_action('get_media_to_delete');

/**
 * Used by How to Make - Media Editor to delete unused media
 * 
 * @return void 
 */
function htm_set_media_to_delete() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$ids = $_REQUEST['data']['ids'];

	foreach ($ids as $id) {
		wp_delete_attachment(intval($id), true);
	}

	echo json_encode(['success' => true]);
	exit;
}
add_ajax_action('set_media_to_delete');

/**
 * Used by How to Make - Media Editor to get the missing authors 
 * quantity
 * 
 * @return void 
 */
function htm_get_missing_authors() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

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
	exit;
}
add_ajax_action('get_missing_authors');

/**
 * Used by How to Make - Media Editor to set the missing authors
 * 
 * @return void 
 */
function htm_set_missing_authors() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

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
	exit;
}
add_ajax_action('set_missing_authors');

/**
 * Used by How to Make - Media Editor to set images categories
 * 
 * @return void 
 */
function htm_get_regenerate_thumbnails() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	global $wpdb;

	$aTerms = $wpdb->get_results(
		"SELECT a.term_id, b.slug
		FROM `wp_term_taxonomy` a, `wp_terms` b
		WHERE 
			a.taxonomy LIKE 'attachment_category' AND
			a.term_id = b.term_id"
	);

	$termIds = list_ids($aTerms, 'term_id', 'string');

	$data = $wpdb->get_results(
		"SELECT p.ID AS 'id', pm.meta_value AS 'meta', GROUP_CONCAT(t.slug SEPARATOR '|') AS 'category'
		FROM `wp_posts` AS p, `wp_postmeta` AS pm, `wp_term_relationships` AS tr, `wp_terms` AS t
		WHERE 
			p.post_type = 'attachment' AND
			pm.post_id = p.ID AND
			pm.meta_key = '_wp_attachment_metadata' AND
			tr.object_id = p.ID AND
			tr.term_taxonomy_id IN ($termIds) AND 
			t.term_id = tr.term_taxonomy_id
		GROUP BY p.ID"
	);

	$upload_dir = wp_upload_dir()['basedir'] . '/';

	$path_cache = (object) [];

	$do_max = 5;
	$do_now = 0;

	$ids = [];
	foreach ($data as $img) {
		if (IS_DEBUG && $do_max === $do_now)
			break;

		$do = false;
		$meta = to_object(maybe_unserialize($img->meta));

		if (empty($meta)) return;

		$sizes = get_image_sizes_for_category(explode('|', $img->category), $meta->width, $meta->height);
		$aSizes = [];
		foreach ($sizes as $size)
			$aSizes[] = $size;

		$msg = '';
		$aMsg = (object) [
			'dimensions' => [
				'original_width' => $oW = $meta->width,
				'original_height' => $oH = $meta->height
			]
		];

		$mFiles = [];
		foreach ($meta->sizes as $size)
			$mFiles[] = $size->file;

		// Checks if the meta data matches the required sizes in length 
		if (length($sizes) != length($mFiles)) {
			$do = true;
			$msg = 'meta counts m|' . length($meta->sizes) . ':s|' . length($sizes);
			$aMsg->META = [$meta->sizes, $sizes];
		} else {

			// Checks if the meta data and required sizes are a match
			foreach ($sizes as $key => $size) {
				$width = preg_number_range($size['width']);
				$height = $size['height'] ? preg_number_range($size['height']) : '\d+';
				if (!length(preg_grep("/-{$size['width']}x{$height}/", $mFiles))) {

					if ($size['crop'] && $oW < $size['width'] && !$meta->sizes->$key) {
						$do = true;
						$msg = 'meta sizes missing';
						$aMsg->META = [$meta->sizes, $sizes, "{$width}x{$height}"];
						break;
					}
				}
			}
		}

		// Checks the files in the directory with what is required
		if (!$do && $file = realpath($upload_dir . $meta->file)) {
			$info = pathinfo($file);
			$path = $info['dirname'];

			if (!$path_cache->$path)
				$path_cache->$path = to_array(array_diff(scandir($path), ['.', '..']));

			$name = $info['filename'] . '-';
			$files = preg_grep("/^$name\d+x\d+\.\w+/i", $path_cache->$path);

			// Checks if the files match the sizes in required sizes
			foreach ($meta->sizes as $size) {
				if (!length(preg_grep("/-{$size->width}x{$size->height}/", $files))) {
					$do = true;
					$msg = 'file missing';
					$aMsg->FILES = [$meta->sizes, $sizes, $files];
					break;
				}
			}

			// Checks if the length of the files to required files is correct
			if (!$do) {
				foreach ($files as $file) {
					preg_match('/-(\d+)x(\d+)\.\w+$/', $file, $dims);
					$w = $dims[1];
					$h = $dims[2];

					$key = array_search($w, array_column($aSizes, 'width'));
					if (!($key >= 0)) {
						$do = true;
						break;
					}

					$sK = $aSizes[$key];
					if ($sK['crop']) {
						if ($sK['height'] != $h) {
							$do = true;
							break;
						}
					} else {
						if ($sK['height'] != 0 && $sK['height'] != $h) {
							$do = true;
							break;
						}
					}
				}

				if ($do) {
					$msg = 'extra files f|' . length($files) . ':s|' . length($sizes);
					$aMsg->FILES = [$meta->sizes, $sizes, $files];
				}

				// return;
			}
		}

		// Checks if the size names are a match with required sizes
		if (!$do) {
			$aSize = [];
			foreach ($sizes as $key => $value)
				$aSize[] = $key;

			foreach ($meta->sizes as $key => $value) {
				if (!in_array($key, $aSize)) {
					$do = true;
					$msg = 'missing size names';
					$aMsg->MISSING = [$meta->sizes, $sizes];
					break;
				}
			}
		}

		if ($do) {
			$do_now++;
			$ids[] = $img->id;
			// logger(
			// 	"$img->id of category $img->category because of $msg",
			// 	$aMsg
			// );
		}
	}

	$return = (object) [
		'ids' => $ids
	];
	$return->count = count($return->ids);
	echo json_encode($return);
	exit;
}
add_ajax_action('get_regenerate_thumbnails');

/**
 * Used by How to Make - Media Editor to set images categories
 * 
 * @return void 
 */
function htm_set_regenerate_thumbnails() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);


	$ids = $_REQUEST['data']['ids'];

	foreach ($ids as $id) {
		generate_category_thumbnails(intval($id));
	}

	echo json_encode(['success' => true]);
	exit;
}
add_ajax_action('set_regenerate_thumbnails');

/**
 * Used by How to Make - Page Settings to change sitemap settings
 * 
 * @return void 
 */
function htm_set_sitemap_settings() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$inputs = $_REQUEST['data']['inputs'];

	foreach ($inputs as $key => $value)
		update_option("htm_sitemap_$key", $value == 'true' ? 1 : 0);

	$post_types = get_post_types(array('public' => true), 'names', 'and');

	$types = [];
	foreach ($post_types as $post_type) {
		// logger("htm_sitemap_include_{$post_type}");
		if ($post_type == 'attachment' || !get_option("htm_sitemap_include_{$post_type}")) continue;
		$types[] = $post_type;
	}
	$types = "'" . implode("','", $types) . "'";

	global $wpdb;
	$ids = list_ids($wpdb->get_results(
		"SELECT `ID` 
		FROM `wp_posts`
		WHERE 
			`post_type` IN ($types) AND
			`ID` NOT IN (
				SELECT `post_id`
				FROM `wp_postmeta`
				WHERE `meta_key` LIKE 'htm_permalink'
			)"
	));

	echo json_encode([
		'success' => true,
		'ids' => $ids,
		'action' => 'set_permalinks',
		'loop' => 5
	]);
	exit;
}
add_ajax_action('set_sitemap_settings');

/**
 * Used by How to Make - Page Settings to change sitemap settings
 * 
 * @return void 
 */
function htm_set_permalinks() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$ids = $_REQUEST['data']['ids'];

	foreach ($ids as $id) {
		$link = get_permalink($id);
		$post = get_post($id);
		htm_set_permalink($id, $link, $post);
	}

	echo json_encode(['success' => true]);
	exit;
}
add_ajax_action('set_permalinks');

/**
 * Used by How to Make - Media Editor to get the video thumbnails that need regenerating
 * 
 * @return void 
 */
function htm_get_video_thumbnails() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	global $wpdb;

	$posts = $wpdb->get_results(
		"SELECT p.ID, pm.meta_value AS yt_id
		FROM `wp_posts` As p, `wp_postmeta` AS pm 
		WHERE 
			p.post_type LIKE 'video' AND
			p.ID = pm.post_id AND
			pm.meta_key LIKE 'youtube_video_id' AND
			p.ID NOT IN (
				SELECT `post_id`
				FROM `wp_postmeta`
				WHERE `meta_key` LIKE 'htm_youtube_refreshed'
			)"
	);

	$json_file = wp_upload_dir()['basedir'] . '/yt-meta.json';
	$yt_meta = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

	$nu_meta = [];
	$ids = list_ids($posts);
	foreach ($yt_meta as $key => $value)
		if (in_array($key, $ids))
			$nu_meta[$key] = $value;
	$yt_meta = $nu_meta;

	$get = [];
	$got = [];
	foreach ($posts as $post) {
		if ($yt_meta[$post->ID])
			$got[] = $post;
		else
			$get[] = $post;
	}

	$blocks = array_chunk($get, 50);
	foreach ($blocks as $block) {
		$yt_ids = list_ids($block, 'yt_id');

		$items = get_video_info_for_ids($yt_ids)->items;

		foreach ($block as $post) {
			$i = array_search($post->yt_id, array_column($items, 'id'));
			if (is_numeric($i)) {
				$item = $items[$i];
				$yt_meta[$post->ID] = [
					'id' => $item->id,
					'snippet' => [
						'thumbnails' => $item->snippet->thumbnails,
						'title' => $item->snippet->title,
					]
				];
			} else $yt_meta[$post->ID] = [
				'delete' => $post->ID
			];
		}
	}

	file_put_contents($json_file, json_encode($yt_meta));

	$ids = array_keys($yt_meta);
	$return = (object) [
		'ids' => $ids
	];
	$return->count = count($ids);
	echo json_encode($return);
	exit;
}
add_ajax_action('get_video_thumbnails');

/**
 * Used by How to Make - Media Editor to regenerate the video images from youtube data
 * 
 * @return void 
 */
function htm_set_video_thumbnails() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$return = (object) ['message' => []];
	$ids = $_REQUEST['data']['ids'];

	$json_file = wp_upload_dir()['basedir'] . '/yt-meta.json';

	if (!file_exists($json_file)) {
		$return->success = false;
		$return->message[] = 'Failed to open the Youtube Metadata file.';
		echo json_encode($return);
		return;
	}

	$yt_meta = json_decode(file_get_contents($json_file), true);

	foreach ($ids as $id) {
		$meta = to_object($yt_meta[$id]);

		if ($meta->delete) {
			if ($attachment_id = get_post_thumbnail_id($id))
				wp_delete_attachment($attachment_id, true);
			wp_delete_post($id, true);
			unset($yt_meta[$id]);
			$return->message[] = "Deleted post ID -> $id as the youtube ID no longer existed.";
			continue;
		}

		if (save_video_image_for_post($id, $meta)) {
			unset($yt_meta[$id]);
			add_post_meta($id, 'htm_youtube_refreshed', true);
			$return->message[] = "Saved new images for post ID -> $id, deleted the old ones and set the category.";
		} else $return->message[] = "Couldn't save the image for post ID -> $id.";
	}

	file_put_contents($json_file, json_encode($yt_meta));

	$return->success = true;
	echo json_encode($return);
	exit;
}
add_ajax_action('set_video_thumbnails');

/**
 * Used by How to Make - Video Editor
 * 
 * @return void 
 */
function htm_get_refresh_video_categories() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$return = (object) ['message' => []];

	$return->ids = get_results(
		"SELECT
		p.id AS id,
		p.post_title AS title,
		GROUP_CONCAT(t.name) AS tags
		FROM wp_term_relationships tr
		INNER JOIN wp_posts p
			ON tr.object_id = p.id
		INNER JOIN wp_term_taxonomy tt
			ON tt.term_taxonomy_id = tr.term_taxonomy_id
		INNER JOIN wp_terms t
			ON tt.term_id = t.term_id
		WHERE p.post_type = 'video'
		AND tt.taxonomy = 'post_tag'
		AND p.id NOT IN (SELECT
			pm.post_id
		FROM wp_postmeta pm
		WHERE pm.meta_key = 'tag_categories_set')
		GROUP BY p.id,
				p.post_title"
	);


	$return->count = count($return->ids);
	echo json_encode($return);
	exit;
}
add_ajax_action('get_refresh_video_categories');

/**
 * Used by How to Make - Video Editor 
 * 
 * @return void 
 */
function htm_set_refresh_video_categories() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$return = (object) ['message' => []];
	$post = to_object($_REQUEST['data']['ids'][0]);

	if ($new = set_video_categories_from_tags($post->id, $post->tags, $post->title))
		$return->message[] = "<b>{$post->title}</b> Categories set to: " . concat_strings($new, 'name');
	else
		$return->message[] = "<s><b>{$post->title}</b></s> could not set categories from tags.";

	$return->success = true;
	echo json_encode($return);
	exit;
}
add_ajax_action('set_refresh_video_categories');

/**
 * Used by How to Make - Video Editor
 * 
 * @return void 
 */
function htm_restart_refresh_video_categories() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$return = (object) ['message' => []];

	get_results(
		"DELETE FROM wp_postmeta WHERE meta_key = 'tag_categories_set'"
	);

	$return->message[] = 'You can now run Refresh Categories starting from scratch.';
	$return->success = true;
	echo json_encode($return);
	exit;
}
add_ajax_action('restart_refresh_video_categories');

/**
 * Used by How to Make - Video Editor
 * 
 * @return void 
 */
function htm_untagged_refresh_video_categories() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$return = (object) ['message' => []];

	get_results(
		"DELETE FROM wp_postmeta WHERE meta_key = 'tag_categories_set' AND meta_value = ''"
	);

	$return->message[] = 'You can now run Refresh Categories to retry all that were uncategorised last time.';
	$return->success = true;
	echo json_encode($return);
	exit;
}
add_ajax_action('untagged_refresh_video_categories');

/**
 * Used by How to Make - Video Editor
 * 
 * @return void 
 */
function htm_get_missing_durations() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$return = (object) ['message' => []];

	$return->ids = get_results(
		"SELECT
		pm.post_id AS id,
		pm.meta_value AS duration
		FROM wp_postmeta pm
		WHERE pm.meta_key = 'video_duration_raw'
		AND pm.post_id NOT IN (SELECT
			mp.post_id
		FROM wp_postmeta mp
		WHERE mp.meta_key = 'video_duration_seconds')"
	);

	$return->count = length($return->ids);
	echo json_encode($return);
	exit;
}
add_ajax_action('get_missing_durations');

/**
 * Used by How to Make - Video Editor 
 * 
 * @return void 
 */
function htm_set_missing_durations() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'settings_nonce'))
		exit(FAILED_NONCE);

	$return = (object) ['message' => []];
	$ids = to_object($_REQUEST['data']['ids']);

	foreach ($ids as $post) {
		$di  = new DateInterval($post->duration);
		$sec = ceil($di->days * 86400 + $di->h * 3600 + $di->i * 60 + $di->s);

		add_post_meta($post->id, 'video_duration_seconds', $sec, true);
	}

	$return->message[] = 'Added the duration in seconds for ' . length($ids) . ' videos.';
	$return->success = true;
	echo json_encode($return);
	exit;
}
add_ajax_action('set_missing_durations');
