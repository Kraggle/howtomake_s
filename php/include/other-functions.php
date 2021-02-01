<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/image.php');

/**
 * This is used to run tasks only once the plugin version
 * changes or it is activated for the first time.
 */
function htm_s_on_install() {

	// Do stuff only on update here
	$path = parse_url(get_template_directory_uri(), PHP_URL_PATH);
	$paths = array(
		'theme' => $path,
		'call' => "$path/php/getters",
		'assets' => "$path/assets",
		'images' => "$path/assets/images",
		'fonts' => "$path/assets/fonts",
		'ajax' => "/wp-admin/admin-ajax.php"
	);

	$js = fopen(get_template_directory() . '/scripts/custom/Paths.js', 'w') or die('Unable to open file!');
	fwrite(
		$js,
		'const V = JSON.parse(\'' . str_replace('\\', '', json_encode($paths)) . '\');' .
			'export default V;'
	);
	fclose($js);

	global $htm__s_version;
	add_option('htm__s_version', $htm__s_version);
	update_option('htm__s_version', $htm__s_version);
	error_log(get_site_option('htm__s_version'));
}

function htm_check_update() {
	global $htm__s_version;
	if (get_site_option('htm__s_version') != $htm__s_version) {
		htm_s_on_install();
	}
}
htm_check_update();

function remove_emoji($text) {

	// Match Emoticons
	$text = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $text);

	// Match Miscellaneous Symbols and Pictographs
	$text = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $text);

	// Match Transport And Map Symbols
	$text = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $text);

	// Match Enclosed Chars
	$text = preg_replace('/[\x{2500}-\x{2BEF}]/u', '', $text);

	// Match Dingbats
	$text = preg_replace('/[\x{2702}-\x{27B0}]/u', '', $text);

	// Match Variation Selector
	$text = preg_replace('/[\x{fe0f}]/u', '', $text);

	// Remove extra spaces
	$text = preg_replace('/\s{2,}/u', ' ', $text);



	return trim($text);
}

// Converts $title to Title Case, and returns the result.
function strtotitle($title) {

	// Our array of 'small words' which shouldn't be capitalised if
	// they aren't the first word. Add your own words to taste.
	$smallwordsarray = array(
		'of', 'a', 'the', 'and', 'an', 'or', 'nor', 'but', 'is', 'if', 'then', 'else', 'when',
		'at', 'from', 'by', 'on', 'off', 'for', 'in', 'out', 'over', 'to', 'into', 'with'
	);

	// Split the string into separate words
	$words = explode(' ', $title);

	foreach ($words as $key => $word) {
		// If this word is the first, or it's not one of our small words, capitalise it
		// with ucwords().
		if ($key == 0 or !in_array($word, $smallwordsarray))
			$words[$key] = ucwords($word);
	}

	// Join the words back into a string
	$newtitle = implode(' ', $words);

	return $newtitle;
}

function friendly_title($text) {

	$text = remove_emoji($text);

	// Remove extra spaces
	$words = explode(' ', $text);
	$join = [];
	foreach ($words as $word) {
		if (preg_match('/\w+|\&|\|/', $word))
			$join[] = $word;
	}



	$text = implode(' ', $join);

	return $text;
}

if (!function_exists('get_slug_from_string')) {
	function get_slug_from_string($string) {
		return preg_replace(
			'/-\w+-{0,}$|^-/',
			'',
			substr(
				preg_replace(
					'/-{1,}/',
					'-',
					strtolower(
						preg_replace(
							'/[^a-zA-Z0-9]+/',
							'-',
							friendly_title($string)
						)
					)
				),
				0,
				55
			)
		);
	}
}

/**
 * Creates a JSON file in the working directory and overwrites with the contents of the array.
 * 
 * @param array $object The object to be logged.
 * @return void 
 */
function logger_object($object) {
	if (!is_array($object))
		return;

	$fp = fopen('object.json', 'w');
	fwrite($fp, json_encode($object, JSON_PRETTY_PRINT));
	fclose($fp);
}

