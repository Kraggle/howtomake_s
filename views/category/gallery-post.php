<?php // HTML partials/entry-meta 
?>

<div id="g_<?= $id ?>" class="gallery-item<?= $args['i'] == 0 ? ' active' : '' ?>" index="<?= $args['i'] ?>">
	<div class="gallery-image" style="background-image:url(<?= get_the_post_thumbnail_url($id, 'featured') ?>)"></div>
	<div class="gallery-content">
		<h2 class="gallery-title">
			<?= get_the_title() ?>
		</h2>
		<p class="gallery-meta">by
			<span class="gallery-author">
				<a href="<?= get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
					<?= get_the_author() ?>
				</a>
			</span> |
			<span class="gallery-date"><?= get_the_date() ?></span> |
			<?php the_category(', ') ?>
		</p>
		<div class="gallery-excerpt">
			<?= remove_emoji(get_the_excerpt()) ?>
		</div>
		<a href="<?= get_permalink() ?>" class="gallery-more">Read More</a>
	</div>
</div>


<?php 
// END
