<?php
error_reporting(E_ALL ^ E_NOTICE);

/*
 * Authors: Bonnie Reiff and Farhan Hormasji
 * Modified from getTwitter.php provided by Dr. Tan
 * CSE 881 Project, Fall 2015
 */

// ---------------------------------------------------------
// Include Twitter libraries
// ---------------------------------------------------------

require 'tmhOAuth.php';
require 'tmhUtilities.php';

// ------------------------------------------
// Parameter array
// ------------------------------------------

$params = array();
$params['delimited'] = 'length'; // Provides the length of the status messages
$params['language'] = 'en'; // Ensures that the Tweets are in English

// ---------------------------------------------------------
// Streaming API time limit and bounding box settings
// ---------------------------------------------------------

// Start timer
$time_pre = microtime(true);
$time_limit = 1800; // 30 minute data collection
set_time_limit($time_limit + 30);

// Set the comma separate list of longitude/latitude pairs
// to specify bounding boxes for filtering tweets
// $params['locations'] = ...;

// -------------------------------------------------------
// Set the keywords to search for in streaming tweets
// -------------------------------------------------------

// $params['track'] = 'a,the,paris,isis,japan,mexico,earthquake,football,basketball,health,president,democrat,republican';
$params['track'] = 'the,i,to,a,and,is,in,it,you,of,for,on,my,that,at,with,me,do,have,be,are,but';

// ---------------------------------------------
// Define callback function for Streaming API
// ---------------------------------------------

