<?php

/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */

?>

<article <?php post_class() ?>>
	<header>
		<h2 class="entry-title"><a href="<?= get_permalink() ?>"><?= get_the_title() ?></a></h2>
		<?php if (get_post_type() === 'post') {
			include(get_template_directory() . '/views/partials/entry-meta.php');
		} ?>
	</header>
	<div class="entry-summary">
		<?php the_excerpt() ?>
	</div>
</article>

<?php 
// END
