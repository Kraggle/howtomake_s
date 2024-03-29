<?php

/**
 * The template for displaying single posts
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

$toc = to_object(get_field('toc'));
if (!$toc)
	$toc = (object) [
		'active' => false
	];

?>
<!doctype html>
<html <?= get_language_attributes() ?>>
<?php get_template_part('views/partials/head') ?>

<body <?php body_class() ?>>
	<?php get_template_part('views/partials/body-top') ?>
	<?php get_template_part('views/partials/loader') ?>
	<div class="body-wrap">
		<?php do_action('get_header') ?>
		<?php get_template_part('views/partials/header') ?>
		<div class="wrap main-container<?= get_field('description') ? ' with-description' : '' ?>" role="document">
			<div class="body-decor"></div>
			<div class="body-curves"></div>
			<div class="content<?= $toc->active ? ' with-sidebar' : '' ?>">
				<main class="main" itemscope itemtype="http://schema.org/Article">

					<?php if (have_posts()) {
						while (have_posts()) {
							the_post();
							// error_log('Post Type: ' . get_post_type());
							get_template_part('views/content/content', get_post_type());
						}

						the_posts_navigation();
					} else {
						get_template_part('views/content/content', 'none');
					} ?>

				</main>
			</div>

			<?php if (get_post_type() == 'video') {
				//get_template_part('views/partials/related-panel');
			} ?>

			<?php get_template_part('views/partials/subscribe-panel') ?>
			<?php get_template_part('views/partials/more-panel') ?>
		</div>


		<?php do_action('get_footer') ?>
		<?php get_template_part('views/partials/footer') ?>
		<?php wp_footer() ?>
	</div>
	<?php get_template_part('views/partials/body-bottom') ?>
</body>

</html>

<?php
// END
