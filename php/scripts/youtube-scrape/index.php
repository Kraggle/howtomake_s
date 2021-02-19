<?php
// INFO: A lot has changed in this scraper.
// INFO:  - It now checks for dead links and duplicates
// INFO:  - All options have been removed while running it as they are now automatic
// INFO:  - The listSearch has been removed as that was quota hungry consuming 100 units each time
// INFO:  - All new youtube calls only consume 1 unit for each call
// INFO:  - There is now a few methods of skipping if the channel has already run today
// INFO:  - There is a timer that will end the script before a timeout and resume the script again

// ------------------------------------------------------------------


if (!defined('ABSPATH')) {
	require_once("../../../../../../wp-load.php");
}

// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
require_once(ABSPATH . 'wp-admin/includes/image.php');

require_once __DIR__ . '/../../include/functions.php';

// ================================== Settings End =======================================

global $wpdb;

if ($force = isset($_GET['force']) && $_GET['force']) {
	$older = date('Y-m-d', strtotime('-1 week'));
	update_option('last_youtube_data_update', $older);
	$wpdb->query(
		"UPDATE {$wpdb->termmeta} SET meta_value = '$older' WHERE meta_key In ('yt_last_update', 'yt_last_removal')"
	);
}

?>

<html>

<head>
	<title>Youtube Scraper - HTMMFH</title>

	<style>
		.logs pre {
			line-height: 1.4;
			font-size: 14px;
			font-family: monospace;
		}
	</style>
</head>

<body>
	<form method="GET" action="">
		<input type="hidden" name="action" value="import" />
		<?php $isChecked = $force ? 'checked' : '' ?>
		<p>
			<label>
				<span>Force update: </span>
				<input type="checkbox" name="force" <?= $isChecked ?> />
			</label>
		</p>
		<input type="submit" value="START UPDATE" />
	</form>
	<input id="kill-switch" type="button" value="KILL SCRIPT" style="position: fixed; top: 20px; right: 20px;" />
	<input id="cancel-kill" type="button" value="CANCEL KILL" style="position: fixed; top: 50px; right: 20px;" />

	<div class="logs"></div>
	<div id="scroller"></div>
	<script type="module" src="index.js"></script>
</body>

</html>

<?php

$postAuthor = 1;
$maxResults = 50; // max: 50

$maxTime = 80;
// $maxTime = (ini_get('max_execution_time') - 20);
global $endAt;
$endAt = strtotime("+{$maxTime} seconds");

if (!file_exists('./info/'))
	mkdir('./info/');

if (!file_exists('./data/'))
	mkdir('./data/');

global $log;
$log = videoLogger::getInstance('./info/log' . date('[d-M-Y H]') . '.txt');

if (!file_exists('running.json'))
	file_put_contents('running.json', json_encode(['running' => false]));
$is = json_decode(file_get_contents('running.json'));
if ($is->running) {
	$log->put('<span style="color:red">Either `running.json` has not updated or someone or something else is already running this script.</span>');
	exit;
}

if (!file_exists('kill-switch.json'))
	file_put_contents('kill-switch.json', json_encode(['kill' => false]));
$do = json_decode(file_get_contents('kill-switch.json'));
if ($do->kill) {
	$log->put('<span style="color:red">Someone has forced the task to stop, you must allow it to resume with `CANCEL KILL`</span>');
	exit;
}

file_put_contents('running.json', json_encode(['running' => true]));

$uploadDir = wp_upload_dir()['basedir'] . '/yt-thumb';
if (!file_exists($uploadDir)) wp_mkdir_p($uploadDir);

