<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */


the_title('<h1 class="title" itemprop="headline">', '</h1>');

echo content_schema_meta(); ?>

<p class="meta">by
	<span class="author">
		<a href="<?= get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
			<?= get_the_author() ?>
		</a>
	</span> |
	<span class="date"><?= get_the_date() ?></span> |
	<?php the_category(', ') ?>
</p>

<?php htm_s_post_thumbnail(); ?>

<div class="wrapper">
	<div class="content-wrap" itemprop="articleBody">
		<?php the_content(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__('Continue reading<span class="screen-reader-text"> "%s"</span>', 'htm_s'),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post(get_the_title())
			)
		);

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__('Pages:', 'htm_s'),
				'after'  => '</div>',
			)
		); ?>
	</div>

	<?= do_shortcode('[htm_more_side_panel]') ?>
</div>

<?php // htm_s_entry_footer(); 
// END
