<?php

include('vendor/autoload.php');

/**
 * htm_s functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package htm_s
 */
/* cSpell:disable */
define('IS_LIVE', $_SERVER['HTTP_HOST'] === 'howtomakemoneyfromhomeuk.com' || $_SERVER['HTTP_HOST'] === 'htm.kraggle.co.uk');
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
/* cSpell:enable */
global $htm__s_version;
$htm__s_version = '0.1.25';

global $refreshing_categories;
$refreshing_categories = false;

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
	'enqueue', 'filters', 'media', 'widgets', 'template-tags', 'other-functions', 'template-functions', 'customizer', 'custom-posts', 'shortcodes', 'forms', 'import-classes', 'ajax-calls', 'admin-menu-tool', 'mail', 'contact-form-7', 'option-pages'
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
 * Load Jetpack compatibility file.
 */
// if (defined('JETPACK__VERSION')) {
// 	require get_template_directory() . '/php/include/jetpack.php';
// }

add_action('post_updated', function ($id, $post) {
	$link = get_permalink($id);

	if (!preg_match('/-autosave-/', $link))
		htm_set_permalink($id, $link, $post);
}, 20, 2);

add_action('acf/render_field', function ($field) {
	if (in_array($field['_name'], ['existing_panel', 'existing_content', 'existing_panels'])) { ?>
		<button class="edit">Edit</button>
	<?php }
});

add_action('admin_footer', function () { ?>
	<p class="admin-page-nonce" nonce="<?= wp_create_nonce('admin_nonce') ?>"></p>
<?php });
