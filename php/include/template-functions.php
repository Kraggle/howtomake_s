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


function htm_add_head_stuff() {
	global $template;
	$base = basename($template);

	$uri = get_template_directory_uri();

	$links = (object) [
		"everyPage" => (object) [
			"query" => true,
			"scripts" => [
				(object) [
					"name" => "htm_s-main",
					"src" => "$uri/scripts/main.js",
					"module" => true
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-main",
					"src" => "$uri/styles/main.css"
				]
			]
		],
		"home" => (object) [
			"query" => is_front_page(),
			"styles" => [
				(object) [
					"name" => "htm_s-home",
					"src" => "$uri/styles/home.css"
				]
			]
		],
		"single" => (object) [
			"query" => $base === 'single.php',
			"scripts" => [
				(object) [
					"name" => "htm_s-single",
					"src" => "$uri/scripts/single.js",
					"module" => true
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-single",
					"src" => "$uri/styles/single.css"
				]
			]
		],
		"category" => (object) [
			"query" => $base === 'category.php',
			"scripts" => [
				(object) [
					"name" => "htm_s-category",
					"src" => "$uri/scripts/category.js",
					"module" => true
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-category",
					"src" => "$uri/styles/category.css"
				]
			]
		],
		"search" => (object) [
			"query" => $base === 'page-search.php',
			"scripts" => [
				(object) [
					"name" => "htm_s-search",
					"src" => "$uri/scripts/search.js",
					"module" => true
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-search",
					"src" => "$uri/styles/search.css"
				]
			]
		],
		"channel" => (object) [
			"query" => $base === 'channel.php',
			"scripts" => [
				(object) [
					"name" => "htm_s-channel",
					"src" => "$uri/scripts/channel.js",
					"module" => true
				]
			],
			"styles" => [
				(object) [
					"name" => "htm_s-channel",
					"src" => "$uri/styles/channel.css"
				]
			]
		],
		"page" => (object) [
			"query" => $base === 'page.php',
			"styles" => [
				(object) [
					"name" => "htm_s-page",
					"src" => "$uri/styles/page.css"
				]
			]
		],
		"404" => (object) [
			"query" => $base === '404.php',
			"styles" => [
				(object) [
					"name" => "htm_s-page",
					"src" => "$uri/styles/page.css"
				]
			]
		],
		"contact" => (object) [
			"query" => $base === 'page-contact-us.php',
			"styles" => [
				(object) [
					"name" => "htm_s-page",
					"src" => "$uri/styles/contact.css"
				]
			]
		]
	];

	foreach ($links as $name => $item) {
		if (!$item->query) continue;

		if ($item->scripts) {
			foreach ($item->scripts as $script)
				wp_enqueue_script(($script->module ? 'module-' : '') . $script->name, $script->src);
		}

		if ($item->styles) {
			foreach ($item->styles as $style)
				wp_enqueue_style($style->name, $style->src, false, null);
		}
	}
}

function htm_script_as_module($tag, $handle, $src) {
	if (preg_match('/^module-/', $handle)) {
		$tag = '<script type="module" src="' . esc_url($src) . '" id="' . $handle . '"></script>';
	}

	return $tag;
}
add_filter('script_loader_tag', 'htm_script_as_module', 10, 3);

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
	if ($channel_id) $channel = get_category($channel_id);
	if (!$channel) return;

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

	if ($key === 'link') {
		echo parse_url(get_category_link($channel->term_id), PHP_URL_PATH);
		return;
	}

	if ($key === 'logo') {
		// get the logo from the database
		$imageId = get_term_meta($channel->term_id, 'actual_logo', true);
		// if it's not in the database, get it from youtube
		if (!$imageId) $imageId = get_channel_logo(get_term_meta($channel->term_id, 'yt_channel_id', true), $channel->term_id);
		echo '<img src="' . parse_url(wp_get_attachment_image_url($imageId, 'medium'), PHP_URL_PATH) . '" class="channel-logo" />';
		return;
	}

	if ($key === 'categories') {
		$categories = get_field('video_categories', $channel);

		$links = [];
		foreach ($categories as $cat) {
			$term = get_term($cat);
			$link = parse_url(get_term_link($term, 'video-category'), PHP_URL_PATH);
			$links[] = '<a href="' . $link . '">' . $term->name . '</a>';
		}

		echo join(', ', $links);
		return;
	}

	if ($key == 'social') {
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

		return;
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

	$minutes = get_post_meta($post, 'read_time', true);

	if (!$minutes)
		$minutes = set_read_time($post);

	// error_log(json_encode($minutes));

	return $minutes;
}

function set_read_time($post = null) {
	global $id;
	if (!$post) $post = $id;
	if (!$post) return;

	$minutes = ceil(str_word_count(strip_tags(get_the_content())) / 250);
	add_post_meta($post, 'read_time', $minutes);
	return $minutes;
}

function get_duration($post = null) {
	global $id;
	if (!$post) $post = $id;
	if (!$post) return;

	$api_key = 'AIzaSyByB7ZeVa4qIN9TPeAlgG6tJtkYoT8Xme8';

	if (get_post_type($post) === 'video') {
		$minutes = get_post_meta($post, 'video_duration', true);

		if (!$minutes) {
			$yt = get_post_meta($post, 'youtube_video_id', true);

			// video json data
			$json_result = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id=$yt&key=$api_key");
			$result = json_decode($json_result);

			// video duration data
			if (!count($result->items)) {
				return 0;
			}

			$duration_encoded = $result->items[0]->contentDetails->duration;

			// duration
			$interval = new DateInterval($duration_encoded);
			$minutes = ceil(($interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s) / 60);


			if ($minutes)
				add_post_meta($post, 'video_duration', $minutes);
			else
				$minutes = 0;
		}
	}
	return $minutes;
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
		$title = '<h4 class="more-title">' . $title . '</h4>';
	}

	if (isset($args['classes'])) {
		$classes = $args['classes'];
		if (!is_array($classes))
			$classes = explode(' ', $classes);
	}

	$classes[] = 'more-panel';

	if ($type !== 'default')
		$classes[] = "is-$type";

	if (isset($args['background'])) {
		$classes[] = 'bg-' . $args['background'];
	} ?>

	<div class="<?php echo implode(' ', $classes) ?>">
		<?php if ($type == 'quote') { ?>
			<i class="quote"><?php echo get_font_awesome_icon('quote-right', 'solid') ?></i>
		<?php } ?>
		<?php echo $title ?>
		<div class="more-content">
			<?php echo is_callable($content) ? $content() : $content ?>
		</div>
	</div>

<?php }

/**
 * Used to remove divi shortcodes from pre existing posts and pages.
 * Also removes the domain from any internal links. 
 */
add_action('the_post', function ($post_object) {

	$post_object->post_content = preg_replace('/(\[.{0,2}et_pb[^]]+]|https{0,1}:.{2,4}how\w+.\w{3,4})/m', '', $post_object->post_content);

	return $post_object;
});
