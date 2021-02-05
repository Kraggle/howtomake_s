<?php // HTML partials/entry-meta 
?>

<article id="l_<?= $id ?>" class="entry <?= get_post_type() ?>">

	<a href="<?= get_permalink() ?>" class="entry-image <?= get_post_type() ?>">
		<img alt="<?php the_title_attribute() ?>" src="<?= get_the_post_thumbnail_url(null, 'result') ?>">
	</a>

	<h2 class="entry-title">
		<a href="<?= get_permalink() ?>" title="<?= get_the_title() ?>"><?= get_the_title() ?></a>
	</h2>

	<p class="entry-meta">by
		<span class="entry-author">
			<a href="<?= get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
				<?= get_the_author() ?>
			</a>
		</span> |
		<span class="entry-date"><?= get_the_date('M jS, Y') ?></span> |
		<?php the_category(', ') ?>
	</p>

	<p class="entry-detail">
		<span class="detail-time">
			<?= get_font_awesome_icon('history', 'light') ?>
			<?php $time = get_read_time();
			echo "$time min" . ($time == 1 ? '' : 's'); ?>
		</span>
		<span class="spacer"></span>
		<span class="detail-type">
			<?= get_font_awesome_icon('book', 'light') ?>
			article
		</span>
	</p>

</article>


<?php 
// END
