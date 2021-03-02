<?php
$nl = "<br />\n";
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/image.php');



function indent($count = 1, $symbol = ' &raquo; ') {
	$indent = '';
	for ($i = 0; $i < $count; $i++) {
		$indent .= $symbol;
	}
	return $indent;
}

if (!function_exists('getYoutubeService')) {

	function getYoutubeService() {
		// INFO: Changed this to try the API key before resuming, as with 3 
		// INFO: API keys we can triple the quota in a day 

		$appName = 'Youtube Scraper';
		$apiKeys = [ // Youtube API Keys
			'AIzaSyByB7ZeVa4qIN9TPeAlgG6tJtkYoT8Xme8',
			'AIzaSyDtGJtBPXdcWfBswi3mJSezfoj23Fr2T1A',
			'AIzaSyD7iDUybQmkxls-Ge3kQ_sGHLsNbAxvc00',
		];

		// Google API init
		$client = new Google_Client();
		$client->setApplicationName($appName);

		foreach ($apiKeys as $key) {

			$client->setDeveloperKey($key);
			$service = new Google_Service_YouTube($client);

			try {
				$results = $service->i18nRegions->listI18nRegions('id');

				if ($results)
					break;
			} catch (Exception $e) {
				videoLogger::getInstance()->put('Exception: ' . $e->getMessage());
			}
		}

		return $service;
	}
}

/**
 * Get enhanced video information from Youtube
 *
 * @param array $features An array of features to save, all if null. default: null
 * @return void
 */
function getExtraYoutubeInfo(array $features = null) {

	global $log;

	$videos = get_results(
		"SELECT
		p.ID AS id,
		m.meta_value AS yt_id,
		p.post_title AS title
		FROM wp_posts p
		INNER JOIN wp_postmeta m
			ON p.ID = m.post_id
		WHERE p.post_type = 'video'
		AND p.post_status = 'publish'
		AND m.meta_key = 'youtube_video_id'
		AND (p.ID NOT IN (SELECT
			tr.object_id
		FROM wp_term_relationships tr
			INNER JOIN wp_term_taxonomy tt
			ON tr.term_taxonomy_id = tt.term_taxonomy_id
		WHERE tt.taxonomy = 'post_tag')
		OR p.ID NOT IN (SELECT
			pm.post_id
		FROM wp_postmeta pm
		WHERE pm.meta_key = 'video_duration_raw'))"
	);

	$videoIds = list_ids($videos, 'yt_id');

	if (!count($videoIds))
		return;

	$log->put('Getting extra youtube information!');
	$log->put('There are ' . count($videoIds) . ' that still have missing info.');

	// Process in batches to reduce API calls. 50 is Youtube's pagination limit.
	$chunkedVideoIDs = array_chunk($videoIds, 50, true);

	foreach ($chunkedVideoIDs as $ids) {

		// Get more information for the videos
		$optParams = array(
			'id' => implode(',', $ids),
		);

		try {
			$results = getYoutubeService()->videos->listVideos('id,snippet,contentDetails,statistics,topicDetails', $optParams);

			foreach ($results->items as $item) {
				$i = array_search($item->id, array_column($videos, 'yt_id'));
				if (!is_numeric($i)) continue; // Skip if video ID not in list, shouldn't happen
				$post = $videos[$i];

				$log->put(indent() . "Adding extra information for: {$post->title}");

				// Attach keywords as tags to post
				if (is_null($features) || in_array('keywords', $features)) {
					wp_set_post_tags($post->id, $item->snippet->tags, true);
					set_video_categories_from_tags($post->id, $item->snippet->tags, $post->title);
				}

				// Video Duration
				if (is_null($features) || in_array('duration', $features)) {
					// ISO_8601 Format
					update_post_meta($post->id, 'video_duration_raw', $item->contentDetails->duration);

					$di  = new DateInterval($item->contentDetails->duration);
					$sec = ceil(($di->days * 86400) + ($di->h * 3600) + ($di->i * 60) + $di->s);
					add_post_meta($post->id, 'duration_seconds', $sec, true);

					if ($sec < 58) {
						wp_update_post([
							'ID' => $post->id,
							'post_status' => 'draft'
						]);
					}
				}

				// INFO: Added this to grab images, buy only if it's in features
				// Video Featured Image
				if (is_array($features) && in_array('image', $features))
					save_video_image_for_post($post->id, $item);

				save_youtube_data($post->id, $item);
			}
		} catch (Exception $e) {
			$log->put('Exception: ' . $e->getMessage());
		}

		test_restart();
	}
}

