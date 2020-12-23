<?php

/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package howtomake_S
 */

remove_action('loop_start', 'et_dbp_main_loop_start');
remove_action('loop_end', 'et_dbp_main_loop_end');

?>
<!doctype html>
<html <? echo get_language_attributes() ?>>
<? get_template_part('views/partials/head') ?>

<body <? body_class() ?>>
	<? get_template_part('views/partials/loader') ?>
	<div class="body-wrap">
		<? do_action('get_header') ?>
		<? get_template_part('views/partials/header') ?>
		<div class="wrap main-container" role="document">
			<div class="body-decor"></div>
			<div class="body-curves"></div>
			<div class="content">
				<main class="main">

					<h1 class="page-title"><? echo single_cat_title() ?></h1>

					<div class="gallery">
						<? $j = 0;
						$ids = [];
						if (have_posts()) {

							while (have_posts()) {
								the_post();
								get_template_part('views/category/gallery', get_post_type(),  ['i' => $j]);

								$j++;
								$ids[] = $id;
							}
						} else {
							// TODO: Put something if there are no results
						}

						if ($j) { ?>
							<div class="gallery-nav">
								<? for ($i = 0; $i < $j; $i++) { ?>
									<div btn=<? echo 'g_' . $ids[$i] ?> class="gallery-btn<? echo $i == 0 ? ' active' : '' ?>" index=<? echo $i ?>></div>
								<? } ?>
							</div>
						<? } ?>

						<div class="gallery-control">
							<div class="gallery-arrow left"></div>
							<div class="gallery-spacer"></div>
							<div class="gallery-arrow right"></div>
						</div>
					</div>

					<div class="detail-wrap">
						<div class="left">
							<? get_channel(null, 'logo'); ?>
							<line class="overline"></line>
							<h4 class="detail-title">Channel Overview</h4>
							<p><? get_channel(null, 'description') ?></p>
						</div>
						<div class="right">
							<div class="detail-box">
								<h5 class="detail-head">Category</h5>
								<? get_channel(null, 'categories'); ?>
							</div>
							<div class="detail-box">
								<h5 class="detail-head">Publisher</h5>
								<span><? get_channel(null, 'name'); ?></span>
							</div>
							<div class="detail-box">
								<h5 class="detail-head">Platform</h5>
								<span>YouTube</span>
							</div>
							<div class="detail-box">
								<h5 class="detail-head">Videos</h5>
								<span><? get_channel(null, 'count') ?></span>
							</div>
							<div class="detail-box span-2">
								<h5 class="detail-head">Social</h5>
								<div class="social-links">
									<? get_channel(null, 'social') ?>
								</div>
							</div>
						</div>
					</div>


					<div class="list">
						<? if (have_posts()) {

							while (have_posts()) {
								the_post();
								get_template_part('views/category/entry', get_post_type());
							}
						} else {
							// TODO: Put something if there are no results
						} ?>

						<div class="list-part" part=1></div>
						<div class="list-part" part=2></div>
						<div class="list-part" part=3></div>
					</div>

					<? the_posts_navigation([
						'prev_text'          => 'Older Videos',
						'next_text'          => 'Newer Videos',
						'screen_reader_text' => '',
						'class'              => 'post-nav'
					]); ?>

				</main>
			</div>

			<? get_template_part('views/partials/subscribe-panel') ?>
			<? get_template_part('views/partials/more-panel') ?>
		</div>
		<? do_action('get_footer') ?>
		<? get_template_part('views/partials/footer') ?>
		<? wp_footer() ?>
	</div>
</body>

</html>

<?
add_action('loop_start', 'et_dbp_main_loop_start');
add_action('loop_end', 'et_dbp_main_loop_end');
// END