if (!function_exists('logger')) {
	function logger() {
		if (!IS_DEBUG) return;

		$db = array_shift(debug_backtrace());
		$line = $db['line'];
		$file = $db['file'];

		$msg = "$file:$line [logger]";

		foreach (func_get_args() as $arg) {
			$msg .= "\n" . (in_array(gettype($arg), ['string', 'double', 'integer']) ? $arg : json_encode($arg, JSON_PRETTY_PRINT));
		}

		error_log($msg . "\n");
	}
}

function logger_print() {
	if (!IS_DEBUG) return;

	$db = array_shift(debug_backtrace());
	$line = $db['line'];
	$file = $db['file'];

	$args = func_get_args();
	$l = count($args);

	$msg = "$file:$line [logger]";

	for ($i = 1; $i < $l; $i += 2) {
		$name = $args[$i - 1];
		$arg = $args[$i];
		$msg .= "\n$name: " . (in_array(gettype($arg), ['string', 'double', 'integer']) ? $arg : json_encode($arg, JSON_PRETTY_PRINT));
	}

	error_log($msg . "\n");
}

/**
 * Get all the registered image sizes along with their dimensions
 *
 * @global array $_wp_additional_image_sizes
 *
 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
 *
 * @return array $image_sizes The image sizes
 */
function get_all_image_sizes() {
	global $_wp_additional_image_sizes;

	$default_image_sizes = get_intermediate_image_sizes();

	foreach ($default_image_sizes as $size) {
		$image_sizes[$size]['width'] = intval(get_option("{$size}_size_w"));
		$image_sizes[$size]['height'] = intval(get_option("{$size}_size_h"));
		$image_sizes[$size]['crop'] = get_option("{$size}_crop") ? get_option("{$size}_crop") : false;
	}

	if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
		$image_sizes = array_merge($image_sizes, $_wp_additional_image_sizes);
	}

	return $image_sizes;
}

function generate_category_thumbnails($object_id) {

	$image = get_post($object_id);

	if (!$image) return false;

	$upload_dir = wp_upload_dir();
	$image_fullpath = get_attached_file($image->ID);

	// Can't get the image path
	if (false === $image_fullpath || strlen($image_fullpath) == 0) {

		// Try getting the image path from url
		if ((strrpos($image->guid, $upload_dir['baseurl']) !== false)) {
			$image_fullpath = realpath($upload_dir['basedir'] . DIRECTORY_SEPARATOR . substr($image->guid, strlen($upload_dir['baseurl']), strlen($image->guid)));
		}
	}

	// Image path incomplete
	if ((strrpos($image_fullpath, $upload_dir['basedir']) === false))
		$image_fullpath = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $image_fullpath;

	// Image doesn't exists
	if (!file_exists($image_fullpath) || realpath($image_fullpath) === false) {

		// Try getting the image path from url
		if ((strrpos($image->guid, $upload_dir['baseurl']) !== false)) {
			$image_fullpath = realpath($upload_dir['basedir'] . DIRECTORY_SEPARATOR . substr($image->guid, strlen($upload_dir['baseurl']), strlen($image->guid)));
		}
	}

	update_attached_file($image->ID, $image_fullpath);

	$file_info = pathinfo($image_fullpath);
	$file_info['filename'] .= '-';
	$name = $file_info['filename'];

	$files = [];
	// logger($file_info);
	$path = to_array(array_diff(scandir($file_info['dirname']), ['.', '..']));

	$files = preg_grep("/^$name\d+x\d+\.\w+/i", $path);

	$meta = to_object(wp_get_attachment_metadata($image->ID));
	$sizes = get_image_sizes_for_attachment($object_id);

	$stored = (object) [];
	foreach ($meta->sizes as $size => $value) {
		preg_match('/-(\d+x\d+)\./', $value->file, $matches);
		if ($matches[1]) {
			$dim = $matches[1];
			if ($sizes[$size]) {
				$stored->$dim = $size;
			}
		}
	}

	// logger($image_fullpath, $files);

	foreach ($files as $thumb) {
		$thumb_path = $file_info['dirname'] . DIRECTORY_SEPARATOR . $thumb;
		$thumb_info = pathinfo($thumb_path);
		$valid_thumb = preg_split("/$name/i", $thumb_info['filename']);

		// logger(
		// 	'valid thumb: ' . ($valid_thumb[0] == "" ? 'yes' : 'no'),
		// 	$valid_thumb,
		// 	$file_info['filename'],
		// 	$thumb_info['filename']
		// );

		if ($valid_thumb[0] == "") {

			$dim = $valid_thumb[1];
			if (!$stored->$dim) {
				$dims = explode('x', $dim);
				// logger("maybe deleting: $thumb");
				if (count($dims) == 2 && is_numeric($dims[0]) && is_numeric($dims[1])) {
					// logger("deleting: $thumb");
					unlink($thumb_path);
				}
			}
		}
	}

	$metadata = wp_generate_attachment_metadata($image->ID, $image_fullpath);
	wp_update_attachment_metadata($image->ID, $metadata);
}