function my_streaming_callback($data, $length, $metrics) 
{
	// Keep running time
	global $time_pre;
	// Returns the Unix timestamp with microseconds as a floating point value
	$time_post = microtime(true); 
	$exec_time = $time_post - $time_pre;
	global $time_limit;
	if ( $exec_time > $time_limit )
	{
		return true;
	}

	// Use the filename created before the streaming request post
	global $outputFile;

	// Converts the JSON string to a PHP variable 
	// (and converts the output into an associative array)
	$data = json_decode($data, true); 
	// Only prints the tweet information if $data has valid contents
	if(!is_null($data['text']))
	{
		$newlineChars = array( PHP_EOL, '\n', '\r' );
		// $data['text'] = str_replace(PHP_EOL, '', $data['text']);
		$data['text'] = str_replace($newlineChars '', $data['text']);
		
		// Create a data dump to have a log of all information 
		// provided for each tweet
		// Call to the streamTweets.php function should be formatted as
		// "php streamTwitter.php > [ data_dump_filename ]"
		print_r($data); 
		
		/* Save any fields Array fields into separate variables for 
		 * easier parsing */
		
		// Current Tweet data fields
		$entities = $data['entities'];
		$place = $data['place'];
		$user = $data['user'];
		
		// Retweeted status
		$retweetedStatus = $data['retweeted_status'];
		
		/*
		* Comma pre-processing: all commas needed to be removed 
		* or re-encoded to not confuse the CSV file format
		*/
		
		// Substitude the HTML comma code for any comma in text
		$data['text'] = str_replace(',', '&#44;', $data['text']); 
		// Remove the commas in other fields and replace with spaces or empty characters
		$user['name'] = str_replace(',', '', $user['name']);
		$place['name'] = str_replace(',', '', $place['name']);
		
		// Add the id_str to the global variable to be passed to the Search API
		global $searchAPIFile; global $idStrCount; global $idStrString;
		if ($idStrCount > 98)
		{
			echo "Search API Tweet Limit Reached. Printing variable to file!";
			$idStrString = "{$data['id_str']}" . "\n\n";
			if (file_put_contents($searchAPIFile;, $idStrString, FILE_APPEND) === FALSE)
			{
				// FALSE indicates that an error occurred during the fwrite operation
			}
			// Reset the variables
			$idStrCount = 0;
			$idStrString = '';
		}
		elseif ($idStrCount == 0)
		{
			$idStrCount = $idStrCount + 1;
			$idStrString = "{$data['id_str']}";
		}
		else
		{
			$idStrCount = $idStrCount + 1;
			$idStrString = "{$idStrString}" . "," "{$data['id_str']}";
		}
		
		
		/* BEGIN FEATURE PRINTING */
		$outputString = "{$data['id_str']}" . "," ."{$data['created_at']}" . "," "{$data['text']}" . "," . "{$data['truncated']}";
		
		// Entities Array printing
		$urlAndMediaCount = 0;
		if(is_array($entities['urls']))
		{
			foreach($entities['urls'] as $urls)
			{
				// $outputString = "{$outputString}" . "{$urls['expanded_url']}" . ";";
				$urlAndMediaCount = $urlAndMediaCount + 1;
			}
			unset($urls);
			// $outputString = rtrim($outputString, ';');
		}
		if(is_array($entities['media']))
		{
			foreach($entities['media'] as $media)
			{
				// $outputString = "{$outputString}" . "{$media['id_str']}" . ";";
				$urlAndMediaCount = $urlAndMediaCount + 1;
			}
			unset($media);
			// $outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . "{$urlAndMediaCount}" . ",";
		unset($urlAndMediaCount);
		
		$userMentionsCount = 0;
		if(is_array($entities['user_mentions']))
		{
			foreach($entities['user_mentions'] as $userMentions)
			{
				// $outputString = "{$outputString}" . "{$userMentions['id_str']}" . ";";
				$userMentionsCount = $userMentionsCount + 1;
			}
			unset($userMentions);
			// $outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . "{$userMentionsCount}" . ",";
		unset($userMentionsCount);
		
		if(is_array($entities['user_mentions']))
		{
			foreach($entities['user_mentions'] as $userMentions)
			{
				$outputString = "{$outputString}" . "{$userMentions['name']}" . ";";
			}
			unset($userMentions);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		
		$hashtagCount = 0;
		if(is_array($entities['hashtags']))
		{
			foreach($entities['hashtags'] as $hashtags)
			{
				$outputString = "{$outputString}" . "{$hashtags['text']}" . ";";
				$hashtagCount = $hashtagCount + 1;
			}
			unset($hashtags);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . "," . "{$hashtagCount}" . ",";
		unset($hashtagCount);
		
		$outputString = "{$outputString}" . ($data['quoted_status_id_str'] <==> '') . ",";
		$outputString = "{$outputString}" . "{$data['quoted_status_id_str']}" . ",";
		$outputString = "{$outputString}" . ($retweetedStatus['id_str'] <==> '') . ",";
		$outputString = "{$outputString}" . "{$retweetedStatus['id_str']}" . ",";
		$outputString = "{$outputString}" . ($data['in_reply_to_screen_name'] <==> '') . ",";
		$outputString = "{$outputString}" . "{$data['in_reply_to_screen_name']}" . ","
		$outputString = "{$outputString}" . ($data['in_reply_to_status_id_str'] <==> '') . ",";
		$outputString = "{$outputString}" . "{$data['in_reply_to_status_id_str']}" . ","
		$outputString = "{$outputString}" . ($data['in_reply_to_user_id_str'] <==> '') . ",";
		$outputString = "{$outputString}" . "{$data['in_reply_to_user_id_str']}" . ","

		$outputString = "{$outputString}" . "{$place['full_name']}" . "," . "{$data['possibly_sensitive']}" . ",";
		$outputString = "{$outputString}" . "{$user['id_str']}" . "," . "{$user['name']}" . "," . "{$user['created_at']}" . "," . "{$user['statuses_count']}" . "," . "{$user['followers_count']}" . "," . "{$user['friends_count']}" . "," . "{$user['listed_count']}" . "," . "{$user['contributors_enabled']}" . "," . "{$user['geo_enabled']}" . "," . "{$user['protected']}" . "," . "{$user['verified']}" . "," . "{$user['default_profile']}" . "," . "{$user['default_profile_image']}";
		/* END FEATURE PRINTING */
		
		if (file_put_contents($outputFile, $outputString, FILE_APPEND) === FALSE)
		{
			// FALSE indicates that an error occurred during the fwrite operation
		}
		
		// End the data record
		if (file_put_contents($outputFile, "\n", FILE_APPEND) === FALSE)
		{
			// FALSE indicates that an error occurred during the fwrite operation
		}
		
	}

	return file_exists(dirname(__FILE__) . '/STOP');
}

// ------------------------------------------
// Get the authentication information
// ------------------------------------------

$secretFile = "auth.token";
$fh = fopen($secretFile, 'r');
$secretArray = array();

while (!feof($fh)) {
	$line = fgets($fh);
	$array = explode( ':', $line );
	$secretArray[trim($array[0])] =trim($array[1]) ;
}
fclose($fh);
$tmhOAuth = new tmhOAuth($secretArray);

// ------------------------------------------
// Get tweets
// ------------------------------------------

// Create the file to which the data will be written
$outputFile = "data_collection_output_" . date('Y-m-d-hisT') . ".csv";
// print_r($outputFile);
$searchAPIFile = "search_idStrings_" . date('Y-m-d-hisT') . "txt";
// print_r($searchAPIFile);

// Create the variables related to building for the Search API
$idStrCount = 0;
$idStrString = '';

// Print CSV file headers
$dataHeaders = "coordinates,created_at,";
$dataHeaders = "{$dataHeaders}" . "urls,user_mentions_id_str,user_mentions_names,hashtags,media,symbols,";
$dataHeaders = "{$dataHeaders}" . "filter_level,id_str,in_reply_to_screen_name,in_reply_to_status_id_str,in_reply_to_user_id_str,place_id,place_type,place_name,place_country,possibly_sensitive,";
$dataHeaders = "{$dataHeaders}" . "quoted_status_id_str,quoted_status_coordinates,quoted_status_creation_date,quoted_status_urls,quoted_status_user_mentions_id_str,quoted_status_user_mentions_name,quoted_status_user_mentions_screenname,quoted_status_hashtags,quoted_status_media,quoted_status_symbols,quoted_status_favorite_count,quoted_status_in_reply_to_screen_name,quoted_status_in_reply_to_status_id_str,quoted_status_in_reply_to_user_id_str,quoted_status_place_id,quoted_status_place_type,quoted_status_place_name,quoted_status_place_country,quoted_status_retweet_count,quoted_status_retweet_status_id_str,quted_status_retweet_text,quoted_status_retweet_favorite_count,quoted_status_retweet_retweet_count,quoted_status_source,quoted_status_text,quoted_status_user_id_str,quoted_status_user_name,quoted_status_user_screenname,quoted_status_user_location,quoted_status_user_creation_date,quoted_status_user_statuses_count,quoted_status_user_followers_count,quoted_status_user_following_count,quoted_status_user_listed_count,quoted_status_user_contributors_enabled,quoted_status_user_geo_enabled,quoted_status_protected_user,quoted_status_verified_user,quoted_status_default_user_profile,quoted_status_default_user_profile_image,quoted_status_user_withheld_in_countries,quoted_status_user_withheld_scope,";
$dataHeaders = "{$dataHeaders}" . "scopes,";
$dataHeaders = "{$dataHeaders}" . "retweeted_status_id_str,retweeted_status_coordinates,retweeted_status_creation_date,retweeted_status_urls,retweeted_status_user_mentions_id_str,retweeted_status_user_mentions_name,retweeted_status_user_mentions_screenname,retweeted_status_hashtags,retweeted_status_media,retweeted_status_symbols,retweeted_status_favorite_count,retweeted_status_in_reply_to_screen_name,retweeted_status_in_reply_to_status_id_str,retweeted_status_in_reply_to_user_id_str,retweeted_status_place_id,retweeted_status_place_type,retweeted_status_place_name,retweeted_status_place_country,retweeted_status_retweet_count,retweeted_status_quoted_status_id_str,retweeted_status_quoted_status_text,retweeted_status_quoted_status_favorite_count,retweeted_status_quoted_status_retweet_count,retweeted_status_source,retweeted_status_text,retweeted_status_user_id_str,retweeted_status_user_name,retweeted_status_user_screenname,retweeted_status_user_location,retweeted_status_user_creation_date,retweeted_status_user_statuses_count,retweeted_status_user_followers_count,retweeted_status_user_following_count,retweeted_status_user_listed_count,retweeted_status_user_contributors_enabled,retweeted_status_user_geo_enabled,retweeted_status_protected_user,retweeted_status_verified_user,retweeted_status_default_user_profile,retweeted_status_default_user_profile_image,retweeted_status_user_withheld_in_countries,retweeted_status_user_withheld_scope,";
$dataHeaders = "{$dataHeaders}" . "source,text,truncated,";
$dataHeaders = "{$dataHeaders}" . "user_id_str,user_name,user_screenname,user_location,user_creation_date,user_statuses_count,user_followers_count,user_following_count,user_listed_count,user_contributors_enabled,user_geo_enabled,protected_user,verified_user,default_user_profile,default_user_profile_image,user_withheld_in_countries,user_withheld_scope,";
$dataHeaders = "{$dataHeaders}" . "tweet_withheld_copyright,tweet_withheld_in_countries,tweet_withheld_scope\n";
// Caution: overwrites any current data in the file
if (file_put_contents($outputFile, $dataHeaders) === FALSE)
{
	// FALSE indicates that an error occurred during the fwrite operation
}

$url = 'https://stream.twitter.com/1/statuses/filter.json';
$tmhOAuth->streaming_request('POST', $url, $params, 'my_streaming_callback');

?>
