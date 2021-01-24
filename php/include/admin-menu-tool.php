<?php

/**
 * This class is used to add additional admin menus for use in the background of the site.
 * 
 * @package howtomake_s
 * @author  kraggle
 */

global $menu_items;
$menu_items = [
	(object) [
		'name' => 'Media Editor',
		'content' => function () { ?>

	<div class="ks-setting-box">
		<span class="ks-name">Missing Authors</span>
		<span class="ks-desc">Used to add authors to media that don't have one already set.</span>
		<div class="flex row">
			<label for="missingCount" class="ks-label">Quantity</label>
			<input id="missingCount" class="ks-input" type="number" readonly />
			<div class="spacer"></div>
			<button id="getMissingAuthors" class="ks-button" action="get_missing_authors" count="missingCount" other="setMissingAuthors">Get Quantity</button>
		</div>
		<button id="setMissingAuthors" class="ks-button" action="set_missing_authors" other="getMissingAuthor">Set Missing</button>
	</div>

	<div class="ks-setting-box">
		<span class="ks-name">Refresh Categories</span>
		<span class="ks-desc">Correctly sets all categories to media. Adds unused images to 'Unused' category for deletion.</span>
		<div class="flex row">
			<label for="mcCount" class="ks-label">Quantity</label>
			<input id="mcCount" class="ks-input" type="number" readonly />
			<div class="spacer"></div>
			<button id="getMC" class="ks-button" action="get_missing_category" count="mcCount" other="setMC">Get Quantity</button>
		</div>
		<button id="setMC" class="ks-button" action="set_missing_category" other="getMC" repeat=10>Set Categories</button>
	</div>

	<div class="ks-setting-box">
		<span class="ks-name">Delete Unused Media</span>
		<span class="ks-desc">Delete all media that is not used anywhere on the site. Be warned, this cannot be undone. Make a backup first. Also, you can view what is to be deleted from 'Media' > 'Assistant' and filter by 'Unused'.</span>
		<div class="flex row">
			<label for="duCount" class="ks-label">Quantity</label>
			<input id="duCount" class="ks-input" type="number" readonly />
			<div class="spacer"></div>
			<button id="getDU" class="ks-button" action="get_media_to_delete" count="duCount" other="setDU">Get Quantity</button>
		</div>
		<button id="setDU" class="ks-button red" action="set_media_to_delete" other="getDU" repeat=1>Delete Media</button>
	</div>

	<div class="ks-setting-box">
		<span class="ks-name">Regenerate Thumbnails</span>
		<span class="ks-desc">Goes through all image attachments, deletes unused image sizes and regenerates those that are missing. This also uses the category sizes, so you don't get loads of images that are never going to be used.</span>
		<div class="flex row">
			<label for="rtCount" class="ks-label">Quantity</label>
			<input id="rtCount" class="ks-input" type="number" readonly />
			<div class="spacer"></div>
			<button id="getRT" class="ks-button" action="get_regenerate_thumbnails" count="rtCount" other="setRT">Get Quantity</button>
		</div>
		<button id="setRT" class="ks-button" action="set_regenerate_thumbnails" other="getRT" repeat=1>Regenerate</button>
	</div>
<?php }
	], (object) [
		'name' => 'Page Settings',
		'content' => function () { ?>

	<div class="ks-setting-box">
		<span class="ks-name">HTML Sitemap</span>
		<span class="ks-desc">Select which types you would like to generate in the HTML Sitemap.</span>
		<div class="ks-setting-list">
			<?php $post_types = get_post_types(array('public' => true), 'names', 'and');

			foreach ($post_types  as $post_type) {
				if ($post_type != 'attachment') {
					$post_typeO = get_post_type_object($post_type);
					$post_type_name = $post_typeO->label;
					$include = "include_{$post_type}"; ?>

					<div class="check-box">
						<label for="<?php echo $include; ?>">
							<?php htm_checkbox($include, get_option("htm_sitemap_$include")); ?>
							<div>
								<span class="ks-check-name"><?php echo $post_type_name; ?></span>
								<p class="ks-help"><?php _e('Check to include', 'howtomake_s'); ?></p>
							</div>
						</label>
					</div>
				<?php }
			}

			// Add taxonomies 
			$taxonomies = htm_get_taxonomies();

			// If there are any taxonomies
			if ($taxonomies) {

				// Loop trough all
				foreach ($taxonomies as $taxonomy) {

					// Get information of current one
					$tax = get_taxonomy($taxonomy);
					$include = "include_{$tax->name}"; ?>

					<div class="check-box">
						<label for="<?php echo $include; ?>">
							<?php htm_checkbox($include, get_option("htm_sitemap_$include")); ?>
							<div>
								<span class="ks-check-name"><?php echo $tax->label; ?></span>
								<p class="ks-which">(<?php echo $tax->name; ?>)</p>
								<p class="ks-help"><?php _e('Check to include', 'howtomake_s'); ?></p>
							</div>
						</label>
					</div>
			<?php }
			} ?>
		</div>
		<button id="setSitemap" type="save" class="ks-button" action="set_sitemap_settings">Save</button>
	</div>
<?php }
	]
];

class HTM_Admin_Menu {

