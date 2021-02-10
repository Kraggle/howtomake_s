<?php

/**
 * htm_s functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package htm_s
 */

define('IS_LIVE', $_SERVER['HTTP_HOST'] === 'howtomakemoneyfromhomeuk.com');
if (!defined('IS_DEBUG'))
	define('IS_DEBUG', IS_LIVE ? false : true);

// Replace the version number of the theme on each release.
if (!defined('_S_VERSION'))
	define('_S_VERSION', '1.0.0');

if (!defined('K_YT_API_KEY'))
	define('K_YT_API_KEY', 'AIzaSyByB7ZeVa4qIN9TPeAlgG6tJtkYoT8Xme8');

if (!defined('K_YT_API_KEYS'))
	define('K_YT_API_KEYS', [
		'AIzaSyByB7ZeVa4qIN9TPeAlgG6tJtkYoT8Xme8',
		'AIzaSyDtGJtBPXdcWfBswi3mJSezfoj23Fr2T1A',
		'AIzaSyD7iDUybQmkxls-Ge3kQ_sGHLsNbAxvc00',
	]);

global $htm__s_version;
$htm__s_version = '0.1.11';

global $refreshing_categories;
$refreshing_categories = false;

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

	$post_types = get_post_types(array('public' => true), 'names', 'and');
	foreach ($post_types as $post_type)
		if ($post_type != 'attachment')
			add_option("htm_sitemap_include_$post_type", 1);

	if ($taxonomies = htm_get_taxonomies())
		foreach ($taxonomies as $taxonomy) {
			$name = get_taxonomy($taxonomy)->name;
			add_option("htm_sitemap_include_$name", 1);
		}
}, 20);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
add_action('widgets_init', function () {
	register_sidebar(
		array(
			'name'          => esc_html__('Sidebar', 'htm_s'),
			'id'            => 'sidebar-1',
			'description'   => esc_html__('Add widgets here.', 'htm_s'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
});

/**
 * Helper function for prettying up errors
 * @param string $message
 * @param string $subtitle
 * @param string $title
 */
$htm_s_error = function ($message, $subtitle = '', $title = '') {
	$title = $title ?: __('howtomake_s &rsaquo; Error', 'htm_s');
	$footer = '<a href="#"></a>';
	$message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p><p>{$footer}</p>";
	wp_die($message, $title);
};

/**
 * Required files
 *
 * The mapped array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 */
array_map(function ($file) use ($htm_s_error) {
	$file = "php/include/{$file}.php";
	if (!locate_template($file, true, true)) {
		$htm_s_error(sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file), 'File not found');
	}
}, [
	'custom-header', 'template-tags', 'template-functions', 'customizer', 'custom-posts',
	'shortcodes', 'other-functions', 'forms', 'ajax-calls', 'admin-menu-tool', 'bulk-functions'
]);

add_action('init', function () {
	new HTM_Admin_Menu();
});

/**
 * Template Hierarchy should search for .blade.php files
 */
$acceptedTemplates = [];
foreach ([
	'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'home',
	'frontpage', 'page', 'paged', 'search', 'single', 'singular', 'attachment', 'embed'
] as $type) {
	add_filter("{$type}_template_hierarchy", function ($templates) {
		if (in_array('category.php', $templates)) {
			array_unshift($templates, 'page-search.php');
		} elseif (in_array('taxonomy-video-category.php', $templates)) {
			array_unshift($templates, 'page-search.php');
		} elseif (in_array('front-page.php', $templates)) {
			array_unshift($templates, 'page-home.php');
		} elseif (in_array('taxonomy-video-channel.php', $templates)) {
			array_unshift($templates, 'channel.php');
		}

		$templates = array_map(function ($value) {
			global $acceptedTemplates;
			$acceptedTemplates[] = str_replace('.php', '', $value);
			return "./views/$value";
		}, $templates);

		add_filter('body_class', function ($classes) {
			global $acceptedTemplates;
			return array_merge($classes, $acceptedTemplates);
		});

		// error_log('Accepted templates:' . json_encode($templates));

		return $templates;
	});
}

/**
 * Enqueue scripts and styles.
 */
add_action('wp_enqueue_scripts', function () {
	// global $template;
	// error_log('Using template: ' . basename($template));

	wp_enqueue_style('page_loader', get_template_directory_uri() . "/styles/loader.css");
	// wp_enqueue_script('greensock', "//cdnjs.cloudflare.com/ajax/libs/gsap/3.5.1/gsap.min.js", array(), null, true);

	if (is_single() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}, 100);

/**
 * Load Jetpack compatibility file.
 */
// if (defined('JETPACK__VERSION')) {
// 	require get_template_directory() . '/php/include/jetpack.php';
// }

// Show Custom Fields on editor
add_filter('acf/settings/remove_wp_meta_box', '__return_false');

add_action('post_updated', function ($id, $post) {
	$link = get_permalink($id);

	if (!preg_match('/-autosave-/', $link))
		htm_set_permalink($id, $link, $post);
}, 20, 2);

add_filter( 'upload_mimes', 'my_mime_types', 1, 1 );
function my_mime_types( $mime_types ) {
  $mime_types['jpegcharsetbinary'] = 'image/jpegcharsetbinary';     // Adding .svg extension

  return $mime_types;
}


