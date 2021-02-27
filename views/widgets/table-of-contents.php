<?php
// This is for including a table of contents as a sidebar

$toc = to_object(get_field('toc'));
if (!$toc)
	$toc = (object) [
		'active' => false
	];

if (!$toc->active) exit;

$el = $toc->title_element;

?>

<aside class="sidebar">
	<<?= $el ?> class="title"><?php $toc->title ?><?= $toc->title ?: 'Table of Contents' ?></<?= $el ?>>
	<div class="content <?= $toc->type ?>">
		<?php foreach ($toc->items as $item) {
			$link = $item->id ? "#$item->id" : '' ?>
			<a href="<?= "{$item->link}$link" ?>" class="item"><?= $item->name ?></a>
		<?php } ?>
	</div>
</aside>

<?php

// END
