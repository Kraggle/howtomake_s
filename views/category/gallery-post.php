<?php // HTML partials/entry-meta 
?>

<div id="g_<?php echo $id ?>" class="gallery-item<?php echo $args['i'] == 0 ? ' active' : '' ?>" index="<?php echo $args['i'] ?>">
	<div class="gallery-image" style="background-image:url(<?php echo get_the_post_thumbnail_url(null, 'featured') ?>)"></div>
	<div class="gallery-content">
		<h2 class="gallery-title">
			<?php echo get_the_title() ?>
		</h2>
		<p class="gallery-meta">by
			<span class="gallery-author">
				<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
					<?php echo  get_the_author() ?>
				</a>
			</span> |
			<span class="gallery-date"><?php echo get_the_date() ?></span> |
			<?php the_category(', ') ?>
		</p>
		<div class="gallery-excerpt">
			<?php echo remove_emoji(get_the_excerpt()) ?>
		</div>
		<a href="<?php echo get_permalink() ?>" class="gallery-more">Read More</a>
	</div>
</div>


<?php 
// END
