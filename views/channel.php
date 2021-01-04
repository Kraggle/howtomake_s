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

					<div class="detail-wrap">
						<div class="left">
							<?php get_channel(null, 'logo'); ?>
							<line class="overline"></line>
							<h4 class="detail-title">Channel Overview</h4>
							<p><?php get_channel(null, 'description') ?></p>
						</div>
						<div class="right">
							<div class="detail-box">
								<h5 class="detail-head">Category</h5>
								<?php get_channel(null, 'categories'); ?>
							</div>
							<div class="detail-box">
								<h5 class="detail-head">Publisher</h5>
								<span><?php get_channel(null, 'name'); ?></span>
							</div>
							<div class="detail-box">
								<h5 class="detail-head">Platform</h5>
								<span>YouTube</span>
							</div>
							<div class="detail-box">
								<h5 class="detail-head">Videos</h5>
								<span><?php get_channel(null, 'count') ?></span>
							</div>
							<div class="detail-box span-2">
								<h5 class="detail-head">Social</h5>
								<div class="social-links">
									<?php get_channel(null, 'social') ?>
								</div>
							</div>
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

					<?php the_posts_navigation([
						'prev_text'          => 'Older Videos',
						'next_text'          => 'Newer Videos',
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
// END
