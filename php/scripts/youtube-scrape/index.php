<?php
// INFO: A lot has changed in this scraper.
// INFO:  - It now checks for dead links and duplicates
// INFO:  - All options have been removed while running it as they are now automatic
// INFO:  - The listSearch has been removed as that was quota hungry consuming 100 units each time
// INFO:  - All new youtube calls only consume 1 unit for each call
// INFO:  - There is now a few methods of skipping if the channel has already run today
// INFO:  - There is a timer that will end the script before a timeout and resume the script again

$force = isset($_GET['force']) ? $_GET['force'] : false;

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
				<input type="checkbox" name="force" <?php echo $isChecked ?> />
			</label>
		</p>
		<input type="submit" value="START UPDATE" />
	</form>
	<input id="kill-switch" type="button" value="KILL SCRIPT" style="position: fixed; top: 20px; right: 20px;" />
	<input id="cancel-kill" type="button" value="CANCEL KILL" style="position: fixed; top: 50px; right: 20px;" />

	<div class="logs"></div>
	<script type="module" src="index.js"></script>
</body>

</html>

<?php

$postAuthor = 1;
$maxResults = 50; // max: 50

$maxTime = (ini_get('max_execution_time') - 20);
$endAt = strtotime("+{$maxTime} seconds");

// ------------------------------------------------------------------
include('../../vendor/autoload.php');

if (!defined('ABSPATH')) {
	require_once("../../../../../../wp-load.php");
}

// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
require_once(ABSPATH . 'wp-admin/includes/image.php');

require_once __DIR__ . '/../../include/functions.php';

// ================================== Settings End =======================================

if (!file_exists('./info/'))
	mkdir('./info/');

$log = videoLogger::getInstance('./info/log' . date('[d-M-Y H]') . '.txt');

if (!file_exists('running.json'))
	file_put_contents('running.json', json_encode(['running' => false]));
$is = json_decode(file_get_contents('running.json'));
if ($is->running) {
	$log->put('Either `running.json` has not updated or someone or something else is already running this script.');
	exit;
}

if (!file_exists('kill-switch.json'))
	file_put_contents('kill-switch.json', json_encode(['kill' => false]));
$do = json_decode(file_get_contents('kill-switch.json'));
if ($do->kill) {
	$log->put('Someone has forced the task to stop, you must allow it to resume with `CANCEL KILL`');
	exit;
}

file_put_contents('running.json', json_encode(['running' => true]));

$uploadDir = wp_upload_dir()['basedir'] . '/yt-thumb';
if (!file_exists($uploadDir)) wp_mkdir_p($uploadDir);

