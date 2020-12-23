<?
/*
YARPP Template: Simple
Description: This template gives you a random other post in case there are no related posts
Author: Kraggle
*/ ?>

<? if (have_posts()) { ?>
	<h3 class="related-title">Related Posts</h3>
	<div class="related-wrap">

		<? while (have_posts()) {
			the_post();

			get_template_part('views/category/post-related', 'none');
		} ?>
	</div>
<? }

// END
