<?php

/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package howtomake_S
 */

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
		<div class="wrap main-container" role="document">
			<div class="body-decor"></div>
			<div class="body-curves"></div>
			<div class="content">
				<main class="main">

					<?php if (have_posts()) { ?>

						<!-- <h1 class="page-title">Search Results</h1> -->

						<div class="search-box">
							<div class="display-text">
								<p>Search to see if you can make money out of your hobby.</p>
							</div>
							<div class="form-wrap">
								<?php get_template_part('views/widgets/search-bar') ?>
							</div>
						</div>

						<div class="list">
							<?php while (have_posts()) {
								the_post();
								// error_log('Post Type: ' . get_post_type());
								get_template_part('views/category/entry', get_post_type());
							} ?>

							<div class="list-part" part=1></div>
							<div class="list-part" part=2></div>
							<div class="list-part" part=3></div>
						</div>

					<?php the_posts_navigation([
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
