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

logger('index.php has beed called, this should be rectified as soon as posible', 'we are just going to display a simple coming soon message for now.');

?>
<!doctype html>
<html <?= get_language_attributes() ?>>
<?php get_template_part('views/partials/head') ?>

<body <?php body_class('index') ?>>
	<?php get_template_part('views/partials/body-top') ?>
	<?php get_template_part('views/partials/loader') ?>
	<div class="body-wrap">
		<?php do_action('get_header') ?>
		<?php get_template_part('views/partials/header') ?>
		<div class="wrap main-container" role="document">
			<div class="body-decor"></div>
			<div class="body-curves"></div>
			<div class="content">
				<main class="main">

					<?php get_template_part('views/content/content', 'soon') ?>

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
	<?php get_template_part('views/partials/active-dialog') ?>
</body>

</html>

<?php
// END
