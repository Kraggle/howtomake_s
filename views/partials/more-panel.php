<?
// The more section for the bottom of pages
?>
<div class="more-panel">
	<div class="more-box">
		<h3 class="more-title">About Us</h3>
		<? get_template_part('views/partials/about-us') ?>
	</div>
	<div class="more-box">
		<h3 class="more-title">Articles</h3>
		<div class="more-links">
			<? $list = wp_list_categories([
				'title_li'           => '',
				'style'              => 'none',
				'echo'               => false,
				'use_desc_for_title' => false,
				'taxonomy'           => 'category'
			]);
			$list = trim(str_replace('<br />',  '', $list));
			echo $list; ?>
		</div>
	</div>
	<div class="more-box">
		<h3 class="more-title">Videos</h3>
		<div class="more-links">
			<? $list = wp_list_categories([
				'title_li'           => '',
				'style'              => 'none',
				'echo'               => false,
				'use_desc_for_title' => false,
				'taxonomy'           => 'video-category'
			]);
			$list = trim(str_replace('<br />',  '', $list));
			echo $list ?>
		</div>
	</div>
</div>