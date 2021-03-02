<?php


add_action('after_setup_theme', function () {
	/**
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on htm_s, use a find and replace
	 * to change 'htm_s' to the name of your theme in all the template files.
	 */
	load_theme_textdomain('htm_s', get_template_directory() . '/languages');

	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');

	/**
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support('title-tag');

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'primary-menu' => __('Primary Navigation', 'htm_s'),
			'social-menu' => __('Social Menu', 'htm_s'),
			'footer-nav-1' => __('Footer Navigation 1', 'htm_s'),
			'footer-nav-2' => __('Footer Navigation 2', 'htm_s'),
		)
	);

	/**
	 * Enable support for Post Thumbnails on posts and pages.
	 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	 */
	add_theme_support('post-thumbnails');

	/**
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support(
		'html5',
		[
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		]
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	/**
	 * Use main stylesheet for visual editor
	 */
	add_editor_style('./styles/main.css');

	remove_image_size('1536x1536');
	remove_image_size('2048x2048');

	add_image_size_category('thumbnail', 'content-images', 150);
	add_image_size_category('medium', ['post-featured-images', 'video-featured-images', 'content-images'], 550);
	add_image_size_category('medium_large', ['post-featured-images', 'video-featured-images', 'content-images'], 700);
	add_image_size_category('large', ['post-featured-images', 'video-featured-images', 'content-images'], 1080);
	add_image_size_category('featured', ['post-featured-images', 'video-featured-images'], 1080, 608, true);
	add_image_size_category('result', ['post-featured-images', 'video-featured-images'], 350, 197, true);
	add_image_size_category('menu', ['post-featured-images', 'video-featured-images'], 250, 141, true);
	add_image_size_category('related', ['post-featured-images', 'video-featured-images'], 150, 84, true);
	add_image_size_category('post', 'content-images', 850);
	add_image_size_category('small', 'content-images', 350);
	add_image_size_category('channel', 'video-channel-icons', 120, 120, true);
	add_image_size_category('tiny', 'video-channel-icons', 60, 60, true);

	// logger(get_all_image_sizes());

	add_filter('image_size_names_choose', function ($sizes) {
		return array_merge($sizes, array(
			'post' => __('Content Images')
		));
	});

	/**
	 * Used to regenerate images when the category is changed. So we are only keeping 
	 * a minimal amount of images in the uploads folder.
	 * 
	 * @codingStandardsIgnoreStart
	 */
	add_action('set_object_terms', function ($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
		global $refreshing_categories;
		// logger($refreshing_categories, $taxonomy);
		if (!$refreshing_categories && $taxonomy == 'attachment_category')
			generate_category_thumbnails($object_id);
	}, 10, 6);

	/**
	 * Used when generating the new images to filter out the image sizes we don't want. 
	 * This uses the categories added with add_image_size_catgory() matching with the
	 * image categories.
	 */
	add_filter('intermediate_image_sizes_advanced', function ($new_sizes, $image_meta, $attachment_id) {
		global $avoid_other_sizes;
		// logger($avoid_other_sizes);
		if ($avoid_other_sizes)
			return array();

		$terms = wp_get_object_terms($attachment_id, 'attachment_category');
		return length($terms) == 0 ? $new_sizes : get_image_sizes_for_attachment($attachment_id);
	}, 10, 3);

	// @codingStandardsIgnoreEnd

	$post_types = get_post_types(array('public' => true), 'names', 'and');
	foreach ($post_types as $post_type)
		if ($post_type != 'attachment')
			add_option("htm_sitemap_include_$post_type", 1);

	if ($taxonomies = htm_get_taxonomies())
		foreach ($taxonomies as $taxonomy) {
			$name = get_taxonomy($taxonomy)->name;
			add_option("htm_sitemap_include_$name", 1);
		}

	global $wpdb;
	$wpdb->{'videometa'} = "{$wpdb->prefix}yt_video_meta";
	$wpdb->{'channelmeta'} = "{$wpdb->prefix}yt_channel_meta";

	// save_youtube_data(31906, json_decode(file_get_contents(get_php_includes() . 'videoData.json'))->items[0]);

	add_filter('other_content', 'do_shortcode');
}, 20);