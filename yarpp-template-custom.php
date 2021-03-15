<?php
/*
YARPP Template: Custom
Description: This template gives you a random other post in case there are no related posts
Author: Kraggle
*/ ?>


<?php if (have_posts()) { ?>
	<h3 class="related-title">Related Posts</h3>

	<div class="list">

		<?php while (have_posts()) {
			the_post();

			get_template_part('views/category/search', get_post_type());
		} ?>

	</div>

<?php }
// END
