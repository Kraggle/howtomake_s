<? // HTML partials/entry-meta 
?>

<article id="l_<? echo $id ?>" class="related">

	<a href="<? echo get_permalink() ?>" class="related-image <? echo get_post_type() ?>">
		<img alt="<? the_title_attribute() ?>" src="<? echo get_the_post_thumbnail_url(null, 'medium') ?>">
	</a>
	<h2 class="related-title">
		<a href="<? echo get_permalink() ?>"><? echo get_the_title() ?></a>
	</h2>
	<p class="related-meta">by
		<span class="related-author">
			<a title="Video by" href="<? the_channel(null, 'link') ?>"><? the_channel(null, 'name'); ?></a>
		</span> |
		<span class="related-date"><? echo get_the_date() ?></span> |
		<? the_terms($id, 'video-category', '', ', ') ?>
	</p>
	<div class="related-excerpt">
		<? echo remove_emoji(get_the_excerpt()) ?>
	</div>
</article>


<? 
// END