if (!function_exists('to_object')) {
	/**
	 * Converts a multidimensional array to an object.
	 * 
	 * @param array   $array The array to convert.
	 * @return object The converted array.
	 */
	function to_object($array) {
		return json_decode(json_encode($array), false);
	}
}

if (!function_exists('to_array')) {
	/**
	 * Converts an object to a multidimensional array.
	 * 
	 * @param object $object The object to convert.
	 * @return array The converted object.
	 */
	function to_array($object) {
		return json_decode(json_encode($object), true);
	}
}

function preg_number_range($number, $range = 1) {
	$reg = [];
	for ($i = 0 - $range; $i < $range + 1; $i++)
		$reg[] = ($number + $i);
	return '(' . implode('|', $reg) . ')';
}

function get_image_sizes_for_attachment($object_id) {

	$terms = wp_get_object_terms($object_id, 'attachment_category');
	$category = [];
	foreach ($terms as $term)
		$category[] = $term->slug;

	$meta = wp_get_attachment_metadata($object_id);

	return get_image_sizes_for_category($category, $meta['width'], $meta['height']);
}

function get_image_sizes_for_category($category, $orig_w = 0, $orig_h = 0) {
	$sizes = get_all_image_sizes();

	if (!is_array($category))
		$category = [$category];

	$needed = [];
	foreach ($sizes as $size => $value) {
		foreach ($category as $cat) {
			if (!in_array($cat, $value['category']))
				continue;

			$w = $value['width'];
			$h = $value['height'];

			if (!empty($orig_w) || !empty($orig_h)) {
				if (empty($h)) {
					if (absint($orig_w - $w) < 3)
						break;
					if ($orig_w <= $w)
						break;
				} elseif (empty($w)) {
					if (absint($orig_h - $h) < 3)
						break;
					if ($orig_h <= $h)
						break;
				} else {
					if (absint($orig_w - $w) < 3 && absint($orig_h - $h) < 3)
						break;
					if ($orig_w <= $w && $orig_h <= $h) {
						break;
					}
				}
			}
			// logger_print('orig_w', $orig_w, 'orig_h', $orig_h, 'w', $w, 'h', $h);
			$needed[$size] = $value;
		}
	}

	return $needed;
}

/**
 * Register a new image size with Category.
 *
 * @global array $_wp_additional_image_sizes Associative array of additional image sizes.
 *
 * @param string           $name     Image size identifier.
 * @param int|array|string $category Can be the category slug, id, or an array of either.
 * @param int              $width    Optional. Image width in pixels. Default 0.
 * @param int              $height   Optional. Image height in pixels. Default 0.
 * @param bool|array       $crop     Optional. Image cropping behavior. If false, the image will be scaled (default),
 *                           If true, image will be cropped to the specified dimensions using center positions.
 *                           If an array, the image will be cropped using the array to specify the crop location.
 *                           Array values must be in the format: array( x_crop_position, y_crop_position ) where:
 *                               - x_crop_position accepts: 'left', 'center', or 'right'.
 *                               - y_crop_position accepts: 'top', 'center', or 'bottom'.
 */
