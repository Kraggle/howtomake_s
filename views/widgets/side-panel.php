<?php
// A panel for the home page for additional content and links

$bar = to_object(get_field('sidebar'));
$sticky = false;

if (!$bar) $bar = (object) [
	'existing_panels' => false,
	'panels'          => false,
	'default'         => true
];

$bar = $bar;
if ($bar->existing_panels)
	$bar = to_object(get_field('bar', $bar->existing_panels));

if (!$bar->panels && $bar->default)
	$bar = to_object(get_field('bar', 30620))->sidebar;

if ($bar->panels) { ?>

	<div class="more-side">

		<div class="more-part" part="1"></div>
		<div class="more-part" part="2"></div>
		<div class="more-part" part="3"></div>

		<?php foreach ($bar->panels as $panel) {
			$panel = $panel->panel;
			if ($panel->existing_panel)
				$panel = to_object(get_field('single', $panel->existing_panel));

			if ($panel->args->sticky) {
				if (!$sticky) $sticky = true;
				else $panel->args->sticky = false;
			}

			do_more_panel(
				$panel->existing_content ? get_field('content', $panel->existing_content) : $panel->content,
				$panel->title,
				(array) $panel->args
			);
		} ?>
	</div>
<?php }
// END
