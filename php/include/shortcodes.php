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


function htm_s_shotcode_more_panel() {

	ob_start(); ?>

	<div class="more-side">

		<div class="more-part" part="1"></div>
		<div class="more-part" part="2"></div>
		<div class="more-part" part="3"></div>

		<?php
		// Search Panel
		do_more_panel(function () {
			get_template_part('views/widgets/search-form');
		});

		// About Panel
		do_more_panel(function () {
			get_template_part('views/partials/about-us');
		}, 'About Us');

		// Popular Panel
		do_more_panel(function () {
			wpp_get_mostpopular([
				'post_type' => 'post',
				'wpp_start' => '',
				'wpp_end'   => '',
				'post_html' => '<a href="{url}">{text_title}</a>',
				'range'     => 'last7days'
			]);
		}, 'Popular');

		do_more_panel(function () {
			$list = wp_list_categories([
				'title_li'           => '',
				'style'              => 'none',
				'echo'               => false,
				'use_desc_for_title' => false,
				'taxonomy'           => 'category'
			]);
			return trim(str_replace('<br />',  '', $list));
		}, 'Categories');

		do_more_panel(function () {
			$latest = get_posts([
				'post_type' => 'post'
			]);

			if ($latest) {
				foreach ($latest as $post) { ?>
					<a href="<?php echo get_permalink($post) ?>"><?php echo $post->post_title ?></a>
		<?php }
			}
		}, 'Latest'); ?>

	</div>

<?php $html = ob_get_contents();
	ob_end_clean();

	return $html;
}
add_shortcode('htm_more_side_panel', 'htm_s_shotcode_more_panel');
