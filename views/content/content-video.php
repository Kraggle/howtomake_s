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
		<a title="Video by" href="<? the_channel(null, 'link') ?>"><? the_channel(null, 'name'); ?></a>
	</span> |
	<span class="date"><? echo get_the_date() ?></span> |
	<? the_terms($id, 'video-category', '', ', ') ?>
</p>

<div id="v_<? echo $id ?>" class="video-wrap">
	<iframe width="1080" height="608" src="https://www.youtube.com/embed/<?php the_field('youtube_video_id'); ?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
</div>

<div class="wrapper">
	<div class="content-wrap">

		<h3>Description</h3>

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
		); ?>

		<div class="detail-wrap">
			<div class="left">
				<line class="overline"></line>
				<h4 class="detail-title">Channel Overview</h4>
				<p><? the_channel(null, 'description'); ?></p>
			</div>
			<div class="right">
				<div class="detail-box">
					<h5 class="detail-head">Category</h5>
					<? the_terms($id, 'video-category', '', ', ') ?>
				</div>
				<div class="detail-box">
					<h5 class="detail-head">Publisher</h5>
					<a href="<? the_channel(null, 'link') ?>"><? the_channel(null, 'name'); ?></a>
				</div>
				<div class="detail-box">
					<h5 class="detail-head">Release Date</h5>
					<span class="date"><? echo get_the_date() ?></span>
				</div>
				<div class="detail-box">
					<h5 class="detail-head">Platform</h5>
					<span>YouTube</span>
				</div>
			</div>
		</div>
		<a href="<? the_channel(null, 'link') ?>" class="channel-link">View More from Channel</a>

		<? wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__('Pages:', 'htm_s'),
				'after'  => '</div>',
			)
		); ?>
	</div>


</div>

<? 
// END
