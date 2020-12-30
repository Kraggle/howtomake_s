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
<html <?php echo get_language_attributes() ?>>
<?php get_template_part('views/partials/head') ?>

<body <?php body_class() ?>>
	<?php get_template_part('views/partials/loader') ?>
	<div class="body-wrap">
		<?php do_action('get_header') ?>
		<?php get_template_part('views/partials/header') ?>
		<div class="wrap main-container" role="document">

			<?php if (have_posts()) {
				while (have_posts()) {
					the_post();
					get_template_part('views/content/content-contact');
				}

				the_posts_navigation();
			} else {
				get_template_part('views/content/content', 'none');
			} ?>

		</div>

		<?php do_action('get_footer') ?>
		<?php get_template_part('views/partials/footer') ?>
		<?php wp_footer() ?>
	</div>
</body>

</html>

<?php
// END