class videoLogger {

	private
		$file,
		$format;
	private static $instance;

	public function __construct($filename, $format = '[d-M-Y H:i:s]') {
		$this->file = $filename;
		$this->format = $format;

		if (!file_exists($filename))
			file_put_contents($this->file, '');
	}

	public static function getInstance($filename = '', $format = '[d-M-Y H:i:s]') {
		return !isset(self::$instance) ?
			self::$instance = new videoLogger($filename, $format) :
			self::$instance;
	}

	public function put($insert) {
		$timestamp = date($this->format);
		file_put_contents($this->file, "$timestamp &raquo; $insert\n", FILE_APPEND);
	}

	public function get() {
		$content = file_get_contents($this->file);
		return $content;
	}
}

function get_channel_video_ids($term_id) {
	return get_results(
		"SELECT
		m.meta_value AS yt_id,
		p.ID AS id
		FROM wp_term_relationships r
		INNER JOIN wp_posts p
			ON r.object_id = p.ID
		INNER JOIN wp_postmeta AS m
			ON m.post_id = p.ID
		WHERE r.term_taxonomy_id = $term_id
		AND m.meta_key = 'youtube_video_id'"
	);
}

function get_playlist_items($ytUploadsId, $nextPageToken = null) {
	try {

		$query = [
			'maxResults' => 50,
			'playlistId' => $ytUploadsId
		];

		if ($nextPageToken)
			$query['pageToken'] = $nextPageToken;

		return getYoutubeService()->playlistItems->listPlaylistItems('snippet', $query);
	} catch (Exception $e) {
		videoLogger::getInstance()->put('Exception: ' . $e->getMessage());
	}
}


function save_user_post_interaction($userId, $postId, $attribute, $value) {
	global $wpdb;

	$data = [
		'user_id'       => $userId,
		'post_id'       => $postId,
		'attribute'    => $attribute,
		'attribute_value'    => $value
	];
	try{
		$result = $wpdb->insert( $wpdb->prefix.'htm_user_post_interactions', $data );
	}
	catch(Exception $e){
		return 0;
	}
	return $result;
}

function delete_user_post_interaction($userId, $postId, $attribute) {
	global $wpdb;

	$result = $wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->prefix}htm_user_post_interactions 
			WHERE user_id = %d
			AND post_id = %d
			AND attribute = %s
			",
			[$userId, $postId, $attribute]
		)
	);

	return $result;
}