	private $name = 'How to Make';
	private $slug = 'htm-admin-menu';
	private $dash;
	private $capability = 'administrator';
	private $pages;

	function __construct() {
		$this->pages = (object) [];

		add_action('admin_menu', array($this, 'init_menu'));
	}

	function init_menu() {
		$this->dash = "{$this->slug}-dashboard";

		wp_enqueue_script('module-htm-admin-js', get_template_directory_uri() . '/scripts/admin.js');
		wp_enqueue_style('htm-admin-css', get_template_directory_uri() . '/styles/admin.css');

		add_menu_page($this->name, $this->name, $this->capability, $this->dash, array($this, 'dashboard_page'), 'data:image/svg+xml,' . HTM_MENU_ICON);
		add_submenu_page($this->dash, "Dashboard", "Dashboard", $this->capability, $this->dash, array($this, 'dashboard_page'));

		global $menu_items;
		foreach ($menu_items as $item) {
			$this->add_menu($item->name, $item->content);
		}
	}

	function add_menu($name, $content = '') {

		$slug = str_replace(' ', '-', strtolower($name));
		$menu_slug = "{$this->slug}-{$slug}";

		$this->pages->$name = (object) [
			'name' => $name,
			'slug' => $slug,
			'menu_slug' => $menu_slug,
			'content' => $content
		];

		add_submenu_page($this->dash, $name, $name, $this->capability, $menu_slug, array($this, 'build_page'));
	}

	function page_before($type) {

		global $title;
		printf(
			'<div class="ks-box" data-nonce="%5$s">
				<div class="ks-head">
					<div class="ks-icon">%1$s</div>
					<span class="ks-title">%2$s</span>
					<span class="ks-sub">%3$s</span>
				</div>
				<div class="ks-content %4$s">',
			urldecode(HTM_MENU_ICON),
			$this->name,
			$title,
			$type,
			wp_create_nonce('settings_nonce')
		);
	}

	function page_after() {
		echo '</div>
			<div class="ks-footer">
				<div class="ks-progress">
					<div class="ks-progress-back"></div>
					<div class="ks-progress-number"></div>
					<div class="ks-progress-time"></div>
				</div>
			</div>
		</div>';
	}

	function dashboard_page() {
		$this->page_before('dashboard');

		$this->page_after();
	}

	function build_page() {
		global $title;
		$page = $this->pages->$title;
		$this->page_before($page->slug);

		if (is_callable($page->content)) {
			call_user_func($page->content);
		} else {
			echo $page->content;
		}

		$this->page_after();
	}
}

if (!defined('HTM_MENU_ICON')) {
	define('HTM_MENU_ICON', "%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 250 250'%3E%3Cpath d='M224.5 62.9L204 51l5.3-3.1-10.1-5.8-5.2 3-40-23.3 5.1-3-10-5.8-5.1 2.9L125 5l-18.3 10.6-5.3-3-10.1 5.8 5.3 3.1-40 23-5.5-3.2L41 47.1l5.5 3.2-21 12.1 19.9 11.5-5.4 3.2 10 5.8 5.4-3.1L95.6 103l-5.6 3.3 10 5.8 5.6-3.3 19.3 11.2 19.4-11.1 5.6 3.3 10-5.8-5.6-3.2 40.3-23.1 5.5 3.2 10-5.8-5.4-3.1 19.8-11.5zm-55.1 10.2l-24.8-14.4L119.7 73l24.8 14.4-15.8 9.1L67.2 61 83 51.9l24 13.8 24.8-14.3-23.9-13.9 15.9-9.2L185.3 64l-15.9 9.1z' fill='%23cdcdcd'/%3E%3Cpath d='M125 167v-11.6l-5.8-3.3v-22.2l-19.3-11.1.1.1V112l-10-5.8v6.8L50.1 90v-7.1l-10-5.8v7.1L19.7 72.4v25.1l-5.8-3.3v11.6l5.8 3.3v46.3l-5.8-3.3v11.6l5.8 3.3v20.3L40 199v6.7l10 5.8v-6.7l40 23.1v6.8l10 5.7v-6.7l19.2 11.1v-23.2l5.8 3.3v-11.6l-5.8-3.3v-46.3l5.8 3.3zm-29-13.4L77.7 143v55.8l-16.4-9.5v-55.8L43 123v-15.1l53 30.6v15.1z' fill='%239b9b9b'/%3E%3Cpath d='M236.1 104.6V93l-5.8 3.3V72.9l-20.1 11.5v-7.1l-10 5.8v7.1l-40.1 23v-6.9l-10 5.8v6.8l-19.3 11v22.2l-5.8 3.3V167l5.8-3.3V210l-5.8 3.3v11.6l5.8-3.3V245l19.3-11v6.4l10-5.7v-6.4l40.1-22.8v6.2l10-5.7v-6.2l20.1-11.4v-22.5l5.8-3.3V151l-5.8 3.3V108l5.8-3.4zM211.4 177l-14.2 8.1-.1-32.6-11.7 39.3-10.3 5.9-11.7-25.5v32.2l-14 8v-71.1l12.7-7.2 18.4 38.1 18.3-59.1 12.6-7.2V177z' fill='%23acacac'/%3E%3C/svg%3E");
}