if (isset($_GET['action']) && $_GET['action'] === 'import') {

	global $wpdb;

	// Get Channels
	$channels = get_terms(['taxonomy' => 'video-channel', 'hide_empty' => false]);

	foreach ($channels as $channel) {
		// INFO: This now checks if it was updated today already and skips if it was
		$lastUpdate = get_term_meta($channel->term_id, 'yt_last_update', true) ?: date('Y-m-d', strtotime('-1 week'));
		if (!$force && date('Y-m-d', strtotime('-1 days')) < $lastUpdate) {
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

		$ytChannelId = get_field('yt_channel_id', $channel, true);
		$ytUploadsId = get_term_meta($channel->term_id, 'yt_uploads_id', true);

		if (!$ytUploadsId) {
			$response = getYoutubeService()->channels->listChannels('contentDetails', [
				'id' => $ytChannelId
			]);

			$ytUploadsId = $response->items[0]->contentDetails->relatedPlaylists->uploads;
			add_term_meta($channel->term_id, 'yt_uploads_id', $ytUploadsId, true);
		}

		$videoCategories = get_field('video_categories', $channel, true);

		// INFO: This whole section will only run once every two days
		// INFO: It checks for dead links and duplicate video posts
		$lastRemoval = get_term_meta($channel->term_id, 'yt_last_removal', true) ?: date('Y-m-d', strtotime('-1 week'));
		if ($force || date('Y-m-d', strtotime('-2 days')) > $lastRemoval) {
			update_term_meta($channel->term_id, 'yt_last_removal', date('Y-m-d'), true);

			$log->put(indent() . "It's been more than 2 days, so we're checking for dead links and duplicates");

			// Get all youtube ids and post ids of existing channel videos
			$posts = get_channel_video_ids($channel->term_id);

			// This is here as there are a few videos that were duplicated and this is 
			// probably the best place to remove them as we are already going through them.
			$got_yt_ids = [];
			$ids = [];
			foreach ($posts as $post) {
				if (in_array($post->yt_id, $got_yt_ids)) {
					// delete the post as its a duplicate
					wp_delete_post($post->post_id, true);
					$log->put(indent(2) . "Deleting post {$post->post_id} as it was a duplicate!");
					continue;
				}
				$got_yt_ids[] = $post->yt_id;
				$ids[] = $post;
			}

			// This is here to remove any dead youtube links, it's the most cost effective way to do it.
			$not_dead = [];
			$chunks = array_chunk($got_yt_ids, 50);
			foreach ($chunks as $chunk) {
				try {
					$response = getYoutubeService()->videos->listVideos('id,snippet,contentDetails,statistics,topicDetails', [
						'id' => implode(',', $chunk)
					]);

					$not_dead = array_merge($not_dead, $response->items);
				} catch (Exception $e) {
					$log->put('Exception: ' . $e->getMessage());
				}
			}

			$json = [];
			$have_yt_ids = [];
			foreach ($not_dead as $video) {
				$have_yt_ids[] = $video->id;

				$json[] = (object) [
					'id' => $video->id,
					'snippet' => [
						'description' => $video->snippet->description,
						'publishedAt' => $video->snippet->publishedAt,
						'tags' => $video->snippet->tags,
						'title' => $video->snippet->title,
						'thumbnails' => $video->snippet->thumbnails,
					],
					'contentDetails' => [
						'duration' => $video->contentDetails->duration,
					],
					'statistics' => $video->statistics
				];
			}

			if (!file_exists('./data/'))
				mkdir('./data/');

			// Save the data we pulled for future reference
			file_put_contents("./data/$ytChannelId.json", json_encode($json));

			foreach ($got_yt_ids as $yt_id) {
				// The link no longer exists, delete the post
				if (!in_array($yt_id, $have_yt_ids)) {
					if (is_numeric($i = array_search($yt_id, $ids))) {
						$post_id = $ids[$i]->post_id;
						wp_delete_post($post_id, true);
						$log->put(indent(2) . "Deleting post {$post->post_id} as it was a dead link!");
					}
				}
			}
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
				'post_content' => remove_emoji($snippet->description),
				'post_status'  => 'publish',
				'post_author'  => $postAuthor,
				'post_type'	   => 'video',
				'post_date'    => $postdate->format('Y-m-d H:i:s')
			]);

			if ($postId) {
				$log->put(indent(3) . "Video successfully added $postid");

				update_field('youtube_video_id', $videoId, $postId);

				// Assign to channel
				wp_set_object_terms($postId, $channel->term_taxonomy_id, 'video-channel');
				wp_set_object_terms($postId, $videoCategories, 'video-category');

				// INFO: Updated this to get the largest available image size (Kraggle)
				if (save_video_image_for_post($postId, $item))
					$log->put(indent(3) . 'Successfully created the video thumbnails.');
				else
					$log->put(indent(3) . 'Creating the video thumbnails failed.');
			} else
				$log->put(indent(3) . 'Failed to add this video.');

			// file_put_contents('youtube-videos.txt', print_r($item, true));

			if (time() >= $endAt) {
				file_put_contents('running.json', json_encode(['running' => false]));
				die(header("Location: ./index.php?action=import"));
			}

			file_put_contents('running.json', json_encode(['running' => false]));
			die(header("Location: ./index.php"));
		}

		update_term_meta($channel->term_id, 'yt_last_update', date('Y-m-d'), true);
	}

	getExtraYoutubeInfo();
}

file_put_contents('running.json', json_encode(['running' => false]));
