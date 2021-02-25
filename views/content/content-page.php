<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */

the_title('<h1 class="title" itemprop="headline">', '</h1>');

$sidebar = (object) get_field('side_bar');
if (!$sidebar)
	$sidebar = (object) [
		'active' => false
	];

if ($desc = get_field('description')) { ?>
	<div class="description">
		<?= $desc ?>
	</div>
<?php }

echo content_schema_meta(); ?>

<div class="wrapper">
	<?php if ($sidebar->active) { ?>
		<div class="inner-wrap">
		<?php get_template_part('views/widgets/table-of-contents');
	} ?>


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

		<?php if ($sidebar->active) { ?>
		</div>
	<?php } ?>

	<?php if (!is_front_page()) echo do_shortcode('[htm_more_side_panel]');
	else get_template_part('views/widgets/home-side-panel') ?>
</div>

<?php // htm_s_entry_footer(); 
// END
