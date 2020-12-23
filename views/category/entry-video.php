<? // HTML partials/entry-meta 
?>


<article id="l_<? echo $id ?>" class="entry">

	<a href="<? echo get_permalink() ?>" class="entry-image <? echo get_post_type() ?>">
		<img alt="<? the_title_attribute() ?>" src="<? echo get_the_post_thumbnail_url(null, 'medium') ?>">
	</a>
	<h2 class="entry-title">
		<a href="<? echo get_permalink() ?>"><? echo get_the_title() ?></a>
	</h2>
	<!-- <time class="updated" datetime="<? echo get_post_time('c', true) ?>"></time> -->
	<p class="entry-meta">by
		<span class="entry-author">
			<a title="Video by" href="<? the_channel(null, 'link') ?>"><? the_channel(null, 'name'); ?></a>
		</span> |
		<span class="entry-date"><? echo get_the_date() ?></span> |
		<? the_terms($id, 'video-category', '', ', ') ?>
	</p>
	<div class="entry-excerpt">
		<? echo remove_emoji(get_the_excerpt()) ?>
	</div>
</article>


<? 
// END
