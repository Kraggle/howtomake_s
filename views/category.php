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
<html <?php echo get_language_attributes() ?>>
<?php get_template_part('views/partials/head') ?>

<body <?php body_class() ?>>
	<?php get_template_part('views/partials/loader') ?>
	<div class="body-wrap">
		<?php do_action('get_header') ?>
		<?php get_template_part('views/partials/header') ?>
		<div class="wrap main-container" role="document">
			<div class="body-decor"></div>
			<div class="body-curves"></div>
			<div class="content">
				<main class="main">

					<h1 class="page-title"><?php echo single_cat_title() ?></h1>

					<div class="gallery">
						<?php $j = 0;
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
								<?php for ($i = 0; $i < $j; $i++) { ?>
									<div btn=<?php echo 'g_' . $ids[$i] ?> class="gallery-btn<?php echo $i == 0 ? ' active' : '' ?>" index=<?php echo $i ?>></div>
								<?php } ?>
							</div>
						<?php } ?>

						<div class="gallery-control">
							<div class="gallery-arrow left"></div>
							<div class="gallery-spacer"></div>
							<div class="gallery-arrow right"></div>
						</div>
					</div>

					<div class="list">
						<?php if (have_posts()) {

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

					<?php $type = ucwords(get_post_type());
					the_posts_navigation([
						'prev_text'          => "Older {$type}s",
						'next_text'          => "Newer {$type}s",
						'screen_reader_text' => '',
						'class'              => 'post-nav'
					]); ?>

				</main>
			</div>

			<?php get_template_part('views/partials/subscribe-panel') ?>
			<?php get_template_part('views/partials/more-panel') ?>
		</div>
		<?php do_action('get_footer') ?>
		<?php get_template_part('views/partials/footer') ?>
		<?php wp_footer() ?>
	</div>
</body>

</html>

<?php
add_action('loop_start', 'et_dbp_main_loop_start');
add_action('loop_end', 'et_dbp_main_loop_end');
// END