function get_user_post_interactions($userId, $postId){
	global $wpdb;
	$results = $wpdb->get_results( 
		$wpdb->prepare("SELECT attribute, attribute_value
		FROM {$wpdb->prefix}htm_user_post_interactions 
		WHERE user_id=%d
		AND post_id=%d", [$userId, $postId]) 
	);

	$outputArray = [];

	foreach($results as $elem){
		$outputArray[$elem->attribute] = $elem->attribute_value;
	}


	return $outputArray;
}


/**
 * This is used to run tasks only once the plugin version
 * changes or it is activated for the first time.
 */
function htm_s_on_install() {

	// Do stuff only on update here
	$path = parse_url(get_template_directory_uri(), PHP_URL_PATH);
	$paths = array(
		'theme' => $path,
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

	global $htm__s_version, $wpdb;

	/* Keep these as it is the only place that creates them */
	$charset_collate = $wpdb->get_charset_collate();
	$max_index_length = 191;

	$wpdb->{'videometa'} = "{$wpdb->prefix}yt_video_meta";
	$wpdb->{'channelmeta'} = "{$wpdb->prefix}yt_channel_meta";

	$wpdb->query(
		"CREATE TABLE IF NOT EXISTS {$wpdb->videometa} (
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			post_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) default NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY post_id (post_id),
			KEY meta_key (meta_key($max_index_length))
		) $charset_collate;"
	);
	$wpdb->query(
		"CREATE TABLE IF NOT EXISTS {$wpdb->channelmeta} (
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			object_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) default NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY object_id (object_id),
			KEY meta_key (meta_key($max_index_length))
		) $charset_collate;"
	);

	$wpdb->query(
		"CREATE TABLE {$wpdb->prefix}htm_user_post_interactions (
			user_id int UNSIGNED NOT NULL,
			post_id int UNSIGNED NOT NULL,
			attribute varchar(100) NOT NULL,
			attribute_value text NOT NULL,
			last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			KEY  user_id(user_id),
			KEY  post_id(post_id)
		) $charset_collate;"
	);
	

	
	$wpdb->query(
		"ALTER TABLE {$wpdb->prefix}htm_user_post_interactions
			ADD CONSTRAINT user_post_attrib PRIMARY KEY (user_id, post_id, attribute);"
	);



	
	/* End of keep this section */

	add_option('htm__s_version', $htm__s_version);
	update_option('htm__s_version', $htm__s_version);
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

	// logger($image_fullpath);

	// Can't get the image path
	if (false === $image_fullpath || strlen($image_fullpath) == 0) {

		// Try getting the image path from url
		if ((strrpos($image->guid, $upload_dir['baseurl']) !== false)) {
			$image_fullpath = realpath($upload_dir['basedir'] . DIRECTORY_SEPARATOR . substr($image->guid, strlen($upload_dir['baseurl']), strlen($image->guid)));
		}
	}

	// Image path incomplete and not CDN
	if (strpos($image_fullpath, '://cdn') === false && strrpos($image_fullpath, $upload_dir['basedir']) === false)
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

	$path = to_array(array_diff(scandir($file_info['dirname']), ['.', '..']));

	$files = preg_grep("/^$name\d+x\d+\.\w+/i", $path);

	$meta = to_object(wp_get_attachment_metadata($image->ID));
	$sizes = get_image_sizes_for_attachment($object_id);

	$stored = (object) [];
	if (is_countable($meta->sizes)) {
		foreach ($meta->sizes as $size => $value) {
			preg_match('/-(\d+x\d+)\./', $value->file, $matches);
			if ($matches[1]) {
				$dim = $matches[1];
				if ($sizes[$size]) {
					$stored->$dim = $size;
				}
			}
		}
	}

	// logger($image_fullpath, $files);

	if (IS_LIVE) {
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
	}

	// list_used_hooks();

	$metadata = wp_generate_attachment_metadata($image->ID, $image_fullpath);

	// logger('TEST');

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

function get_php_includes($file = '') {
	return get_template_directory() . '/php/include/' . $file;
}

function get_php_vendor($file = '') {
	return get_template_directory() . '/php/vendor/' . $file;
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


	if (strpos($mimeType, 'image/jpeg') === 0) $mimeType = 'image/jpeg'; // Fix for image/jpegcharsetbinary

	$fileExt = [
		'image/png' => 'png',
		'image/jpg' => 'jpg',
		'image/jpeg' => 'jpg',
		// 'image/jpeg' => 'jpeg',
		'image/gif' => 'gif',
		'image/svg' => 'svg'
	];

	$ext = $fileExt[$mimeType] ?: 'jpg';

	$slug = get_slug_from_string($info->snippet->title);
	$filename = $post . '-' . $slug . '.' . $ext;

	$filePath = wp_upload_dir()['basedir'] . '/yt-thumb/';

	if (!file_exists($filePath))
		mkdir($filePath);

	$filePath .= $filename;

	if (!file_exists($filePath))
		file_put_contents($filePath, $image);

	$mediaId = wp_insert_attachment([
		'post_title'     => wp_strip_all_tags($info->snippet->title),
		'post_content'   => '',
		'post_mime_type' => $mimeType,
		'post_status'    => 'inherit',
		'post_parent'    => $post,
		'post_author'    => 1,
		'tax_input'		 => [
			'attachment_category' => 14872
		]
	], $filePath, $post);

	// Generate the metadata for the attachment, and update the database record.
	$attach_data = wp_generate_attachment_metadata($mediaId, $filePath);
	wp_update_attachment_metadata($mediaId, $attach_data);
	set_post_thumbnail($post, $mediaId);

	return $mediaId;
}

