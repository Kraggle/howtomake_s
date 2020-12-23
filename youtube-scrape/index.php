<?php

// http://localhost/youtube-scrape/?from=2019-01-01&to=2019-02-28&loop=1
// /wp-content/themes/howtomake/youtube-scrape/?from=2019-01-01&loop=1
// /wp-content/themes/howtomake/youtube-scrape/?from=2019-12-15&to=2019-12-30
//
$postAuthor = 1;


$maxResults = 50;// max: 50

// ------------------------------------------------------------------

include('vendor/autoload.php');

if(!defined( 'ABSPATH' )){
	require_once("../../../../wp-load.php");
}

$appName = 'Youtube Scraper';
$apiKey = 'AIzaSyDtGJtBPXdcWfBswi3mJSezfoj23Fr2T1A';// Youtube API Key


// ================================== Settings End =======================================

$nl = "<br />\n";
$wp_upload_dir = wp_upload_dir();

$uploadDir = $wp_upload_dir['basedir'].'/yt-thumb';
if ( ! file_exists( $uploadDir ) ) wp_mkdir_p( $uploadDir );


// Google API init
$client = new Google_Client();
$client->setApplicationName($appName);
$client->setDeveloperKey($apiKey);






// if(isset($_GET['from'])){
// 	$publishedAfter = $_GET['from'] . ' 00:00:00';
// }else{
// 	$publishedAfter = file_get_contents('last-run.txt');
// 	$publishedAfter = trim($publishedAfter);
// 	if(!$publishedAfter)$publishedAfter = '2000-01-01 00:00:00';
// }
// $publishedAfterObj = new DateTime($publishedAfter);

$publishedBefore = null;
$publishedBeforeObj = null;
// if(isset($_GET['to'])){
// 	$publishedBefore = $_GET['to'] . ' 23:59:59';
// 	$publishedBefore = trim($publishedBefore);
// 	$publishedBeforeObj = new DateTime($publishedBefore);
// }else{
// 	$publishedBeforeObj = $publishedAfterObj->modify('+1month');
// }



//echo "Getting videos uploaded from <b>$publishedAfter</b>" . ($publishedBefore?' to <b>'.$publishedBefore.'</b>':'') . $nl;


// Get Channels
$channels = get_terms( ['taxonomy' => 'video-channel', 'hide_empty' => false] );

