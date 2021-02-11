<?php // HTML partials/entry-meta 
?>


<article id="l_<?= $id ?>" class="entry <?= get_post_type() ?>">

	<a href="<?= get_permalink() ?>" class="entry-image <?= get_post_type() ?>">
		<img alt="<?php the_title_attribute() ?>" src="<?= get_the_post_thumbnail_url($id, 'menu') ?>">
	</a>
	<h2 class="entry-title">
		<a href="<?= get_permalink() ?>" title="<?= get_the_title() ?>"><?= get_the_title() ?></a>
	</h2>
	<p class="entry-meta">by
		<span class="entry-author">
			<a title="Video by" href="<?php the_channel(null, 'link') ?>"><?php the_channel(null, 'name'); ?></a>
		</span> |
		<span class="entry-date"><?= get_the_date('M jS, Y') ?></span> |
		<?php the_terms($id, 'video-category', '', ', ') ?>
	</p>
	<div class="entry-excerpt">
		<?= remove_emoji(get_the_excerpt()) ?>
	</div>
</article>


<?php 
// END
