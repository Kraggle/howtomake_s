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

<body <? body_class('front-page') ?>>
	<? get_template_part('views/partials/loader') ?>
	<div class="body-wrap">
		<? do_action('get_header') ?>
		<? get_template_part('views/partials/header') ?>
		<div class="wrap main-container" role="document">
			<div class="content">
				<main class="main">

					<? if (have_posts()) {

						while (have_posts()) {
							the_post();

							get_template_part('views/content/content', get_post_type());
						}

						the_posts_navigation();
					} else {
						get_template_part('views/content/content', 'none');
					} ?>

				</main>
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
