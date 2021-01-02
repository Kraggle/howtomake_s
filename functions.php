<?php

global $htm__s_version;
$htm__s_version = '0.1.10';

/**
 * htm_s functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package htm_s
 */

if (!defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.0');
}

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
}, 20);

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
// function htm_s_content_width() {
// 	$GLOBALS['content_width'] = apply_filters('htm_s_content_width', 640);
// }
// add_action('after_setup_theme', 'htm_s_content_width', 0);

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
	$title = $title ?: __('HowToMake_S &rsaquo; Error', 'hmt_s');
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
	'custom-header', 'template-tags', 'template-functions', 'customizer',
	'custom-posts', 'shortcodes', 'other-functions', 'forms', 'ajax-calls'
]);

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

// add_action('template_redirect', function () {

// 	global $wp_query;
// 	$query = $wp_query->query;

// 	if (array_key_exists('video-category', $query)) {
// 		$cat = $query['video-category'];

// 		wp_safe_redirect(site_url("/search?type=video~$cat"));
// 		exit();
// 	} elseif (array_key_exists('category_name', $query)) {
// 		$cat = $query['category_name'];

// 		wp_safe_redirect(site_url("/search?type=post~$cat"));
// 		exit();
// 	}
// });



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
 * Implement the Custom Header feature.
 */
// require get_template_directory() . '/php/include/custom-header.php';

/**
 * Custom template tags for this theme.
 */
// require get_template_directory() . '/php/include/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
// require get_template_directory() . '/php/include/template-functions.php';

/**
 * Customizer additions.
 */
// require get_template_directory() . '/php/include/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/php/include/jetpack.php';
}

// Show Custom Fields on editor
add_filter('acf/settings/remove_wp_meta_box', '__return_false');