<?php

/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package htm_s
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function htm_s_body_classes($classes) {
	// Adds a class of hfeed to non-singular pages.
	if (!is_singular()) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if (!is_active_sidebar('sidebar-1')) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter('body_class', 'htm_s_body_classes');

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function htm_s_pingback_header() {
	if (is_singular() && pings_open()) {
		printf('<link rel="pingback" href="%s">', esc_url(get_bloginfo('pingback_url')));
	}
}
add_action('wp_head', 'htm_s_pingback_header');



function dialogShouldShow() {
	$popupOptions = get_field('promo_popup', 'option');

	if (isset($popupOptions['enabled'][0]) && $popupOptions['enabled'][0] == '1') {

		$uris = explode("\n", $popupOptions['uri_list']);

		$uriMatches = false;
		foreach ($uris as $uri) {
			$uri = trim(strtolower($uri));

			if (!empty($uri) && strpos($_SERVER['REQUEST_URI'], $uri) === 0) {
				$uriMatches = true;
				break;
			}
		}

		if (
			!is_user_logged_in() &&
			($popupOptions['restrict_pages'] == 'all' ||
				($popupOptions['restrict_pages'] == 'white' && $uriMatches) ||
				($popupOptions['restrict_pages'] == 'black' && !$uriMatches))
		)
			return true;
	}
	return false;
}




/**
 * Retrieves the channel information for the current video and prints it.
 *
 * @param int|null $post Post ID or null.
 * @param string   $key  The key to retrieve, defaults as 'name'.
 * @return null    Echo the value if found, null otherwise.
 */
function the_channel($post = null, $key = 'name') {
	global $id;
	if (!$post) $post = $id;
	if (!$post) return;

	$channel = (object) get_the_terms($post, 'video-channel')[0];

	htm_s_echo_channel($channel, $key);
}

/**
 * Retrieves the channel information for the current channel and prints it.
 *
 * @param int|null $channel_id Channel ID or null.
 * @param string   $key  The key to retrieve, defaults as 'name'.
 * @return null    Echo the value if found, null otherwise.
 */
function get_channel($channel_id = null, $key = 'name') {
	$channel = get_queried_object();
	if ($channel_id) $channel = get_term($channel_id, 'video-channel');
	if (!$channel || !($channel instanceof WP_Term)) return;

	htm_s_echo_channel($channel, $key);
}

function htm_s_echo_channel($channel, $key) {

	$keys = [
		'term_id',
		'name',
		'slug',
		'term_group',
		'term_taxonomy_id',
		'taxonomy',
		'description',
		'parent',
		'count'
	];

	if (in_array($key, $keys)) {
		echo $channel->$key;
		return;
	}

	switch ($key) {
		case 'link':
			echo parse_url(get_category_link($channel->term_id), PHP_URL_PATH);
			break;

		case 'logo':
			// get the logo from the database
			$imageId = get_term_meta($channel->term_id, 'actual_logo', true);

			// if it's not in the database, get it from youtube
			if (!$imageId) $imageId = get_channel_logo(get_term_meta($channel->term_id, 'yt_channel_id', true), $channel->term_id);
			echo '<img src="' . wp_get_attachment_image_url($imageId, 'channel') . '" class="channel-logo" />';
			break;

		case 'categories':
		case 'category':
			// gets all categories from all videos from the given channel
			$categories = get_results(
				"SELECT
				t2.term_id,
				t2.name,
				t2.slug
				FROM wp_term_relationships tr1
				INNER JOIN wp_posts p
					ON tr1.object_id = p.ID
				INNER JOIN wp_term_taxonomy tt
					ON tr1.term_taxonomy_id = tt.term_taxonomy_id
				INNER JOIN wp_term_relationships tr2
					ON tr2.object_id = p.ID
				INNER JOIN wp_terms t1
					ON t1.term_id = tr2.term_taxonomy_id
				INNER JOIN wp_terms t2
					ON t2.term_id = tr1.term_taxonomy_id
				WHERE tt.taxonomy = 'video-category'
				AND tr2.term_taxonomy_id = $channel->term_id
				GROUP BY t2.term_id,
						t2.name,
						t2.slug
				ORDER BY t2.name"
			);

			$links = [];
			foreach ($categories as $cat) {
				$link = parse_url(get_term_link($cat->slug, 'video-category'), PHP_URL_PATH);
				$links[] = '<a class="link" href="' . $link . '">' . $cat->name . '</a>';
			}

			echo implode('', $links);
			break;

		case 'social':
			$social = (object) get_field('social_accounts', $channel);

			$social->youtube_url = 'https://www.youtube.com/channel/' .
				get_field('yt_channel_id', $channel->term_id);

			$links = (object) [
				'youtube_url' => (object) [
					'class' => 'type-brands svg-youtube color-a8240f',
					'name' => 'YouTube'
				],
				'facebook_url' => (object) [
					'class' => 'type-brands svg-facebook-f color-39579a',
					'name' => 'Facebook'
				],
				'instagram_url' => (object) [
					'class' => 'type-brands svg-instagram color-517fa4',
					'name' => 'Instagram'
				],
				'twitter_url' => (object) [
					'class' => 'type-brands svg-twitter color-01aced',
					'name' => 'Twitter'
				],
				'pinterest_url' => (object) [
					'class' => 'type-brands svg-pinterest-p color-c61d26',
					'name' => 'Pinterest'
				],
				'website_url' => (object) [
					'class' => 'type-solid svg-link color-31a237',
					'name' => 'Website'
				]
			];

			foreach ($social as $key => $url) {
				if ($url) {
					printf(
						'<a href="%1$s" class="fa-icon %2$s" title="%3$s"
						target="_blank"></a>',
						$url,
						$links->$key->class,
						"Follow on {$links->$key->name}"
					);
				}
			}
			break;

		default:
			break;
	}
}

function load_template_part($template_name, $part_name = null) {
	ob_start();
	get_template_part($template_name, $part_name);
	$var = ob_get_contents();
	ob_end_clean();
	return $var;
}

function get_font_awesome_icon($name, $type = 'regular') {
	ob_start();
	include(get_template_directory() . "/assets/fonts/font-awesome/$type/$name.svg");
	$var = ob_get_contents();
	ob_end_clean();
	return $var;
}


function get_read_time($post = null) {
	global $id;
	if (!$post) $post = $id;
	if (!$post) return;

	$seconds = get_post_meta($post, 'duration_seconds', true);

	if (!$seconds) $seconds = set_read_time($post);

	return ceil($seconds / 60);
}

function set_read_time($post = null) {
	global $id;
	if (!$post) $post = $id;
	if (!$post) return;

	$minutes = ceil(str_word_count(strip_tags(get_the_content(null, false, $post))) / 250);
	add_post_meta($post, 'duration_seconds', $minutes * 60);
	return $minutes;
}

function get_duration($post = null, $format = 'minutes') {
	global $id;
	if (!$post) $post = $id;
	if (!$post || get_post_type($post) !== 'video') return 0;

	$duration = get_post_meta($post, 'video_duration_raw', true);

	if (!$duration) {
		$yt = get_post_meta($post, 'youtube_video_id', true);

		// video json data
		$json_result = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id=$yt&key=" . K_YT_API_KEY);
		$result = json_decode($json_result);

		// video duration data
		if (!count($result->items)) return 0;

		$duration = $result->items[0]->contentDetails->duration;

		if ($duration)
			add_post_meta($post, 'video_duration_raw', $duration);
		else
			return 0;
	}

	$interval = new DateInterval($duration);

	switch ($format) {
		case 'minutes':
			return ceil(($interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s) / 60);
		default:
			$hrs = $interval->days * 24 + $interval->h;
			$min = $interval->i;
			$sec = str_pad($interval->s, 2, '0', STR_PAD_LEFT);

			return $hrs ? "$hrs:" . str_pad($min, 2, '0', STR_PAD_LEFT) . ":$sec" : "$min:$sec";
	}
}

function get_attachment_image_by_slug($slug, $size = 'thumbnail') {
	$args = array(
		'post_type' => 'attachment',
		'name' => sanitize_title($slug),
		'posts_per_page' => 1,
		'post_status' => 'inherit',
	);
	$_header = get_posts($args);
	$header = $_header ? array_pop($_header) : null;
	return $header ? wp_get_attachment_image($header->ID, $size) : '';
}

function get_attachment_image_url_by_slug($slug, $size = 'thumbnail') {
	$args = array(
		'post_type' => 'attachment',
		'name' => sanitize_title($slug),
		'posts_per_page' => 1,
		'post_status' => 'inherit',
	);
	$_header = get_posts($args);
	$header = $_header ? array_pop($_header) : null;
	return $header ? wp_get_attachment_image_url($header->ID, $size) : '';
}

/**
 * Create a more panel, used on the right side bar of the pages and posts
 *
 * @param string|function $content  A string containing the content of the panel or a function 
 *                            that returns the string value.
 * @param string          $title    The title you want for the panel. Default empty string.
 * @param array           $args     An array of arguments. Default empty array.
 * @return null           Echo the resulting panel if no errors, null otherwise.
 */
function do_more_panel($content, $title = '', $args = array()) {
	if (!isset($content) || !$content) return null;

	$type = 'default';
	if (isset($args['type']))
		$type = $args['type'];

	if ($title) {
		$el = $args['title_element'];
		$title = "<$el class='more-title'>$title</$el>";
	}

	if (isset($args['classes'])) {
		$classes = $args['classes'];
		if (!is_array($classes))
			$classes = explode(' ', $classes);
	}

	$classes[] = 'more-panel';
	$classes[] = "is-$type";
	$classes[] = $args['background'];

	if ($args['sticky'])
		$classes[] = 'make-sticky';

?>

	<div class="<?= implode(' ', $classes) ?>">
		<?php if ($type == 'quote') { ?>
			<i class="quote"><?= get_font_awesome_icon('quote-right', 'solid') ?></i>
		<?php } ?>
		<?= $title ?>
		<div class="more-content">
			<?= apply_filters('other_content', is_callable($content) ? $content() : $content) ?>
		</div>
	</div>

<?php }

/**
 * Used to remove divi shortcodes from pre existing posts and pages.
 * Also removes the domain from any internal links. 
 */
add_action('the_post', function ($post) {

	$post->post_content = preg_replace('/(\[.{0,2}et_pb[^]]+]|https{0,1}:.{2,4}how\w+.\w{3,4})/', '', $post->post_content);

	return $post;
});


function get_channel_logo($term_id) {
	if (!$term_id) return;

	$ytId = get_term_meta($term_id, 'yt_channel_id', true);
	if (!$ytId) return;

	// get the logo from the database
	$imageId = get_term_meta($term_id, 'actual_logo', true);

	if (!$imageId) {

		$json_result = file_get_contents("https://www.googleapis.com/youtube/v3/channels?part=snippet&id=$imageId&key=" . K_YT_API_KEY);
		$result = json_decode($json_result);

		if ($result->items && count($result->items)) {
			$thumb = $result->items[0]->snippet->thumbnails->high;

			include_once(ABSPATH . 'wp-admin/includes/image.php');

			$imageType = end(explode('/', getimagesize($thumb->url)['mime']));
			$fileName = date('dmY') . (int) microtime(true) . '.' . $imageType;

			$uploadFile = wp_upload_dir()['path'] . '/' . $fileName;
			$saveFile = fopen($uploadFile, 'w');
			fwrite($saveFile, file_get_contents($thumb->url));
			fclose($saveFile);

			$fileType = wp_check_filetype(basename($fileName), null);
			$attachment = array(
				'post_mime_type' => $fileType['type'],
				'post_title' => $fileName,
				'post_content' => '',
				'post_status' => 'inherit'
			);

			$imageId = wp_insert_attachment($attachment, $uploadFile);
			$fullSizePath = get_attached_file(get_post($imageId)->ID);
			$attachData = wp_generate_attachment_metadata($imageId, $fullSizePath);
			wp_update_attachment_metadata($imageId, $attachData);

			add_term_meta($termId, 'actual_logo', $imageId);
		}
	}

	return wp_get_attachment_image_url($imageId, 'channel');
}



function content_schema_meta() {
	$post = get_post();

	if (!($post instanceof WP_Post))
		return '';

	ob_start(); ?>

	<div class="hidden-meta">
		<meta itemprop="datePublished" content="<?= date('Y-m-d', strtotime($post->post_date)) ?>">
		<meta itemprop="dateModified" content="<?= date('Y-m-d', strtotime($post->post_modified)) ?>">
		<meta itemprop="mainEntityOfPage" content="<?= get_the_permalink() ?>">

		<div class="hidden-meta" itemscope itemprop="publisher" itemtype="https://schema.org/Organization">
			<meta itemprop="name" content="How to Make Money From Home Ltd">
			<div class="hidden-meta" itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
				<meta itemprop="url" content="<?= get_template_directory_uri() ?>/assets/images/logo.png">
				<meta itemprop="width" content="300">
				<meta itemprop="height" content="182">
			</div>
		</div>

		<div class="hidden-meta" itemprop="author" itemscope itemtype="https://schema.org/Person">
			<meta itemprop="name" content="<?= get_the_author() ?>">
			<meta itemprop="url" content="<?= get_author_posts_url(get_the_author_meta('ID')) ?>">
		</div>
	</div>

<?php $content = ob_get_contents();
	ob_end_clean();

	return $content;
}

/**
 * Display the video excerpt.
 */
function the_video_excerpt($max_length = 200) {

	/**
	 * Filters the displayed video excerpt.
	 *
	 * @see get_the_video_excerpt()
	 *
	 * @param string $post_excerpt The post excerpt.
	 */
	echo apply_filters('the_video_excerpt', get_the_video_excerpt(null, $max_length));
}

/**
 * Retrieves the video excerpt.
 *
 * @param int|WP_Post $post Optional. Post ID or WP_Post object. Default is global $post.
 * @return string Video excerpt.
 */
function get_the_video_excerpt($post = null, $max_length = 200) {
	$post = get_post($post);
	if (empty($post)) {
		return '';
	}

	if (post_password_required($post)) {
		return __('There is no excerpt because this is a protected post.');
	}

	$doc = phpQuery::newDocument($post->post_content);
	$content = '';
	$length = 0;
	foreach ($doc['p'] as $p) {
		$text = pq($p)->getString()[0];
		$len = strlen($text);
		if ($length + $len < $max_length) {
			$length += $len;
			$content .= "<p>$text</p>";
		} else
			break;
	}

	/**
	 * Filters the retrieved video excerpt.
	 *
	 * @param string  $post_excerpt The video excerpt.
	 * @param WP_Post $post         Video object.
	 */
	return apply_filters('get_the_video_excerpt', $content, $post);
}