// function get_status_code($url) {
// 	$headers = @get_headers($url);
// 	$headers = (is_array($headers)) ? implode("\n ", $headers) : $headers;

// 	preg_match('#HTTP/.*\s+(\d{3})\s#i', $headers, $match);
// 	return count($match) ? $match[1] : false;
// }



function random_string($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

/**
 * Converts the returned SQL query into an array or string of ids.
 * 
 * @param array  $array         The SQL result
 * @param string $key       The key of the id, default 'ID'
 * @param string $type      The return type (array|string), default 'array'
 * @param string $delimiter If $type is string, what to join with, default ','
 * @return array|string Depending on the value of $type
 */
function list_ids($array, $key = 'ID', $type = 'array', $delimiter = ',') {
	$ids = [];
	foreach ($array as $value) {
		$value = to_object($value);
		$ids[] = is_numeric($value->$key) ? intval($value->$key) : $value->$key;
	}
	return $type == 'array' ? $ids : implode($delimiter, $ids);
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
 * Joins a array of strings or and assoc array with defined key to a human
 * readable string. Used for outputting messages and content for diplay.
 * 
 * @param array  $array     The object to take the strings from.
 * @param string $key       The key to use if assoc array.
 * @param string $delimiter What to join them together with.
 * @param string $last      What to put before the last entry.
 * @return string The output string joined together. 
 */
function concat_strings($array, $key = null, $delimiter = ', ', $last = ' and ') {
	if (!is_array($array) || !count($array))
		return '';

	if (count($array) == 1)
		return $key ? to_object($array[0])->$key : $array[0];

	$final = array_pop($array);
	$final = $key ? to_object($final)->$key : $final;

	$entries = [];
	foreach ($array as $entry)
		$entries[] = $key ? to_object($entry)->$key : $entry;

	return implode($delimiter, $entries) . $last . $final;
}

// TODO: Make this work having different instances for each filename input.
/**
 * This, like error_log, outputs whatever you tell it to to a file of your choosing.
 * 
 * @param string $filename This is the path and filename of the file you want to log to.
 * @param string $format   This is the format of the date of each string output to the log.
 */
class outputLogger {

	private
		$file,
		$format;
	private static $instance;

	public function __construct($filename, $format = '[d-M-Y H:i:s]') {
		$this->file = $filename;
		$this->format = $format;

		$path = pathinfo($filename);
		if (!file_exists($path['dirname']))
			mkdir($path['dirname']);

		if (!file_exists($filename))
			file_put_contents($this->file, '');
	}

	public static function getInstance($filename = '', $format = '[d-M-Y H:i:s]') {
		return !isset(self::$instance) ?
			self::$instance = new outputLogger($filename, $format) :
			self::$instance;
	}

	public function put($insert) {
		$timestamp = date($this->format);
		file_put_contents($this->file, "$timestamp $insert\n", FILE_APPEND);

		return $this;
	}

	public function get() {
		$content = file_get_contents($this->file);
		return $content;
	}
}

/**
 * Used to automatically set the video categories from the YouTube tags
 * 
 * @param int             $video_id    The video id
 * @param string|string[] $video_tags  The tags from YouTube
 * @param string          $video_title The video title
 * @return false|array    If failed false, otherwise an array of the new categories
 */
function set_video_categories_from_tags($video_id, $video_tags, $video_title) {

	if (get_post_type($video_id) != 'video')
		return false;

	if (!is_array($video_tags))
		$video_tags = explode(',', $video_tags);

	if (empty($video_tags))
		return false;

	$cats = get_results(
		"SELECT
		t.term_id,
		t.name,
		tm.meta_value AS tags
		FROM wp_terms t
		INNER JOIN wp_term_taxonomy tt
			ON t.term_id = tt.term_id
		INNER JOIN wp_termmeta tm
			ON tm.term_id = t.term_id
		WHERE tt.taxonomy = 'video-category'
		AND tm.meta_key = 'alternative_names'"
	);

	// The videos channel
	$terms = get_the_terms($video_id, 'video-channel');

	$new = [];
	foreach ($cats as $cat) {
		// The channel(s) to exclude
		$excludes = get_term_meta($cat->term_id, 'exclude_channels', true);
		// Check if is being excluded
		if (in_object($excludes, $terms, 'term_id')) {
			// If it is, skip adding it
			continue;
		}

		$tags = explode(',', $cat->tags);
		$score = 0;

		foreach ($tags as $tag) {
			if ($matches = preg_grep("/{$tag}/i", $video_tags))
				$score += length($matches);
		}

		$tags = implode('|', $tags);
		preg_match("/({$tags})/i", $video_title, $matches);
		if (count($matches)) {
			array_shift($matches);
			$score += count($matches);
			wp_set_post_tags($video_id, $matches, true);
		}

		if ($score) {
			$cat->score = $score;
			$new[] = $cat;
		}
	}

	if (length($new)) {
		wp_set_object_terms($video_id, list_ids($new, 'term_id'), 'video-category');
		add_post_meta($video_id, 'tag_categories_set', true, true);

		return $new;
	} else {
		wp_update_post([
			'ID'          => $video_id,
			'post_status' => 'draft'
		]);
		wp_remove_object_terms($video_id, list_ids($cats, 'term_id'), 'video-category');
		add_post_meta($video_id, 'tag_categories_set', false, true);

		return false;
	}
}

function in_object($needle, $haystack, $key) {
	if (empty($needle)) return [];

	if (!is_array($needle))
		$needle = [$needle];

	if (!is_array($haystack))
		$haystack = [$haystack];

	$return = [];
	foreach ($needle as $n)
		$return = array_merge($return, array_keys(array_column($haystack, $key), $n));

	return $return;
}

function get_search_categories() {

	$taxes = get_terms([
		'taxonomy' => 'category'
	]);

	$return = [];
	foreach ($taxes as $tax) {
		$return[$tax->slug] = [
			'post' => $tax
		];
	}

	$taxes = get_terms([
		'taxonomy' => 'video-category'
	]);

	foreach ($taxes as $tax) {
		if ($return[$tax->slug]) {
			$return[$tax->slug]['video'] = $tax;
		} else {
			$return[$tax->slug] = [
				'video' => $tax
			];
		}
	}

	ksort($return);

	return to_object($return);
}

/*
 * Define custom user roles (for Free, Premium members)
 * Make sure this matches the dashboard
 * Uses the version option to ensure only runs once.
 */
function htm__update_custom_roles() {
	if (get_option('custom_roles_version') < 1) {
		add_role('free_member', 'Free Member', array(get_role('subscriber')->capabilities));
		add_role('premium_member', 'Premium Member', array(get_role('subscriber')->capabilities));
		update_option('custom_roles_version', 1);
	}
}
add_action('init', 'htm__update_custom_roles');




/**
 * This runs from the How to Make - Video Editor menu, it will get all selected 
 * information for every video that is fed in. 
 * 
 * $features can include: keywords, duration, image, title, slug, description, excerpt
 *
 * @param array $videos   An array of objects containing post id, title and yt_id
 * @param array $features An array of features to save, all if null. default: null
 * @return void
 */
function refresh_youtube_data(array $items, array $features = null) {

	if (!count($items))
		return false;

	foreach ($items as $item) {
		$item = to_object($item);
		$title = friendly_title($item->snippet->title);
		$args = [
			'ID' => $item->post_id
		];

		// Attach keywords as tags to post
		if (is_null($features) || in_array('keywords', $features)) {
			wp_set_post_tags($item->post_id, $item->snippet->tags, true);
			set_video_categories_from_tags($item->post_id, $item->snippet->tags, $title);
		}

		// Video Duration
		if (is_null($features) || in_array('duration', $features)) {
			// ISO_8601 Format
			update_post_meta($item->post_id, 'video_duration_raw', $item->contentDetails->duration);

			$di  = new DateInterval($item->contentDetails->duration);
			$sec = ceil(($di->days * 86400) + ($di->h * 3600) + ($di->i * 60) + $di->s);
			update_post_meta($item->post_id, 'duration_seconds', $sec, true);

			if ($sec < 58)
				$args['post_status'] = 'draft';
		}

		// Video Featured Image (only works if included in features)
		if (is_array($features) && in_array('image', $features))
			save_video_image_for_post($item->post_id, $item);

		// Video Title
		if (is_null($features) || in_array('title', $features))
			$args['post_title'] = $title;

		// The slug [permalink] of the video (only works if included in features)
		if (is_array($features) && in_array('slug', $features)) {
			// action
		}

		// Video Description
		if (is_null($features) || in_array('description', $features))
			$args['post_content'] = format_youtube_description($item->snippet->description);

		if (length($args) > 1)
			wp_update_post($args);

		add_post_meta($item->post_id, 'htm_youtube_extra', true);

		return 'Refreshed ' . concat_strings($features) . " for $title @ {$item->post_id}";
	}
}

/**
 * This runs from the How to Make - Video Editor menu, it will get all selected 
 * information for every video that is fed in. 
 * 
 * $features can include: keywords, duration, image, title, slug, description, excerpt
 *
 * @param array $videos   An array of objects containing post id, title and yt_id
 * @param array $features An array of features to save, all if null. default: null
 * @return array the items returned from the youtube api
 */
function get_youtube_video_data(array $videos) {

	$videoIds = list_ids($videos, 'yt_id');

	if (!count($videoIds))
		return false;

	// Process in batches to reduce API calls. 50 is Youtube's pagination limit.
	$chunkedIDs = array_chunk($videoIds, 50, true);

	$return = [];

	foreach ($chunkedIDs as $ids) {

		// Get more information for the videos
		$optParams = array(
			'id' => implode(',', $ids),
		);

		try {
			$results = getYoutubeService()->videos->listVideos('id,snippet,contentDetails,statistics,topicDetails', $optParams);

			$items = $results->items;
			foreach ($items as $item) {
				$i = array_search($item->id, array_column($videos, 'yt_id'));
				if (!is_numeric($i)) continue; // Skip if video ID not in list, shouldn't happen
				$post = to_object($videos[$i]);

				save_youtube_data($post->id, $item);

				$item->post_id = $post->id;
				$return[] = $item;
			}
		} catch (Exception $e) {
			logger('Exception: ' . $e->getMessage());

			return false;
		}
	}

	return $return;
}

/**
 * This runs from the How to Make - Video Editor menu, it will get all selected 
 * information for every video that is fed in. 
 * 
 * $features can include: keywords, duration, image, title, slug, description, excerpt
 *
 * @param array $channels   An array of channels
 * @param array $features An array of features to save, all if null. default: null
 * @return void
 */
function get_youtube_channel_data(array $channels) {

	$channelIds = [];
	foreach ($channels as &$channel) {
		$channel->yt_id = get_field('yt_channel_id', $channel, true);
		$channelIds[] = $channel->yt_id;
	}

	if (!count($channelIds))
		return false;

	// Process in batches to reduce API calls. 50 is Youtube's pagination limit.
	$chunkedIDs = array_chunk($channelIds, 50, true);

	foreach ($chunkedIDs as $ids) {

		// Get more information for the videos
		$optParams = array(
			'id' => implode(',', $ids),
		);

		try {
			$results = getYoutubeService()->channels->listChannels('id,snippet,contentDetails,statistics,topicDetails', $optParams);

			$items = $results->items;
			foreach ($items as $key => $item) {
				$i = array_search($item->id, array_column($channels, 'yt_id'));
				if (!is_numeric($i)) continue; // Skip if ID not in list, shouldn't happen
				$obj = to_object($channels[$i]);

				save_youtube_data($obj->term_id, $item);

				$items[$key]->object_id = $obj->term_id;
			}

			return $items;
		} catch (Exception $e) {
			logger('Exception: ' . $e->getMessage());

			return false;
		}
	}
}

function format_youtube_description($desc) {
	return '<p>' . implode('</p><p>', preg_split('/\n{1,}/', $desc)) . '</p>';
}

function list_used_hooks() {
	add_action('all', function ($tag) {
		global $wp_filter;

		if (!isset($wp_filter[$tag]))
			return;

		logger($wp_filter[$tag]);
	});
}

function empty_error_log() {
	file_put_contents(ini_get('error_log'), '');
}

function save_youtube_data($object_id, $data) {
	// empty_error_log();

	switch ($data->kind) {
		case 'youtube#video':
			$table = 'wp_yt_video_meta';
			$id = 'post_id';
			$type = 'video';
			break;

		case 'youtube#channel':
			$table = 'wp_yt_channel_meta';
			$id = 'object_id';
			$type = 'channel';
			break;

		default:
			return;
	}

	$bulk = BulkAddMeta::get_instance($type);
	$bulk->query("DELETE FROM $table WHERE $id = $object_id");

	$data = get_key_value($data);

	foreach ($data as $key => $value) {
		if (!is_null($value)) {
			$bulk->add($object_id, $key, $value);
		}
	}

	$bulk->push();
}

function get_key_value($obj) {
	$return = (object) [];

	foreach ($obj as $k => $v) {
		if (gettype($v) == 'object') {
			$v = get_key_value($v);

			foreach ($v as $l => $w)
				$return->{"$k.$l"} = $w;

			continue;
		}

		while (strlen($k) > 191)
			$k = preg_replace('/^\w+\./', '', $k);

		$return->$k = $v;
	}

	return $return;
}

function get_video_meta($video_id, $key) {
	global $wpdb;
	$results = $wpdb->get_results(
		"SELECT 
			meta_value
		FROM {$wpdb->videometa}
		WHERE meta_key = '$key' 
		AND post_id = $video_id"
	);

	return count($results) ? $results[0]->meta_value : null;
}

function get_channel_meta($channel_id, $key) {
	global $wpdb;
	$results = $wpdb->get_results(
		"SELECT 
			meta_value
		FROM {$wpdb->channelmeta}
		WHERE meta_key = '$key' 
		AND `object_id` = $channel_id"
	);

	return count($results) ? $results[0]->meta_value : null;
}

function custom_number_format($n, $precision = 1) {
	if ($n < 1000) {
		$n_format = number_format($n);
	} elseif ($n < 1000000) {
		// Anything less than a million
		$n_format = number_format($n / 1000) . 'K';
	} elseif ($n < 1000000000) {
		// Anything less than a billion
		$n_format = number_format($n / 1000000, $precision) . 'M';
	} else {
		// At least a billion
		$n_format = number_format($n / 1000000000, $precision) . 'B';
	}

	return $n_format;
}

	/*	Value				Type	Example	
	* $entityType			String	(user|system), 
    * $event                String
	* entity id				Int		
	* acted on entity type	String	(article, video),
    * value                 String
	* label					String
	* data					Mixed
	*/

    function SaveAnalyticsEvent(
		$entityType, 
		$event, 
		$entityId = null, 
		$actedOnEntityType = null,  
		$value, 
		$label = null, 
		$data = null ) {


	}