function add_image_size_category($name, $category, $width = 0, $height = 0, $crop = false) {
	global $_wp_additional_image_sizes, $wpdb;

	if (!is_array($category))
		$category = [$category];

	$terms = $wpdb->get_results(
		"SELECT a.term_id, b.slug
	FROM `wp_term_taxonomy` a, `wp_terms` b
	WHERE 
		a.taxonomy LIKE 'attachment_category' AND
		a.term_id = b.term_id"
	);

	$cats = [];
	foreach ($category as $cat) {

		$column = intval($cat) ? 'term_id' : 'slug';
		$i = array_search($cat, array_column($terms, $column));

		if ($i > -1)
			$cats[] = $terms[$i]->slug;
	}

	$_wp_additional_image_sizes[$name] = array(
		'width'    => absint($width),
		'height'   => absint($height),
		'crop'     => $crop,
		'category' => $cats
	);
}

function length($obj) {
	if (is_array($obj) || $obj instanceof Countable)
		return count($obj);

	if (is_object($obj)) {
		$i = 0;
		foreach ($obj as $k)
			$i++;
		return $i;
	}

	return 0;
}

// Get taxonomies
function htm_get_taxonomies() {

	// Global array
	$taxes = array();

	// Built in
	$term_args = array('public' => true, '_builtin' => true); // Arguments
	$taxonomies = get_taxonomies($term_args, 'names', 'and'); // Get taxonomies
	foreach ($taxonomies as $taxonomy) array_push($taxes, $taxonomy); // Push to global array

	// Not builtin
	$term_args = array('public' => true, '_builtin' => false); // Arguments
	$taxonomies = get_taxonomies($term_args, 'names', 'and'); // Get taxonomies
	foreach ($taxonomies as $taxonomy) array_push($taxes, $taxonomy); // Push to global array

	// Return global array
	return $taxes;
}

function htm_checkbox($name, $checked = true) {
	$input = "<input id='$name' name='$name' type='checkbox'" . ($checked ? ' checked />' : ' />');
	$mark  = '<div class="check-mark"></div>';
	echo "<div class='check-wrap'>{$input}{$mark}</div>";
}

function get_results(string $query) {
	if (empty($query)) return [];
	global $wpdb;
	return $wpdb->get_results($query);
}

function has_post_meta($id, $key) {
	return !empty(get_post_meta($id, $key, true));
}

function htm_set_permalink($id, $link, $post) {

	if (has_post_meta($id, 'htm_permalink'))
		update_post_meta($id, 'htm_permalink', $link);
	else
		add_post_meta($id, 'htm_permalink', $link);

	$children = get_children([
		'post_parent' 	 => $id,
		'post_type'   	 => $post->post_type
	]);

	if (empty($children)) return;

	foreach ($children as $child) {
		$link = get_permalink($child->ID);
		htm_set_permalink($child->ID, $link, $child);
	}
}

function get_php_library($file = '') {
	return get_template_directory() . '/php/libraries/' . $file;
}

/**
 * Return the video thumbnail URL.
 *
 * @since 4.4.0
 *
 * @param int|WP_Post  $post Optional. Post ID or WP_Post object.  Default is global `$post`.
 * @param string|array $size Optional. Registered image size to retrieve the source for or a flat
 *                           array of height and width dimensions. Default 'post-thumbnail'.
 * @return string|false Post thumbnail URL or false if no URL is available.
 */
