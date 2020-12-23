<? // HTML partials/entry-meta 
?>

<div id="g_<? echo $id ?>" class="gallery-item<? echo $args['i'] == 0 ? ' active' : '' ?>" index="<? echo $args['i'] ?>">
	<div class="gallery-image" style="background-image:url(<? echo get_the_post_thumbnail_url(null, 'large') ?>)"></div>
	<div class="gallery-content">
		<h2 class="gallery-title">
			<? echo get_the_title() ?>
		</h2>
		<p class="gallery-meta">by
			<span class="gallery-author">
				<a href="<? echo get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
					<? echo  get_the_author() ?>
				</a>
			</span> |
			<span class="gallery-date"><? echo get_the_date() ?></span> |
			<? the_category(', ') ?>
		</p>
		<div class="gallery-excerpt">
			<? echo remove_emoji(get_the_excerpt()) ?>
		</div>
		<a href="<? echo get_permalink() ?>" class="gallery-more">Read More</a>
	</div>
</div>


<? 
// END
