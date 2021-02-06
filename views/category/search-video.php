<?php // HTML partials/entry-meta 
?>

<article id="l_<?= $id ?>" class="entry <?= get_post_type() ?>">

	<a href="<?= get_permalink() ?>" class="entry-image <?= get_post_type() ?>">
		<img alt="<?php the_title_attribute() ?>" src="<?= get_the_video_thumbnail_url(null, 'result') ?>">
	</a>

	<h2 class="entry-title">
		<a href="<?= get_permalink() ?>" title="<?= get_the_title() ?>"><?= get_the_title() ?></a>
	</h2>

	<p class="entry-meta">by
		<span class="entry-author">
			<a title="Video by" href="<?php the_channel(null, 'link') ?>">
				<?php the_channel(null, 'name'); ?>
			</a>
		</span> |
		<span class="entry-date"><?= get_the_date('M jS, Y') ?></span> |
		<?php the_terms($id, 'video-category', '', ', ') ?>
	</p>

	<p class="entry-detail">
		<span class="detail-time">
			<?= get_font_awesome_icon('history', 'light') ?>
			<?= get_duration(null, 'full') ?>
		</span>
		<span class="spacer"></span>
		<span class="detail-type">
			<?= get_font_awesome_icon('vhs', 'light') ?>
			video
		</span>
	</p>

</article>


<?php 
// END
