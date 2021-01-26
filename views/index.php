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
<html <?php echo get_language_attributes() ?>>
<?php get_template_part('views/partials/head') ?>

<body <?php body_class('index') ?>>
	<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-T674ZX6" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php get_template_part('views/partials/loader') ?>
	<div class="body-wrap">
		<?php do_action('get_header') ?>
		<?php get_template_part('views/partials/header') ?>
		<div class="wrap main-container" role="document">
			<div class="body-decor"></div>
			<div class="body-curves"></div>
			<div class="content">
				<main class="main">

					<?php if (have_posts()) {

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

			<?php get_template_part('views/partials/subscribe-panel') ?>
			<?php get_template_part('views/partials/more-panel') ?>
		</div>


	</div>
	<?php do_action('get_footer') ?>
	<?php get_template_part('views/partials/footer') ?>
	<?php wp_footer() ?>
	</div>
</body>

</html>

<?php
// END
