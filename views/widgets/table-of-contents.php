<?php
// This is for including a table of contents as a sidebar

$toc = to_object(get_field('toc'));
if (!$toc)
	$toc = (object) [
		'active' => false
	];

if (!$toc->active) exit;

$el = $toc->title->element;

if ($style = $toc->title->style)
	$style = ' style="' . implode(';', explode(PHP_EOL, $style)) . ';"'; ?>

<aside class="sidebar">

	<<?= $el ?> class="title" <?= $style ?: '' ?>><?= $toc->title->text ?: 'Table of Contents' ?></<?= $el ?>>

	<div class="content <?= $toc->type ?>">
		<?php foreach ($toc->items as $item) {
			$subs = $item->sub_items;
			$item = $item->item->item;
			$link = $item->id ? "#$item->id" : ''; ?>
			<a href="<?= "{$item->link}$link" ?>" class="item"><?= $item->name ?></a>
			<?php if ($subs) {
				foreach ($subs as $sub) {
					$item = $sub->item;
					$link = $item->id ? "#$item->id" : ''; ?>
					<a href="<?= "{$item->link}$link" ?>" class="sub item"><?= $item->name ?></a>
		<?php }
			}
		} ?>
	</div>
</aside>

<?php

// END
