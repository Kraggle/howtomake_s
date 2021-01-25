<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */


the_title('<h1 class="title">', '</h1>'); ?>

<p class="meta">by
	<span class="author">
		<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
			<?php echo  get_the_author() ?>
		</a>
	</span> |
	<span class="date"><?php echo get_the_date() ?></span> |
	<?php the_category(', ') ?>
</p>

<?php htm_s_post_thumbnail(); ?>

<div class="wrapper">
	<div class="content-wrap">
		<?php ob_start();
		the_content(
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

		$content = ob_get_contents();
		ob_end_clean();

		preg_match_all('/<img [^>]+>/i', $content, $imgs);

		// logger($imgs[0]);
		$sizes = get_all_image_sizes();

		foreach ($imgs[0] as $img) {
			if (preg_match('/wp-image-(\d+)/i', $img, $id) && preg_match('/size-([^ \"]+)/i', $img, $size)) {

				if ($size[0] == 'size-post') continue;

				$size = $size[1];

				$id = $id[1];
				$meta = wp_get_attachment_metadata($id);

				if ($meta['width'] < $sizes['post']['width']) continue;

				if ($use = $meta['sizes']['post']) {
					$new = preg_replace("/size-$size/", "size-post", $img);
					$new = preg_replace('/(src="[^"]+\/)[^"]+/', "$1{$use['file']}", $new);
					$new = preg_replace('/width="\d+/', "width=\"{$use['width']}", $new);
					$new = preg_replace('/height="\d+/', "height=\"{$use['height']}", $new);

					$content = str_replace($img, $new, $content);
				}
			}
		}

		echo $content;

		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__('Pages:', 'htm_s'),
				'after'  => '</div>',
			)
		); ?>
	</div>

	<?php echo do_shortcode('[htm_more_side_panel]') ?>
</div>

<?php // htm_s_entry_footer(); 
// END
