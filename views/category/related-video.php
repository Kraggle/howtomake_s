<?php // HTML partials/entry-meta 
?>

<article id="l_<?= $id ?>" class="related">

	<a href="<?= get_permalink() ?>" class="related-image <?= get_post_type() ?>">
		<img alt="<?php the_title_attribute() ?>" src="<?= get_the_video_thumbnail_url($id, 'menu') ?>">
	</a>
	<h2 class="related-title">
		<a href="<?= get_permalink() ?>"><?= get_the_title() ?></a>
	</h2>
	<p class="related-meta">by
		<span class="related-author">
			<a title="Video by" href="<?php the_channel(null, 'link') ?>"><?php the_channel(null, 'name'); ?></a>
		</span> |
		<span class="related-date"><?= get_the_date('M jS, Y') ?></span> |
		<?php the_terms($id, 'video-category', '', ', ') ?>
	</p>
	<div class="related-excerpt">
		<?= remove_emoji(get_the_excerpt()) ?>
	</div>
</article>


<?php 
// END
