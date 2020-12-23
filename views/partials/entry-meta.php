<? // HTML partials/entry-meta 
?>

<time class="updated" datetime="<? echo get_post_time('c', true) ?>"><? echo get_the_date() ?></time>
<p class="byline author vcard">
	<? echo  __('By', 'htm_s')  ?>
	<a href="<? echo get_author_posts_url(get_the_author_meta('ID')) ?>" rel="author" class="fn">
		<? echo  get_the_author()  ?>
	</a>
</p>

<? 
// END
