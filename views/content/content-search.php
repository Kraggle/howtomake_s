<?php

/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */

?>

<article <? post_class() ?>>
	<header>
		<h2 class="entry-title"><a href="<? echo get_permalink() ?>"><? echo get_the_title() ?></a></h2>
		<? if (get_post_type() === 'post') {
			include(get_template_directory() . '/views/partials/entry-meta.php');
		} ?>
	</header>
	<div class="entry-summary">
		<? the_excerpt() ?>
	</div>
</article>

<? 
// END
