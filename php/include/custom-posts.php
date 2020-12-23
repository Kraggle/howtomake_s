<?php

add_action(
	'init',
	function () {
		register_post_type(
			'video',
			[
				'labels'              => [
					'name'               => _x('Videos', 'post type general name', 'boilerplate'),
					'singular_name'      => _x('Video', 'post type singular name', 'boilerplate'),
					'menu_name'          => _x('Videos', 'admin menu', 'boilerplate'),
					'name_admin_bar'     => _x('Video', 'add new on admin bar', 'boilerplate'),
					'add_new'            => _x('Add New', 'optional_extras', 'boilerplate'),
					'add_new_item'       => __('Add New Video', 'boilerplate'),
					'new_item'           => __('New Video', 'boilerplate'),
					'edit_item'          => __('Edit Video', 'boilerplate'),
					'view_item'          => __('View Video', 'boilerplate'),
					'all_items'          => __('Videos', 'boilerplate'),
					'search_items'       => __('Search Videos', 'boilerplate'),
					'parent_item_colon'  => __('Parent Videos:', 'boilerplate'),
					'not_found'          => __('No Videos found.', 'boilerplate'),
					'not_found_in_trash' => __('No Videos found in Trash.', 'boilerplate'),
				],
				'supports'            => ['title', 'thumbnail', 'comments'], // 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'

				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 20,
				'menu_icon'           => 'dashicons-format-video',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'map_meta_cap'        => true,
				'capabilities'        => ['edit_posts'],
				'yarpp_support'       => true
			]
		);

		register_taxonomy(
			'video-channel',
			'video',
			array(
				'hierarchical' => false,
				'label' => 'Channels',
				'query_var' => true,
				'show_ui' => true,
				'public' => true,
				'publicly_queryable' => true,
				'has_archive'        => true,
				'rewrite' => array(
					'slug' => 'channel', // This controls the base slug that will display before each term
					'with_front' => false // Don't display the category base before 
				)
			)
		);

		register_taxonomy(
			'video-category',
			'video',
			array(
				'hierarchical' => false,
				'label' => 'Categories',
				'query_var' => true,
				'show_ui' => true,
				'public' => true,
				'publicly_queryable' => true,
				'has_archive'        => true,
				'rewrite' => array(
					'slug' => 'category', // This controls the base slug that will display before each term
					'with_front' => false // Don't display the category base before
				)
			)
		);
	}
);

function filter_cars_by_taxonomies($post_type) {

	// Apply this only on a specific post type
	if ('video' !== $post_type)
		return;

	// A list of taxonomy slugs to filter by
	$taxonomies = array('video-channel', 'video-category');

	foreach ($taxonomies as $taxonomy_slug) {

		// Retrieve taxonomy data
		$taxonomy_obj = get_taxonomy($taxonomy_slug);
		$taxonomy_name = $taxonomy_obj->labels->name;

		// Retrieve taxonomy terms
		$terms = get_terms($taxonomy_slug);

		// Display filter HTML
		echo "<select name='{$taxonomy_slug}' id='{$taxonomy_slug}' class='postform'>";
		echo '<option value="">' . sprintf(esc_html__('Show All %s', 'text_domain'), $taxonomy_name) . '</option>';
		foreach ($terms as $term) {
			printf(
				'<option value="%1$s" %2$s>%3$s (%4$s)</option>',
				$term->slug,
				((isset($_GET[$taxonomy_slug]) && ($_GET[$taxonomy_slug] == $term->slug)) ? ' selected="selected"' : ''),
				$term->name,
				$term->count
			);
		}
		echo '</select>';
	}
}
add_action('restrict_manage_posts', 'filter_cars_by_taxonomies', 10, 2);
