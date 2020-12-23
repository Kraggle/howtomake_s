<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */

if (!is_front_page()) the_title('<h1 class="title">', '</h1>'); ?>

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

	<? if (!is_front_page()) echo do_shortcode('[htm_more_side_panel]') ?>
</div>

<? // htm_s_entry_footer(); 
// END
