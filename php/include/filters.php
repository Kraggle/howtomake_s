<?php

// Show Custom Fields on editor
add_filter('acf/settings/remove_wp_meta_box', '__return_false');


add_filter('upload_mimes', 'my_mime_types', 1, 1);
function my_mime_types($mime_types) {
	$mime_types['jpegcharsetbinary'] = 'image/jpegcharsetbinary';     // Adding .svg extension

	return $mime_types;
}

// disable srcset on frontend
function disable_wp_responsive_images() {
	return 1;
}
add_filter('max_srcset_image_width', 'disable_wp_responsive_images');

add_filter('the_content', function ($content) {
	empty_error_log();

	$doc = phpQuery::newDocument($content);

	$sizes = get_all_image_sizes();
	// error_log(json_encode($sizes, JSON_PRETTY_PRINT));

	// Set the content images to the biggest possible without go bigger then the max
	foreach ($doc['img'] as $img) {
		$img = pq($img);
		if ($img->hasClass('emoji')) continue;

		$img->removeAttr('style');
		$wrap = $img->parent();

		if (!$wrap->hasClass('image-wrap')) {
			// $img->insertBefore($wrap);
			$img->wrap('<div class="image-wrap">');
			$wrap = $img->parent();
		}

		// error_log($img->htmlOuter());

		$get = (object) [
			'size' => 0,
			'class' => 0,
			'id' => 0
		];

		$size = [];

		$classes = explode(' ', $img->attr('class'));
		foreach ($classes as $class) {
			if (preg_match('/wp-image-(\d+)/', $class, $id))
				$get->id = intval($id[1]);
			elseif (preg_match('/size-(\w+)/', $class, $size)) {
				$get->class = $size[0];
				$get->size = $size[1];
			}

			if ($get->id && $get->size) break;
		}

		$meta = wp_get_attachment_metadata($get->id);

		$smaller = false;

		if ($get->size !== 'post') {

			if (isset($meta['sizes']['post'])) {
				$size = $meta['sizes']['post'];
				$info = pathinfo($meta['file']);

				$get->file = $info['dirname'] . '/' . $size['file'];
				$get->width = $size['width'];
				$get->height = $size['height'];
				$get->size = 'post';
			} else {
				// error_log(json_encode($meta, JSON_PRETTY_PRINT));
				// error_log(json_encode($get, JSON_PRETTY_PRINT));

				if ($meta['width'] <= $sizes['post']['width']) {

					$get->file = $meta['file'];
					$get->width = $meta['width'];
					$get->height = $meta['height'];
					$get->size = 'full';

					pq($img)->attr('style', "max-width: {$get->width}px;")->addClass('image-center');
					pq($img)->parents('figure')->removeClass('aligncenter');
					$smaller = true;
				}

				// TODO: Find a post that the previous statement does not work
				// foreach ($meta['sizes'] as $size => $value) {

				// }
			}

			$imageUrl = wp_get_attachment_image_url($get->id, "{$get->width}x{$get->height}", false);
			pq($img)->removeClass($get->class)->addClass("size-{$get->size}")
				->attr('src', $imageUrl);
		} else {
			$get->width = $meta['sizes']['post']['width'];
			$get->height = $meta['sizes']['post']['height'];
		}

		// pq($img)->attr('width', 'initial')->attr('height', 'initial');
		$height = $get->height / ($smaller ? 850 : $get->width) * 100;
		pq($wrap)->attr('style', "padding-bottom: $height%");
	}

	// Change the page jump links to not include the page so it does not reload
	$uri = preg_quote($_SERVER['REQUEST_URI'], '/');
	foreach ($doc['a'] as $link) {
		$href = pq($link)->attr('href');

		if (preg_match("/$uri{0,1}(#.+)/", $href, $match))
			pq($link)->attr('href', $match[1]);
	}

	ob_start();
	print $doc->htmlOuter();
	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}, 3);

add_filter('the_content', function ($content) {

	$doc = phpQuery::newDocument($content);

	// Wrap any iframe
	foreach ($doc['iframe'] as $iframe) {
		$wrap = pq($iframe)->parent();
		if ($wrap->hasClass('video-wrap') || $wrap->hasClass('iframe-wrap')) continue;
		if ($wrap->hasClass('content-wrap'))
			pq($iframe)->wrap('<div class="iframe-wrap">');
		else $wrap->addClass('iframe-wrap');
	}

	pq('h1')->attr('itemprop', 'headline');

	ob_start();
	print $doc->htmlOuter();
	$content = ob_get_contents();
	ob_end_clean();

	return $content;
}, 20);

// disable Gutenberg
add_filter('use_block_editor_for_post', '__return_false', 10);



function htm_script_as_module($tag, $handle, $src) {
	if (preg_match('/^module-/', $handle)) {
		$tag = '<script type="module" src="' . esc_url($src) . '" id="' . $handle . '"></script>';
	}

	return $tag;
}
add_filter('script_loader_tag', 'htm_script_as_module', 10, 3);
