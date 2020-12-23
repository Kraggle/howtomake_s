<?php

/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package howtomake_S
 */

add_action('loop_start', 'et_dbp_main_loop_start');
add_action('loop_end', 'et_dbp_main_loop_end');
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

					<? if (have_posts()) { ?>

						<!-- <h1 class="page-title">Search Results</h1> -->

						<div class="search-box">
							<div class="display-text">
								<p>Search to see if you can make money out of your hobby.</p>
							</div>
							<div class="form-wrap">
								<? get_template_part('views/widgets/search-bar') ?>
							</div>
						</div>

						<div class="list">
							<? while (have_posts()) {
								the_post();
								// error_log('Post Type: ' . get_post_type());
								get_template_part('views/category/entry', get_post_type());
							} ?>

							<div class="list-part" part=1></div>
							<div class="list-part" part=2></div>
							<div class="list-part" part=3></div>
						</div>

					<? the_posts_navigation([
							'prev_text'          => 'Older',
							'next_text'          => 'Newer',
							'screen_reader_text' => '',
							'class'              => 'post-nav'
						]);
					} else {
						get_template_part('views/content/content', 'none');
					} ?>
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
// END