function get_the_video_thumbnail_url($post = null, $size = 'post-thumbnail') {
	if (!$post_thumbnail_id = get_post_thumbnail_id($post)) {

		get_video_info_for_post($post);

		if (!$post_thumbnail_id = get_post_thumbnail_id($post)) {
			logger('Something went terribly wrong, there is no image on youtube!');
			return false;
		}
	} else {

		$path = get_post_meta($post_thumbnail_id, '_wp_attached_file', true);
		if (!upload_file_exists($path)) {
			get_video_info_for_post($post);

			$post_thumbnail_id = get_post_thumbnail_id($post);
		}
	}

	return wp_get_attachment_image_url($post_thumbnail_id, $size);
}

function upload_file_exists($path) {
	return !$path ? false : file_exists(wp_upload_dir()['basedir'] . '/' . $path);
}

function get_video_info_for_post($post) {
	global $id;
	if (!$post) $post = $id;
	if (!$post || get_post_type($post) !== 'video') return 0;

	if ($info = get_video_info_for_ids(get_post_meta($post, 'youtube_video_id', true), true))
		if (save_video_image_for_post($post, $info))
			add_post_meta($id, 'htm_youtube_refreshed', true);
}

/**
 * Pull video information from youtube api for upto 50 youtube ids.
 * 
 * @param string|string[] $ids    The youtube id(s)
 * @param bool            $single (optional) True to return the first result. Default false.
 * @return object		  The results from youtube api. 
 */
function get_video_info_for_ids($ids, $single = false) {
	if (is_array($ids))
		$ids = implode('%2C', $ids);

	// video json data
	$json_result = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet%2CcontentDetails&id=$ids&key=" . K_YT_API_KEY);
	$result = json_decode($json_result);

	return count($result->items) ? ($single ? $result->items[0] : $result) : false;
}

global $avoid_other_sizes;
$avoid_other_sizes = false;

function save_video_image_for_post($post, $info) {
	// $content = $info->snippet->description;
	$thumbs = $info->snippet->thumbnails;
	$thumb = $thumbs->maxres ?: ($thumbs->standard ?: ($thumbs->high ?: ($thumbs->medium ?: $thumbs->default)));

	$image = @file_get_contents($thumb->url);
	if (!$image) return false;

	// remove old attachment if one exists
	if ($attachment_id = get_post_thumbnail_id($post))
		wp_delete_attachment($attachment_id, true);

	$finfo = new finfo(FILEINFO_MIME);
	$mimeType = $finfo->buffer($image);

	$fileExt = [
		'image/png' => 'png',
		'image/jpg' => 'jpg',
		'image/jpeg' => 'jpg',
		'image/jpeg' => 'jpeg',
		'image/gif' => 'gif',
		'image/svg' => 'svg'
	];

	$ext = $fileExt[$mimeType] ?: 'jpg';

	$slug = get_slug_from_string($info->snippet->title);
	$filename = $post . '-' . $slug . '.' . $ext;

	$filePath = wp_upload_dir()['basedir'] . "/yt-thumb/$filename";

	if (!file_exists($filePath))
		file_put_contents($filePath, $image);

	$mediaId = wp_insert_attachment([
		'post_title'     => wp_strip_all_tags($info->snippet->title),
		'post_content'   => '',
		'post_mime_type' => $mimeType,
		'post_status'    => 'inherit',
		'post_parent'    => $post,
		'post_author'    => 1
	], $filePath, $post);

	global $avoid_other_sizes;
	$avoid_other_sizes = true;

	// Generate the metadata for the attachment, and update the database record.
	$attach_data = wp_generate_attachment_metadata($mediaId, $filePath);
	wp_update_attachment_metadata($mediaId, $attach_data);
	set_post_thumbnail($post, $mediaId);

	$avoid_other_sizes = false;

	wp_set_object_terms($mediaId, 'video-featured-images', 'attachment_category', true);

	return true;
}

function get_status_code($url) {
	$headers = @get_headers($url);
	$headers = (is_array($headers)) ? implode("\n ", $headers) : $headers;

	preg_match('#HTTP/.*\s+(\d{3})\s#i', $headers, $match);
	return count($match) ? $match[1] : false;
}
