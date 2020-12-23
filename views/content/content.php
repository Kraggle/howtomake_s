<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */


if (is_singular())
	the_title('<h1 class="title">', '</h1>');
else
	the_title('<h2 class="title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');

if ('post' === get_post_type()) { ?>
	<p class="meta">by
		<span class="author">
			<a href="<? echo get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
				<? echo  get_the_author() ?>
			</a>
		</span> |
		<span class="date"><? echo get_the_date() ?></span> |
		<? the_category(', ') ?>
	</p>
<? } ?>

<? htm_s_post_thumbnail(); ?>

<div class="wrapper">
	<div class="content-wrap">
		<? the_content(
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

	<? echo do_shortcode('[htm_more_side_panel]') ?>
</div>

<? // htm_s_entry_footer(); 
// END
