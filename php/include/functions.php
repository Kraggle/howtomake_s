<?php

$nl = "<br />\n";




function getYoutubeService()
{

    $appName = 'Youtube Scraper';
    $apiKey = 'AIzaSyDtGJtBPXdcWfBswi3mJSezfoj23Fr2T1A';// Youtube API Key

        // Google API init
    $client = new Google_Client();
    $client->setApplicationName($appName);
    $client->setDeveloperKey($apiKey);

    $service = new Google_Service_YouTube($client);

    return $service;
}



/**
 *
 * Get enhanced video from Youtube and store
 *
 * @param    array  $videoIds Array of PostIDs with Youtube video IDs as keys.
 * @return      void
 *
 */

/* [ YtID => PostID ] */

function getExtraYoutubeInfo(Array $videoIds)
{

    if(count($videoIds)){


        // Process in batches to reduce API calls. 50 is Youtube's pagination limit.
        $chunkedVideoIDs = array_chunk($videoIds,50,true);

        foreach($chunkedVideoIDs as $ids){


            // Get more information for the videos
            $optParams = array(
                'id' => implode(',', array_keys($ids)),
            );
            echo "$nl$nl Loading extra YT video info$nl$nl";
            
            try{
                //echo "IDS: " . array_keys($videoIds) . "$nl";
                $results = getYoutubeService()->videos->listVideos('id,snippet,contentDetails,statistics,topicDetails', $optParams);
                //var_dump();
                foreach ($results->items as $item) {
                    $id = $videoIds[$item->id];
                    if(!$id) continue;// Skip if video ID not in list, shoudln't happen

                    // Attach tags to post
                    $result = wp_set_post_tags($id, $item->snippet->tags, true );


					// duration (in seconds)
					$interval = new DateInterval($item->contentDetails->duration);
					$duration = ceil(($interval->days * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s));
				
                    // Video Duration
                    if ($duration){
                        update_post_meta($id, 'video_duration', $duration);// Total in seconds
                        
                        update_post_meta($id, 'video_duration_h', $interval->h);// hour portion
                        update_post_meta($id, 'video_duration_m', $interval->i);// minutes portion
                        update_post_meta($id, 'video_duration_s', $interval->s);// seconds portion
                        
                    }
                    //file_put_contents('youtube-videos-extra.txt', print_r($item, true));

                }
            }
            catch(Exception $e){
                echo 'Exception: ' . $e->getMessage();
                
            }
        }



    }

}