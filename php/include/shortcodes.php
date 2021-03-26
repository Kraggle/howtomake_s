<?php


/* Single Video */

function howtomake_shortcode_embed_video() {

	$ytVideoId = get_field('youtube_video_id');

	return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $ytVideoId . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
}
add_shortcode('current_video_embed', 'howtomake_shortcode_embed_video');



function howtomake_shortcode_video_title() {

	return get_the_title();
}
add_shortcode('current_video_title', 'howtomake_shortcode_video_title');


function howtomake_shortcode_video_channel_url() {

	$channels = get_the_terms(the_post(), 'video-channel');
	//$ytChannelId = get_field('yt_channel_id', $channels[0]);

	$link = get_term_link($channels[0], 'video-channel');
	return $link;
}
add_shortcode('current_video_channel_url', 'howtomake_shortcode_video_channel_url');


function howtomake_shortcode_video_category_list() {

	$cats = get_the_terms(get_the_ID(), 'video-category');

	$catsArray = [];
	foreach ($cats as $cat) {
		$link = get_term_link($cat, 'video-category');

		$catsArray[] = '<a href="' . $link . '">' . $cat->name . '</a>';
	}

	return implode(', ', $catsArray);
}
add_shortcode('current_video_category_list', 'howtomake_shortcode_video_category_list');


function howtomake_shortcode_video_channel_name() {

	$channels = get_the_terms(get_the_ID(), 'video-channel');

	return $channels[0]->name;
}
add_shortcode('current_video_channel_name', 'howtomake_shortcode_video_channel_name');


function howtomake_shortcode_video_publish_date() {

	return get_the_date('jS M, Y');
}
add_shortcode('current_video_publish_date', 'howtomake_shortcode_video_publish_date');


function howtomake_shortcode_registration_url() {

	return get_the_date('jS M, Y');
}
add_shortcode('registration_url', 'howtomake_shortcode_registration_url');


/* Video Channel */

function howtomake_shortcode_embed_featured_video() {

	$queried_object = get_queried_object();
	$termId = $queried_object->term_id;

	$postObj = get_field('featured_video', $queried_object);

	if (!$postObj) {
		$posts = get_posts(
			array(
				'posts_per_page' => 1,
				'post_type' => 'video',
				'orderby'          => 'post_date',
				'order'            => 'DESC',
				'tax_query' => array(
					array(
						'taxonomy' => 'video-channel',
						'field' => 'term_id',
						'terms' => $termId,
					)
				)
			)
		);

		$postObj = (is_array($posts) && isset($posts[0]))  ? $posts[0] : null;
	}
	//var_dump($posts);exit;

	//$posts = wp_get_recent_posts( array $args = array(), OBJECT );
	if (!$postObj) return '';

	$ytVideoId = get_field('youtube_video_id', $posts[0]);

	return '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . $ytVideoId . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
}
add_shortcode('featured_video_embed', 'howtomake_shortcode_embed_featured_video');

add_shortcode('search_form', function () {

	$value = get_search_query();
	$value = !$value ? '' : $value;

	ob_start() ?>

	<form role="search" method="get" class="search-form" action="/search/">
		<input type="text" name="term" class="search-field" placeholder="Search" value="<?= $value ?>">
		<button type="submit" class="search-submit"><i class="fa-icon type-regular svg-search"></i></button>
		<input type="hidden" name="type" value="post,video">
		<input type="hidden" name="order" value="desc">
		<input type="hidden" name="orderby" value="relevance">
	</form>

	<?php $html = ob_get_contents();
	ob_end_clean();

	return $html;
});

add_shortcode('list_categories', function ($attrs = null) {

	extract(shortcode_atts([
		'title_li'           => '',
		'style'              => 'none',
		'use_desc_for_title' => false,
		'taxonomy'           => 'category'
	], $attrs, 'htm_s'));

	$list = wp_list_categories([
		'title_li'           => $title_li,
		'style'              => $style,
		'echo'               => false,
		'use_desc_for_title' => $use_desc_for_title,
		'taxonomy'           => $taxonomy
	]);
	return trim(str_replace('<br />',  '', $list));
});

add_shortcode('latest_posts', function ($attrs = null) {

	extract(shortcode_atts([
		'post_type' => 'post'
	], $attrs, 'htm_s'));

	$latest = get_posts([
		'post_type' => $post_type
	]);

	ob_start();

	if ($latest) {
		foreach ($latest as $post) { ?>
			<a href="<?= get_permalink($post) ?>"><?= $post->post_title ?></a>
	<?php }
	}

	$html = ob_get_contents();
	ob_end_clean();

	return $html;
});

function sitemap_item($object, $post_type, $type = 'post') {
	$type = $type === 'post';
	$id   = $type ? $object->ID : $object->term_id;
	$link = $type ? $object->link : get_term_link($object->term_id);
	$name = $type ? $object->post_title : $object->name;

	ob_start(); ?>
	<li class="list-item" id="<?= "{$post_type}_{$id}" ?>">
		<a href="<?= $link ?>" class="list-link" title="<?= $name ?>">
			<?= $name ?>
		</a>

		<?php if ($type) {
			$kids = get_results(
				"SELECT
					p.ID,
					p.post_title,
					m.meta_value AS link
				FROM wp_postmeta m
				INNER JOIN wp_posts p
					ON m.post_id = p.ID
				WHERE p.post_type = '$post_type'
				AND p.post_status = 'publish'
				AND p.post_parent = $id
				AND m.meta_key = 'htm_permalink'
				GROUP BY p.ID,
						p.post_title,
						m.meta_value"
			);

			if (!empty($kids)) {
				foreach ($kids as $key => $post)
					$kids[$key]->post_title = friendly_title($post->post_title);

				uasort($kids, function ($a, $b) {
					$aT = preg_replace('/^[^\w]+/', '', $a->post_title);
					$bT = preg_replace('/^[^\w]+/', '', $b->post_title);
					return strnatcasecmp($aT, $bT);
				}); ?>

				<ul class="section-sub-list">
					<?php foreach ($kids as $kid) {
						echo sitemap_item($kid, $post_type, $type);
					} ?>
				</ul>
			<?php } ?>
		<?php } ?>
	</li>
	<?php
	$html = ob_get_contents();
	ob_end_clean();

	return $html;
}

