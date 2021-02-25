<?php
// This is for including a table of contents as a sidebar

$sb = to_object(get_field('side_bar'));
if (!$sb)
	$sb = (object) [
		'active' => false
	];

if (!$sb->active) exit;

?>

<aside class="sidebar">
	<h4 class="title"><?php $sb->title ?><?= $sb->title ?: 'Table of Contents' ?></h4>
	<div class="content <?= $sb->type ?>">
		<?php foreach ($sb->items as $item) {
			$link = $item->id ? "#$item->id" : '' ?>
			<a href="<?= "{$item->link}$link" ?>" class="item"><?= $item->name ?></a>
		<?php } ?>
	</div>
</aside>

<?php

// END
