<?php // HTML partials/entry-meta 
?>

<article id="l_<?php echo $id ?>" class="related">

	<a href="<?php echo get_permalink() ?>" class="related-image <?php echo get_post_type() ?>">
		<img alt="<?php the_title_attribute() ?>" src="<?php echo get_the_post_thumbnail_url(null, 'menu') ?>">
	</a>
	<h2 class="related-title">
		<a href="<?php echo get_permalink() ?>"><?php echo get_the_title() ?></a>
	</h2>
	<p class="related-meta">by
		<span class="related-author">
			<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
				<?php echo  get_the_author() ?>
			</a>
		</span> |
		<span class="related-date"><?php echo get_the_date('M jS, Y') ?></span> |
		<?php the_category(', ') ?>
	<div class="related-excerpt">
		<?php echo remove_emoji(get_the_excerpt()) ?>
	</div>
	</p>
</article>


<?php 
// END
