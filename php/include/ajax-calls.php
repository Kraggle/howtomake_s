<?
add_action('wp_ajax_custom_search', 'htm_custom_search');
add_action('wp_ajax_nopriv_custom_search', 'htm_custom_search');

function htm_custom_search() {
	if (!wp_verify_nonce($_REQUEST['nonce'], 'custom_search_nonce'))
		exit('Well, that was wrong!');

	$query = $_REQUEST['query'] ?: array();
	$query['post_status'] = 'publish';

	// error_log(json_encode($query));

	$query = new WP_Query($query);
	if ($query->have_posts()) {
		$posts = [];

		while ($query->have_posts()) {
			$query->the_post();

			$posts[] = load_template_part('views/category/search', get_post_type());
		}

		$return = [
			"success" => true,
			"posts" => $posts,
			"count" => $query->post_count,
			"found" => $query->found_posts
		];
	} else {
		$return = [
			"success" => false
		];
	}

	// error_log(json_encode($query));

	echo json_encode($return);
}
