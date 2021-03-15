<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package htm_s
 */

// This is used to split the content and the related posts apart 
ob_start();
the_content();
$content = ob_get_contents();
ob_end_clean();

$doc = phpQuery::newDocument($content);
$related = pq('.yarpp-related');
$entries = $related->find('.list .entry')->slice(0, 5);
$related->find('.list')->html($entries);

$doc->find('.yarpp-related')->remove();

ob_start();
print $doc->htmlOuter();
$content = ob_get_contents();
ob_end_clean();

ob_start();
print $related->htmlOuter();
$related = ob_get_contents();
ob_end_clean();

the_title('<h1 class="title" itemprop="headline">', '</h1>');

$toc = to_object(get_field('toc'));
if (!$toc)
	$toc = (object) [
		'active' => false
	]; ?>

<?= content_schema_meta(); ?>

<p class="meta">by
	<span class="author">
		<a href="<?= get_author_posts_url(get_the_author_meta('ID')) ?>" title="Posts by " rel="author">
			<?= get_the_author() ?>
		</a>
	</span> |
	<span class="date"><?= get_the_date() ?></span> |
	<?php the_category(', ') ?>
</p>

<?php if ($desc = get_field('description')) { ?>
	<div class="description">
		<?= $desc ?>
	</div>
<?php } ?>

<?php htm_s_post_thumbnail(); ?>

<div class="wrapper">
	<?php if ($toc->active) { ?>
		<div class="inner-wrap">
		<?php get_template_part('views/widgets/table-of-contents');
	} ?>

		<div class="content-wrap" itemprop="articleBody">
			<?= $content ?>
		</div>

		<?php if ($toc->active) { ?>
		</div>
	<?php } ?>

	<?php get_template_part('views/widgets/side-panel') ?>
</div>

<?= $related ?>

<?php // htm_s_entry_footer(); 
// END
