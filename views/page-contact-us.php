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

			<? if (have_posts()) {
				while (have_posts()) {
					the_post();
					get_template_part('views/content/content-contact');
				}

				the_posts_navigation();
			} else {
				get_template_part('views/content/content', 'none');
			} ?>

		</div>

		<? do_action('get_footer') ?>
		<? get_template_part('views/partials/footer') ?>
		<? wp_footer() ?>
	</div>
</body>

</html>

<?
// END
