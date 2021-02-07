<?php

$nl = "<br />\n";

function indent($count = 1, $symbol = ' &raquo; ') {
	$indent = '';
	for ($i = 0; $i < $count; $i++) {
		$indent .= $symbol;
	}
	return $indent;
}

if (!function_exists('getYoutubeService')) {

	function getYoutubeService() {
		// INFO: Changed this to try the API key before resuming, as with 3 
		// INFO: API keys we can triple the quota in a day 

		$appName = 'Youtube Scraper';
		$apiKeys = [ // Youtube API Keys
			'AIzaSyByB7ZeVa4qIN9TPeAlgG6tJtkYoT8Xme8',
			'AIzaSyDtGJtBPXdcWfBswi3mJSezfoj23Fr2T1A',
			'AIzaSyD7iDUybQmkxls-Ge3kQ_sGHLsNbAxvc00',
		];

		// Google API init
		$client = new Google_Client();
		$client->setApplicationName($appName);

		foreach ($apiKeys as $key) {

			$client->setDeveloperKey($key);
			$service = new Google_Service_YouTube($client);

			try {
				$results = $service->i18nRegions->listI18nRegions('id');

				if ($results)
					break;
			} catch (Exception $e) {
				videoLogger::getInstance()->put('Exception: ' . $e->getMessage());
			}
		}

		return $service;
	}
}

/**
 * Get enhanced video information from Youtube
 *
 * @param array $features An array of features to save, all if null. default: null
 * @return void
 */
function getExtraYoutubeInfo(array $features = null) {

	global $log;

	$videos = get_results(
		"SELECT
		p.ID AS id,
		m.meta_value AS yt_id,
		p.post_title AS title
		FROM wp_posts p
		INNER JOIN wp_postmeta m
			ON p.ID = m.post_id
		WHERE p.post_type = 'video'
		AND p.post_status = 'publish'
		AND m.meta_key = 'youtube_video_id'
		AND (p.ID NOT IN (SELECT
			tr.object_id
		FROM wp_term_relationships tr
			INNER JOIN wp_term_taxonomy tt
			ON tr.term_taxonomy_id = tt.term_taxonomy_id
		WHERE tt.taxonomy = 'post_tag')
		OR p.ID NOT IN (SELECT
			pm.post_id
		FROM wp_postmeta pm
		WHERE pm.meta_key = 'video_duration_raw'))"
	);

	$videoIds = list_ids($videos, 'yt_id');

	if (!count($videoIds))
		return;

	$log->put('Getting extra youtube information!');
	$log->put('There are ' . count($videoIds) . ' that still have missing info.');

	// Process in batches to reduce API calls. 50 is Youtube's pagination limit.
	$chunkedVideoIDs = array_chunk($videoIds, 50, true);

	foreach ($chunkedVideoIDs as $ids) {

		// Get more information for the videos
		$optParams = array(
			'id' => implode(',', $ids),
		);

		try {
			$results = getYoutubeService()->videos->listVideos('id,snippet,contentDetails,statistics,topicDetails', $optParams);

			foreach ($results->items as $item) {
				$i = array_search($item->id, array_column($videos, 'yt_id'));
				if (!is_numeric($i)) continue; // Skip if video ID not in list, shouldn't happen
				$post = $videos[$i];

				$log->put(indent() . "Adding extra information for: {$post->title}");

				// Attach keywords as tags to post
				if (is_null($features) || in_array('keywords', $features)) {
					wp_set_post_tags($post->id, $item->snippet->tags, true);
					set_video_categories_from_tags($post->id, $item->snippet->tags, $post->title);
				}

				// Video Duration
				if (is_null($features) || in_array('duration', $features)) {
					// ISO_8601 Format
					update_post_meta($post->id, 'video_duration_raw', $item->contentDetails->duration);

					$di  = new DateInterval($item->contentDetails->duration);
					$sec = ceil($di->days * 86400 + $di->h * 3600 + $di->i * 60 + $di->s);
					add_post_meta($post->id, 'video_duration_seconds', $sec, true);
				}

				// INFO: Added this to grab images, buy only if it's in features
				// Video Featured Image
				if (is_array($features) && in_array('image', $features))
					save_video_image_for_post($post->id, $item);
			}
		} catch (Exception $e) {
			$log->put('Exception: ' . $e->getMessage());
		}

		test_restart();
	}
}

class videoLogger {

	private
		$file,
		$format;
	private static $instance;

	public function __construct($filename, $format = '[d-M-Y H:i:s]') {
		$this->file = $filename;
		$this->format = $format;

		if (!file_exists($filename))
			file_put_contents($this->file, '');
	}

	public static function getInstance($filename = '', $format = '[d-M-Y H:i:s]') {
		return !isset(self::$instance) ?
			self::$instance = new videoLogger($filename, $format) :
			self::$instance;
	}

	public function put($insert) {
		$timestamp = date($this->format);
		file_put_contents($this->file, "$timestamp &raquo; $insert\n", FILE_APPEND);
	}

	public function get() {
		$content = file_get_contents($this->file);
		return $content;
	}
}

function get_channel_video_ids($term_id) {
	return get_results(
		"SELECT
		m.meta_value AS yt_id,
		p.ID AS post_id
		FROM wp_term_relationships r
		INNER JOIN wp_posts p
			ON r.object_id = p.ID
		INNER JOIN wp_postmeta AS m
			ON m.post_id = p.ID
		WHERE r.term_taxonomy_id = $term_id
		AND m.meta_key = 'youtube_video_id'"
	);
}

function get_playlist_items($ytUploadsId, $nextPageToken = null) {
	try {

		$query = [
			'maxResults' => 50,
			'playlistId' => $ytUploadsId
		];

		if ($nextPageToken)
			$query['pageToken'] = $nextPageToken;

		return getYoutubeService()->playlistItems->listPlaylistItems('snippet', $query);
	} catch (Exception $e) {
		videoLogger::getInstance()->put('Exception: ' . $e->getMessage());
	}
}
