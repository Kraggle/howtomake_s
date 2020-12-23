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

?>
<!doctype html>
<html <? echo get_language_attributes() ?>>
<? get_template_part('views/partials/head') ?>

<body <? body_class('index') ?>>
	<? get_template_part('views/partials/loader') ?>
	<div class="body-wrap">
		<? do_action('get_header') ?>
		<? get_template_part('views/partials/header') ?>
		<div class="wrap main-container" role="document">
			<div class="body-decor"></div>
			<div class="body-curves"></div>
			<div class="content">
				<main class="main">

					<? if (have_posts()) {

						/* Start the Loop */
						while (have_posts()) {
							the_post();

							/**
							 * Include the Post-Type-specific template for the content.
							 * If you want to override this in a child theme, then include a file
							 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
							 */
							get_template_part('views/content/content', get_post_type());
						}

						the_posts_navigation();
					} else {
						get_template_part('views/content/content', 'none');
					} ?>

				</main>
			</div>

			<? get_template_part('views/partials/subscribe-panel') ?>
			<? get_template_part('views/partials/more-panel') ?>
		</div>


	</div>
	<? do_action('get_footer') ?>
	<? get_template_part('views/partials/footer') ?>
	<? wp_footer() ?>
	</div>
</body>

</html>

<?
// END
