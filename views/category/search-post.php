<?php // HTML partials/entry-meta 
?>

<article id="l_<?php echo $id ?>" class="entry <?php echo get_post_type() ?>">

	<a href="<?php echo get_permalink() ?>" class="entry-image <?php echo get_post_type() ?>">
		<img alt="<?php the_title_attribute() ?>" src="<?php echo get_the_post_thumbnail_url(null, 'medium') ?>">
	</a>

	<h2 class="entry-title">
		<a href="<?php echo get_permalink() ?>" title="<?php echo get_the_title() ?>"><?php echo get_the_title() ?></a>
	</h2>

	<p class="entry-meta">by
		<span class="entry-author">
			<a href="<?php echo get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
				<?php echo  get_the_author() ?>
			</a>
		</span> |
		<span class="entry-date"><?php echo get_the_date('M jS, Y') ?></span> |
		<?php the_category(', ') ?>
	</p>

	<p class="entry-detail">
		<span class="detail-time">
			<?php echo get_font_awesome_icon('history', 'light') ?>
			<?php $time = get_read_time();
			echo "$time min" . ($time == 1 ? '' : 's'); ?>
		</span>
		<span class="spacer"></span>
		<span class="detail-type">
			<?php echo get_font_awesome_icon('book', 'light') ?>
			article
		</span>
	</p>

</article>


<?php 
// END
