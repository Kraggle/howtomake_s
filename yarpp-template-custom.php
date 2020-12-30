<?php
/*
YARPP Template: Custom
Description: This template gives you a random other post in case there are no related posts
Author: Kraggle
*/ ?>


<?php if (have_posts()) { ?>
	<h3 class="related-title">Related Posts</h3>

	<div class="related-wrap">

		<?php while (have_posts()) {
			the_post();

			get_template_part('views/category/related', get_post_type());
		} ?>
	<?php } ?>

	<div class="related-part" part="1"></div>
	<div class="related-part" part="2"></div>
	<div class="related-part" part="3"></div>
	<div class="related-part" part="4"></div>
	<div class="related-part" part="5"></div>

	</div>

	<?php
// END
