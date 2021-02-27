<?php

/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */

?>

<h1 class="title"><?php esc_html_e('404! Not Found', 'htm_s'); ?></h1>

<div class="wrapper">
	<div class="content-wrap">

		<p><?php esc_html_e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'htm_s'); ?></p>

		<?php get_template_part('views/widgets/search-form') ?>

	</div>

	<?php get_template_part('views/widgets/side-panel') ?>
</div>

<?php 
// END