if (isset($_GET['action']) && $_GET['action'] === 'import') {

	$log->put('Getting the channels and checking if they need updating.');

	// Get Channels
	$channels = get_terms(['taxonomy' => 'video-channel', 'hide_empty' => false]);

	$last = get_option('last_youtube_data_update', date('Y-m-d', strtotime('-1 week')));
	if (date('Y-m-d', strtotime('-1 days')) > $last) {
		$log->put('Saving the youtube data for ' . count($channels) . ' channels!');
		get_youtube_channel_data($channels);
		update_option('last_youtube_data_update', date('Y-m-d'));
	}

	foreach ($channels as $channel) {
		// INFO: This now checks if it was updated today already and skips if it was
		$lastUpdate = get_term_meta($channel->term_id, 'yt_last_update', true) ?: date('Y-m-d', strtotime('-1 week'));
		if (date('Y-m-d', strtotime('-1 days')) < $lastUpdate) {
			$log->put("Skipping <b>{$channel->name}</b> as it was updated within the last day.");
			continue;
		}

		$log->put("<b>{$channel->name}</b>:");

		$importerEnabled = get_field('importer_enabled', $channel, true);

		// Skip unless importer is enabled
		if (!$importerEnabled || !is_array($importerEnabled) || !$importerEnabled[0]) {
			$log->put(indent() . "Skipping, importer disabled");
			continue;
		}

		$channel->yt_id = get_field('yt_channel_id', $channel, true);
		$ytUploadsId = get_term_meta($channel->term_id, 'yt_uploads_id', true);

		if (!$ytUploadsId) {
			$response = getYoutubeService()->channels->listChannels('contentDetails', [
				'id' => $channel->yt_id
			]);

			$ytUploadsId = $response->items[0]->contentDetails->relatedPlaylists->uploads;
			add_term_meta($channel->term_id, 'yt_uploads_id', $ytUploadsId, true);
		}

		$videoCategories = get_field('video_categories', $channel, true);

		// INFO: This whole section will only run once every day
		// INFO: It checks for dead links and duplicate video posts
		// INFO: It now also updates any video meta
		$lastRemoval = get_term_meta($channel->term_id, 'yt_last_removal', true) ?: date('Y-m-d', strtotime('-1 week'));
		if (date('Y-m-d', strtotime('-1 days')) > $lastRemoval) {

			$log->put(indent() . "Checking for dead links, duplicates and updating the video data.");

			// Get all youtube ids and post ids of existing channel videos
			$posts = get_channel_video_ids($channel->term_id);

			// This is here as there are a few videos that were duplicated and this is 
			// probably the best place to remove them as we are already going through them.
			$saved_ids = (object) [];
			$ids = [];
			foreach ($posts as $post) {
				if ($saved_ids->{$post->yt_id}) {
					// delete the post as its a duplicate
					wp_delete_post($post->id, true);
					$wpdb->query("DELETE FROM {$wpdb->videometa} WHERE post_id = {$post->id};");
					$log->put(indent(2) . "Video @{$post->id} has been deleted as it was a duplicate!");
					continue;
				}
				$saved_ids->{$post->yt_id} = $post->id;
				$ids[] = $post;
			}
			unset($posts);

			$itemsPath = "./data/{$channel->yt_id}.json";
			if (!file_exists($itemsPath))
				file_put_contents($itemsPath, json_encode([]));
			$items = json_decode(file_get_contents($itemsPath));

			if (!count($items)) {
				$log->put(indent(2) . 'Acquiring the video data from youtube for ' . count($ids) . ' videos.');
				// This is here to remove any dead youtube links, it's the most cost effective way to do it.
				$chunks = array_chunk($ids, 50, true);
				foreach ($chunks as $chunk) {

					try {
						$results = getYoutubeService()->videos->listVideos('id,snippet,contentDetails,statistics,topicDetails', [
							'id' => list_ids($chunk, 'yt_id', 'string'),
						]);

						$items = array_merge($items, $results->items);
					} catch (Exception $e) {
						logger('Exception: ' . $e->getMessage());
					}
				}

				file_put_contents($itemsPath, json_encode($items));
				test_restart();
			}

			$log->put(indent(2) . 'Saving the youtube data for ' . count($items) . ' videos! This may take some time.');

			$index = 0;
			foreach ($items as &$item) {
				if ($item->is_saved) continue;

				if ($index % 10 == 0) {
					file_put_contents($itemsPath, json_encode($items));
					test_restart(false);
				}

				$post_id = $saved_ids->{$item->id};

				save_youtube_data($post_id, $item);

				$item->post_id = $post_id;
				$item->is_saved = true;
				$index++;
			}

			$existing_ids = list_ids($items, 'id');
			foreach ($saved_ids as $yt_id => $id) {
				// The link no longer exists, delete the post
				if (!in_array($yt_id, $existing_ids)) {
					wp_delete_post($id, true);
					$wpdb->query("DELETE FROM {$wpdb->videometa} WHERE post_id = $id;");
					$log->put(indent(2) . "Video @{$id} has been deleted as it no longer existed!");
				}
			}

			file_put_contents($itemsPath, json_encode([]));
			update_term_meta($channel->term_id, 'yt_last_removal', date('Y-m-d'), true);
		}

		$posts = get_channel_video_ids($channel->term_id);

		$nextPageToken = null;
		$temp = [];
		do {
			$result = get_playlist_items($ytUploadsId, $nextPageToken);
			$nextPageToken = $result->nextPageToken;
			$temp = array_merge($temp, $result->items);
		} while ($nextPageToken);

		$items = [];
		$yt_ids = list_ids($posts, 'yt_id');
		foreach ($temp as $item) {
			if (!in_array($item->snippet->resourceId->videoId, $yt_ids))
				$items[] = $item;
		}

		$log->put(indent() . "We have: " . count($posts) . " and there are " . count($temp) . " in total.");
		$log->put(indent() . "Results: " . count($items));

		foreach ($items as $item) {
			$snippet = $item->snippet;
			$videoId = $snippet->resourceId->videoId;
			$postdate = new DateTime($snippet->publishedAt);

			// This has been added to clean up the title from the source
			$snippet->title = friendly_title($snippet->title);

			$log->put(indent(2) . "{$videoId} - {$snippet->title}");

			// Insert the post into the database
			$postId = wp_insert_post([
				'post_title'   => $snippet->title,
				'post_content' => format_youtube_description($snippet->description),
				'post_status'  => 'publish',
				'post_author'  => $postAuthor,
				'post_type'	   => 'video',
				'post_date'    => $postdate->format('Y-m-d H:i:s')
			]);

			if ($postId) {
				$log->put(indent(3) . "Video successfully added @ " . strval($postId));

				update_field('youtube_video_id', $videoId, $postId);

				// Assign to channel
				wp_set_object_terms($postId, $channel->term_taxonomy_id, 'video-channel');
				wp_set_object_terms($postId, $videoCategories, 'video-category');

				// INFO: Updated this to get the largest available image size (Kraggle)
				if ($imgId = save_video_image_for_post($postId, $item))
					$log->put(indent(3) . 'Successfully created the video thumbnails @ ' . strval($imgId));
				else
					$log->put(indent(3) . 'Creating the video thumbnails failed.');
			} else
				$log->put(indent(3) . 'Failed to add this video.');

			test_restart();
		}


		$log->put(indent() . 'Finished channel update, setting last update to ' . date('Y-m-d'));
		update_term_meta($channel->term_id, 'yt_last_update', date('Y-m-d'));

		test_restart();
	}

	getExtraYoutubeInfo();
} else {
	$log->put('Click `START UPDATE` at the top of the page to manually run this task.');
}

file_put_contents('running.json', json_encode(['running' => false]));

function test_restart($do_msg = true) {
	global $endAt, $log;

	$end = false;
	$msg = '';
	$header = 'Location: index.php';

	$do = json_decode(file_get_contents('kill-switch.json'));
	if ($do->kill) {
		$end = true;
		$msg = '<span style="color:red">Exited due to `KILL SWITCH` being pressed!</span>';
		$header = 'Location: index.php';
	} elseif (time() >= $endAt) {
		$end = true;
		$msg = '';
		$header = 'Location: index.php?action=import';
	} elseif ($do_msg) {
		$log->put('Restarting in ' . strval($endAt - time()) . ' seconds');
	}

	if ($end) {
		$log->put($msg);
		file_put_contents('running.json', json_encode(['running' => false]));
		header($header);
		exit;
	}
}