$rows = [];
foreach($channels as $channel){

	echo $nl . $nl . "<b>".$channel->name .":</b>" . $nl;

	$importerEnabled = get_field('importer_enabled', $channel, true);

	// Skip unless importer is enabled
	if(!$importerEnabled || !is_array($importerEnabled) || !$importerEnabled[0]){
		echo 'Skipping, importer disabled' . $nl;
		continue;
	}


	$ytChannelId = get_field('yt_channel_id', $channel, true);

	$videoCategories = get_field('video_categories', $channel, true);




	// Get start date from Channel field
	$importStart = get_field('imported_up_to', $channel, true);
	$importStart = trim($importStart);

	$importStartDate = empty($importStart)?'2000-01-01':$importStart;
	$publishedAfterObj = new DateTime($importStartDate);

	$publishedBeforeObj = clone $publishedAfterObj;
	$publishedBeforeObj->modify('+1month');

	$currentTime = new DateTime('now');

	// Don't go past now
	if( $publishedBeforeObj > $currentTime)$publishedBeforeObj = $currentTime;

	$service = new Google_Service_YouTube($client);
	$optParams = array(
		'channelId' => $ytChannelId,
		'maxResults' => $maxResults,
		'order' => 'date',
		'type' => 'video',
		'publishedAfter' => $publishedAfterObj->format(DateTimeInterface::ATOM),
		'publishedBefore' => $publishedBeforeObj->format(DateTimeInterface::ATOM)
	);


	try{
		$results = $service->search->listSearch('snippet', $optParams);
//var_dump($results);exit;
		echo $publishedAfterObj->format("Y-m-d H:i:s") . " - " . $publishedBeforeObj->format("Y-m-d H:i:s") . " | Results: " . count($results->items) . $nl;

		foreach ($results->items as $item) {
			echo ' - ' . $item->id->videoId . " - " . $item->snippet->title ;


			$existingPost = get_page_by_title( wp_strip_all_tags( $item->snippet->title ), OBJECT, 'video' );
			if($existingPost){
				echo "[exists]" .  $nl;
				continue;
			}

			$row = [
				$item->snippet->channelId,
				$item->id->videoId,
				$item->snippet->publishedAt,
				
				$item->snippet->title,
				$item->snippet->description,
				$item->snippet->publishedAt
			];

			if($item->snippet->thumbnails)$row[] = $item->snippet->thumbnails->high->url;

			$postdate = new DateTime($item->snippet->publishedAt);

			$my_post = array(
			  'post_title'    => wp_strip_all_tags( $item->snippet->title ),
			  'post_content'  => $item->snippet->description,
			  'post_status'   => 'publish',
			  'post_author'   => $postAuthor,
			  'post_type'	  => 'video',
			  'post_date'     => $postdate->format('Y-m-d H:i:s'),
			);
			 
			// Insert the post into the database
			$postId = wp_insert_post( $my_post );

			echo "[added]" .  $nl;

			if($postId){

				update_field('youtube_video_id', $item->id->videoId, $postId );

				// Assign to channel
				wp_set_object_terms( $postId, $channel->term_taxonomy_id, 'video-channel' );

				wp_set_object_terms( $postId, $videoCategories, 'video-category' );


				if(!$item->snippet->thumbnails || !$item->snippet->thumbnails->high->url)continue;

			    $image = file_get_contents($item->snippet->thumbnails->high->url);
			    if(!$image)continue;

			    $finfo = new finfo(FILEINFO_MIME);
			    $mimeType = $finfo->buffer($image);

			    $fileExt = [
			      'image/png' => 'png',
			      'image/jpg' => 'jpg',
			      'image/jpeg' => 'jpg',
			      'image/jpeg' => 'jpeg',
			      'image/gif' => 'gif',
			      'image/svg' => 'svg',

			    ];

			    $ext = isset($fileExt[$mimeType])?$fileExt[$mimeType]:'jpg';
			    if(!$ext) $ext = 'jpg';
			    
			    $slug = get_slug_from_string( $item->snippet->title, null, 'display' );
			    $filename = $postId . '-' . $slug . '.' . $ext;

			    $filePath = $uploadDir . '/' . $filename;

			    if(!file_exists($filePath))
			    		file_put_contents($filePath, $image);

			    
			    $mediaId = wp_insert_attachment( ['post_title' => wp_strip_all_tags( $item->snippet->title ), 'post_content' => '', 'post_mime_type' => $mimeType, 'post_status'    => 'inherit'], $filePath, $postId );

				// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				// Generate the metadata for the attachment, and update the database record.
				$attach_data = wp_generate_attachment_metadata( $mediaId, $filePath );
				wp_update_attachment_metadata( $mediaId, $attach_data );


				set_post_thumbnail( $postId, $mediaId );

			}

			$rows[] = $row;
		}
		// echo "<br><br>---------------------------------<br><br><pre>";
		// var_dump($results);
		// echo "</pre>";
	}
	catch(Exception $e){
		echo 'Exception: ' . $e->getMessage();
		
	}

	update_field('imported_up_to', $publishedBeforeObj->format('Y-m-d H:i:s'), $channel);

}


// Write to CSV
$fp = fopen('videos.csv', 'w');
foreach($rows as $row){
	fputcsv($fp, $row);
}
fclose($fp);


//file_put_contents('last-run.txt', date('Y-m-d H:i:s'));
file_put_contents('last-run.txt', $publishedBeforeObj->format(DateTimeInterface::ATOM));

// Redirect to next month
if(isset($_GET['loop'])){

	$link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
	$link .= $_SERVER['HTTP_HOST'];
	$url = parse_url($_SERVER['REQUEST_URI']); 
	$link .= $url['path']; 

	$link .= '?loop=1';
	
	$publishedAfterObj = $publishedBeforeObj;
	$link .= '&from=' . $publishedAfterObj->format('Y-m-d');
	$link .= '&to=' . $publishedBeforeObj->modify('+1month')->format('Y-m-d');

	echo '<meta http-equiv="refresh" content="5;URL=\''. $link .'\'" />';
}

function get_slug_from_string($string)
{
	$result = preg_replace('/[^a-zA-Z0-9]+/', '-' , $string);
	return $result;
}