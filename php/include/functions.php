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

function getExtraYoutubeInfo(Array $videoIds, Array $features = null)// keywords, duration
{
    global $nl;
    if(count($videoIds)){


        // Process in batches to reduce API calls. 50 is Youtube's pagination limit.
        $chunkedVideoIDs = array_chunk($videoIds,50,true);

        foreach($chunkedVideoIDs as $ids){


            // Get more information for the videos
            $optParams = array(
                'id' => implode(',', array_keys($ids)),
            );
            echo "$nl$nl Loading extra YT video info $nl $nl";
            
            try{

                $results = getYoutubeService()->videos->listVideos('id,snippet,contentDetails,statistics,topicDetails', $optParams);

                foreach ($results->items as $item) {
                    $id = $videoIds[$item->id];
                    if(!$id) continue;// Skip if video ID not in list, shoudln't happen

                    // Attach keywords as tags to post
                    if(is_null($features) || in_array('keywords', $features))
                        $result = wp_set_post_tags($id, $item->snippet->tags, true );


                    // Video Duration
                    if(is_null($features) || in_array('duration', $features))
                        update_post_meta($id, 'video_duration_raw', $item->contentDetails->duration);// ISO_8601 Format

                    //file_put_contents('youtube-videos-extra.txt', print_r($item, true));

                }
            }
            catch(Exception $e){
                echo 'Exception: ' . $e->getMessage();
                
            }
        }



    }

}