function htm_shortcode_sitemap() {

	$post_types = get_post_types(array('public' => true), 'names', 'and');
	$taxonomies = htm_get_taxonomies();

	global $wpdb;
	$wpdb->query(
		"DELETE
		FROM wp_postmeta
		WHERE meta_key = 'htm_permalink'
		AND meta_value LIKE '%-autosave-%'"
	);

	ob_start();

	foreach ($post_types as $post_type) {
		if (!get_option("htm_sitemap_include_$post_type")) continue;

		$posts = get_results(
			"SELECT
				p.ID,
				p.post_title,
				m.meta_value AS link
			FROM wp_postmeta m
			INNER JOIN wp_posts p
				ON m.post_id = p.ID
			WHERE p.post_type = '$post_type'
			AND p.post_status = 'publish'
			AND p.post_parent = 0
			AND m.meta_key = 'htm_permalink'
			GROUP BY p.ID,
					p.post_title,
					m.meta_value"
		);

		if (!empty($posts)) {
			foreach ($posts as $key => $post)
				$posts[$key]->post_title = friendly_title($post->post_title);

			uasort($posts, function ($a, $b) {
				$aT = preg_replace('/^[^\w]+/', '', $a->post_title);
				$bT = preg_replace('/^[^\w]+/', '', $b->post_title);
				return strnatcasecmp($aT, $bT);
			});

			$type = get_post_type_object($post_type); ?>

			<div class="section-wrap">
				<h3 class="section-head"><?= $type->label ?></h3>
				<ul class="section-list">

					<?php foreach ($posts as $post) {
						echo sitemap_item($post, $post_type, 'post');
					} ?>
				</ul>
			</div>
			<?php }
		wp_reset_query();
	}

	if ($taxonomies) {
		foreach ($taxonomies as $taxonomy) {
			$tax = get_taxonomy($taxonomy);
			if (!get_option("htm_sitemap_include_{$tax->name}")) continue;

			$terms = get_terms(array('taxonomy' => $taxonomy, 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => true));

			if (!empty($terms)) { ?>
				<div class="section-wrap">
					<h3 class="section-head"><?= $tax->label ?></h3>
					<ul class="section-list">

						<?php foreach ($terms as $term) {

							echo sitemap_item($term, $tax->label, 'taxonomy');
						} ?>
					</ul>
				</div>
	<?php }
		}
	}

	$html = ob_get_contents();
	ob_end_clean();

	return $html;
}
add_shortcode('htm_sitemap', 'htm_shortcode_sitemap');

add_shortcode('fancy_title', function ($attrs = []) {
	extract(shortcode_atts([
		'id'     => '',
		'title'  => 'Please add a title="Some Title" attribute to the shortcode',
		'icon'   => '',
		'sub'    => '',
		'social' => '',
		'link'   => ''
	], $attrs, 'htm_s'));

	$doSocial = false;
	if ($social) {
		$new = explode(';', $social);
		$social = [];
		foreach ($new as $item)
			$social[] = explode(',', $item);

		if ($social[0] && $social[0][0])
			$doSocial = true;
	}

	$out = '';
	if (!empty($link))
		$out = get_font_awesome_icon('external-link');

	ob_start(); ?>

	<div id="<?= $id ?>" class="fancy-title<?= !empty($icon) ? ' with-icon' : '' ?><?= $doSocial ? ' with-social' : '' ?><?= !empty($sub) ? ' with-sub' : '' ?>">

		<?php // The Icon 
		if (!empty($icon)) { ?>
			<img loading="lazy" src="<?= $icon ?>" class="fancy-icon">
		<?php } ?>

		<div class="fancy-details">

			<?php // The Title 
			if ($link) { ?>
				<a href="<?= $link ?>" class="fancy-out" target="_blank">
				<?php } ?>
				<h2 class="fancy-text"><?= $title ?></h2>
				<?php if ($link) {
					echo $out; ?>
				</a>
			<?php } ?>

			<?php // The Subtitle 
			if (!empty($sub)) { ?>
				<span class="fancy-sub"><?= $sub ?></span>
			<?php } ?>

			<?php // The Social links 
			if ($doSocial) { ?>
				<div class="fancy-social">
					<?php foreach ($social as $item) { ?>
						<a href="<?= $item[1] ?>" class="fancy-link" title="<?= $item[2] ?: '' ?>" <?= $item[3] ? ' style="background-color:' . $item[3] . '"' : '' ?> target="_blank"><?= get_font_awesome_icon($item[0], 'brands') ?></a>
					<?php } ?>
				</div>
			<?php } ?>

		</div>
	</div>

<?php $html = ob_get_contents();
	ob_end_clean();

	return $html;
});
