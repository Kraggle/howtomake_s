<?php // HTML partials/entry-meta 
?>


<article id="l_<?php echo $id ?>" class="entry">

	<a href="<?php echo get_permalink() ?>" class="entry-image <?php echo get_post_type() ?>">
		<img alt="<?php the_title_attribute() ?>" src="<?php echo get_the_post_thumbnail_url(null, 'medium') ?>">
	</a>
	<h2 class="entry-title">
		<a href="<?php echo get_permalink() ?>"><?php echo get_the_title() ?></a>
	</h2>
	<!-- <time class="updated" datetime="<?php echo get_post_time('c', true) ?>"></time> -->
	<p class="entry-meta">by
		<span class="entry-author">
			<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
				<?php echo  get_the_author() ?>
			</a>
		</span> |
		<span class="entry-date"><?php echo get_the_date() ?></span> |
		<?php the_category(', ') ?>
	</p>
	<div class="entry-excerpt">
		<?php echo remove_emoji(get_the_excerpt()) ?>
	</div>
</article>


<?php 
// END
