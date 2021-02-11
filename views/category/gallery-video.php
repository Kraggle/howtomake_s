<?php // HTML partials/entry-meta 
?>

<div id="g_<?= $id ?>" class="gallery-item<?= $args['i'] == 0 ? ' active' : '' ?>" index="<?= $args['i'] ?>">
	<div class="gallery-image" style="background-image:url(<?= get_the_post_thumbnail_url(null, 'featured') ?>)"></div>
	<div class="gallery-content">
		<h2 class="gallery-title">
			<?= get_the_title() ?>
		</h2>
		<p class="gallery-meta">by
			<span class="gallery-author">
				<a title="Video by" href="<?php the_channel(null, 'link') ?>"><?php the_channel(null, 'name'); ?></a>
			</span> |
			<span class="gallery-date"><?= get_the_date() ?></span> |
			<?php the_terms($id, 'video-category', '', ', ') ?>
		</p>
		<div class="gallery-excerpt">
			<?= remove_emoji(get_the_excerpt()) ?>
		</div>
		<a href="<?= get_permalink() ?>" class="gallery-more">Watch Now</a>
	</div>
</div>


<?php 
// END
