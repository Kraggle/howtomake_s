<? // HTML partials/entry-meta 
?>

<article id="l_<? echo $id ?>" class="entry <? echo get_post_type() ?>">

	<a href="<? echo get_permalink() ?>" class="entry-image <? echo get_post_type() ?>">
		<img alt="<? the_title_attribute() ?>" src="<? echo get_the_post_thumbnail_url(null, 'medium') ?>">
	</a>

	<h2 class="entry-title">
		<a href="<? echo get_permalink() ?>" title="<? echo get_the_title() ?>"><? echo get_the_title() ?></a>
	</h2>

	<p class="entry-meta">by
		<span class="entry-author">
			<a href="<? echo get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
				<? echo  get_the_author() ?>
			</a>
		</span> |
		<span class="entry-date"><? echo get_the_date('M jS, Y') ?></span> |
		<? the_category(', ') ?>
	</p>

	<p class="entry-detail">
		<span class="detail-time">
			<? echo get_font_awesome_icon('history', 'light') ?>
			<? $time = get_read_time();
			echo "$time min" . ($time == 1 ? '' : 's'); ?>
		</span>
		<span class="spacer"></span>
		<span class="detail-type">
			<? echo get_font_awesome_icon('book', 'light') ?>
			article
		</span>
	</p>

</article>


<? 
// END
