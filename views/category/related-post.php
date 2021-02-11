<?php // HTML partials/entry-meta 
?>

<article id="l_<?= $id ?>" class="related">

	<a href="<?= get_permalink() ?>" class="related-image <?= get_post_type() ?>">
		<img alt="<?php the_title_attribute() ?>" src="<?= get_the_post_thumbnail_url($id, 'menu') ?>">
	</a>
	<h2 class="related-title">
		<a href="<?= get_permalink() ?>"><?= get_the_title() ?></a>
	</h2>
	<p class="related-meta">by
		<span class="related-author">
			<a href="<?= get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
				<?= get_the_author() ?>
			</a>
		</span> |
		<span class="related-date"><?= get_the_date('M jS, Y') ?></span> |
		<?php the_category(', ') ?>
	<div class="related-excerpt">
		<?= remove_emoji(get_the_excerpt()) ?>
	</div>
	</p>
</article>


<?php 
// END
