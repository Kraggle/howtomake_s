<?php // HTML partials/entry-meta 
?>

<div id="g_<?php echo $id ?>" class="gallery-item<?php echo $args['i'] == 0 ? ' active' : '' ?>" index="<?php echo $args['i'] ?>">
	<div class="gallery-image" style="background-image:url(<?php echo get_the_post_thumbnail_url(null, 'large') ?>)"></div>
	<div class="gallery-content">
		<h2 class="gallery-title">
			<?php echo get_the_title() ?>
		</h2>
		<p class="gallery-meta">by
			<span class="gallery-author">
				<a title="Video by" href="<?php the_channel(null, 'link') ?>"><?php the_channel(null, 'name'); ?></a>
			</span> |
			<span class="gallery-date"><?php echo get_the_date() ?></span> |
			<?php the_terms($id, 'video-category', '', ', ') ?>
		</p>
		<div class="gallery-excerpt">
			<?php echo remove_emoji(get_the_excerpt()) ?>
		</div>
		<a href="<?php echo get_permalink() ?>" class="gallery-more">Watch Now</a>
	</div>
</div>


<?php 
// END
