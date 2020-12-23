<?php

function read_time($content) {
	return ceil(str_word_count(strip_tags($content)) / 250);
}

function get_duration($id) {

	$api_key = 'AIzaSyByB7ZeVa4qIN9TPeAlgG6tJtkYoT8Xme8';

	// video json data
	$json_result = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id=$id&key=$api_key");
	$result = json_decode($json_result);

	// video duration data
	if (!count($result->items)) {
		return 0;
	}

	$duration_encoded = $result->items[0]->contentDetails->duration;

	// duration
	$interval = new DateInterval($duration_encoded);
	$minutes = ceil(($interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s) / 60);

	return $minutes;
}

function get_channel_logo($id, $termId) {

	if (!$id) return null;

	$api_key = 'AIzaSyByB7ZeVa4qIN9TPeAlgG6tJtkYoT8Xme8';

	$json_result = file_get_contents("https://www.googleapis.com/youtube/v3/channels?part=snippet&id=$id&key=$api_key");
	$result = json_decode($json_result);

	if ($result->items && count($result->items)) {
		$thumb = $result->items[0]->snippet->thumbnails->high;

		include_once(ROOT . 'wp-admin/includes/image.php');

		$imageType = end(explode('/', getimagesize($thumb->url)['mime']));
		$fileName = date('dmY') . (int) microtime(true) . '.' . $imageType;

		$uploadFile = wp_upload_dir()['path'] . '/' . $fileName;
		$saveFile = fopen($uploadFile, 'w');
		fwrite($saveFile, file_get_contents($thumb->url));
		fclose($saveFile);

		$fileType = wp_check_filetype(basename($fileName), null);
		$attachment = array(
			'post_mime_type' => $fileType['type'],
			'post_title' => $fileName,
			'post_content' => '',
			'post_status' => 'inherit'
		);

		$attachId = wp_insert_attachment($attachment, $uploadFile);
		$fullSizePath = get_attached_file(get_post($attachId)->ID);
		$attachData = wp_generate_attachment_metadata($attachId, $fullSizePath);
		wp_update_attachment_metadata($attachId, $attachData);

		add_term_meta($termId, 'actual_logo', $attachId);

		return $attachId;
	}
}
