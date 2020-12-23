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


// Google API init
$client = new Google_Client();
$client->setApplicationName($appName);
$client->setDeveloperKey($apiKey);


$publishedBeforeObj = null;



//echo "Getting videos uploaded from <b>$publishedAfter</b>" . ($publishedBefore?' to <b>'.$publishedBefore.'</b>':'') . $nl;


$channel = new stdClass();




	$ytChannelId = 'UCDAzmE9V4Xw5CdLkn3pvO3A';


	$publishedAfterObj = new DateTime('2019-06-13');

	$publishedBeforeObj = new DateTime('2019-06-15');




	$service = new Google_Service_YouTube($client);
	$optParams = array(
		'channelId' => $ytChannelId,
		'maxResults' => $maxResults,
		'order' => 'date',
		'type' => 'video',
		'publishedAfter' => $publishedAfterObj->format(DateTimeInterface::RFC3339),
		'publishedBefore' => $publishedBeforeObj->format(DateTimeInterface::RFC3339)
	);


	try{
		$results = $service->search->listSearch('snippet', $optParams);

		
		foreach ($results->items as $item) {
			echo ' - ' . $item->id->videoId . " - " . $item->snippet->title ;


			echo "[added]" .  $nl;



				if(!$item->snippet->thumbnails || !$item->snippet->thumbnails->high->url){
					echo "No Image" .  $nl;
					continue;
				}

			    $image = file_get_contents($item->snippet->thumbnails->high->url);
			    if(!$image){
			    	echo "Couldnt get Image" .  $nl;
			    	continue;
			    }

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
			    
			    $slug = get_slug_from_string( $item->snippet->title );
			    $filename = $slug . '.' . $ext;

			    $filePath = './testimg/' . $filename;

			    if(!file_exists($filePath))
			    		file_put_contents($filePath, $image);



		}
		// echo "<br><br>---------------------------------<br><br><pre>";
		// var_dump($results);
		// echo "</pre>";
	}
	catch(Exception $e){
		echo 'Exception: ' . $e->getMessage();
		
	}



function get_slug_from_string($string)
{
	$result = preg_replace('/[^a-zA-Z0-9]+/', '-' , $string);
	return $result;
}