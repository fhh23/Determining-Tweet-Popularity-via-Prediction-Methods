<?php
error_reporting(E_ALL ^ E_NOTICE);

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
$time_limit = 14400; // 4 hour data collection
set_time_limit($time_limit + 30);

// Set the comma separate list of longitude/latitude pairs
// to specify bounding boxes for filtering tweets
// $params['locations'] = ...;

// -------------------------------------------------------
// Set the keywords to search for in streaming tweets
// -------------------------------------------------------

$params['track'] = 'a,the,paris,isis,japan,mexico,earthquake,football,basketball,health,president,democrat,republican';

// ---------------------------------------------
// Define callback function for Streaming API
// ---------------------------------------------

function my_streaming_callback($data, $length, $metrics) 
{
	// Keep running time
	global $time_pre;
	$time_post = microtime(true); // Returns the Unix timestamp with microseconds as a floating point value
	$exec_time = $time_post - $time_pre;
	global $time_limit;
	if ( $exec_time > $time_limit )
	{
		return true;
	}

	// Use the filename created before the streaming request post
	global $outputFile;

	// Converts the JSON string to a PHP variable (and converts the output into an associative array)
	$data = json_decode($data, true); 
	if(!is_null($data['text'])) // Only prints the tweet information if $data has valid contents
	{
		// BEGIN DEBUG: create a data dump so that the appropriate way to expand all fields can be determined
		// and necessary extra information can be found
		print_r($data); // Call to the getTwitter.php function should include "> [ data_dump_filename ]" at the end
		// END DEBUG
		
		$data['text'] = str_replace(PHP_EOL, '', $data['text']); 
		
		// Save any fields that contain an array in their own variable
		
		// Current Tweet data fields
		$coordinates = $data['coordinates'];
		$entities = $data['entities'];
		$place = $data['place'];
		$user = $data['user'];
		$quotedStatus = $data['quoted_status'];
		$retweetedStatus = $data['retweeted_status'];
		
		// Quoted status fields
		$quotedStatusCoordinates = $quotedStatus['coordinates'];
		$quotedStatusEntities = $quotedStatus['entities'];
		$quotedStatusPlace = $quotedStatus['place'];
		$quotedStatusRetweetedStatus = $quotedStatus['retweeted_status'];
		$quotedStatusUser = $quotedStatus['user'];
		
		// Retweeted status fields
		$retweetedStatusCoordinates = $retweetedStatus['coordinates'];
		$retweetedStatusEntities = $retweetedStatus['entities'];
		$rewteetedStatusPlace = $retweetedStatus['place'];
		$retweetedStatusUser = $retweetedStatus['user'];
		
		// Comma pre-processing: all commas needed to be removed or re-encoded to not confuse the CSV file format
		$data['text'] = str_replace(',', '&#44;', $data['text']); // Substitude the HTML comma code for any comma
		$quotedStatus['text'] = str_replace(',', '&#44;', $quotedStatus['text']); // Substitude the HTML comma code for any comma
		$retweetdStatus['text'] = str_replace(',', '&#44;', $retweetedStatus['text']); // Substitude the HTML comma code for any comma
		$coordinates['coordinates'] = str_replace(',', ' ', $coordinates['coordinates']);
		$quotedStatusCoordinates['coordinates'] = str_replace(',', ' ', $quotedStatusCoordinates['coordinates']);
		$retweetedStatusCoordinates['coordinates'] = str_replace(',', ' ', $retweetedStatusCoordinates['coordinates']);
		$user['location'] = str_replace(',', '', $user['location']);
		$quotedStatusUser['location'] = str_replace(',', '', $quotedStatusUser['location']);
		$retweetedStatusUser['location'] = str_replace(',', '', $retweetedStatusUser['location']);
		$user['name'] = str_replace(',', '', $user['name']);
		$quotedStatusUser['name'] = str_replace(',', '', $quotedStatusUser['name']);
		$retweetedStatusUser['name'] = str_replace(',', '', $retweetedStatusUser['name']);
		$place['full_name'] = str_replace(',', '', $place['full_name']);
		$quotedStatusPlace['full_name'] = str_replace(',', '', $quotedStatusPlace['full_name']);
		$retweetedStatusPlace['full_name'] = str_replace(',', '', $retweetedStatusPlace['full_name']);
		
		// BEGIN FEATURE PRINTING
		$outputString = "{$data['contributors']}" . "," . "{$coordinates['coordinates']}" . "," . "{$data['created_at']}" . ",";
		
		// Entities Array printing
		if(is_array($entities['urls']))
		{
			foreach($entities['urls'] as $urls)
			{
				$outputString = "{$outputString}" . "{$urls['expanded_url']}" . ";";
			}
			unset($urls);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($entities['user_mentions']))
		{
			foreach($entities['user_mentions'] as $userMentions)
			{
				$outputString = "{$outputString}" . "{$userMentions['id_str']}" . ";";
			}
			unset($userMentions);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
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
		if(is_array($entities['user_mentions']))
		{
			foreach($entities['user_mentions'] as $userMentions)
			{
				$outputString = "{$outputString}" . "{$userMentions['screen_name']}" . ";";
			}
			unset($userMentions);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($entities['hashtags']))
		{
			foreach($entities['hashtags'] as $hashtags)
			{
				$outputString = "{$outputString}" . "{$hashtags['text']}" . ";";
			}
			unset($hashtags);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($entities['media']))
		{
			foreach($entities['media'] as $media)
			{
				$outputString = "{$outputString}" . "{$media['id_str']}" . ";";
			}
			unset($media);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($entities['symbols']))
		{
			foreach($entities['symbols'] as $symbols)
			{
				$outputString = "{$outputString}" . "{$symbols['text']}" . ";";
			}
			unset($symbols);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		
		$outputString = "{$outputString}" . "{$data['favorite_count']}" . "," . "{$data['filter_level']}" . "," . "{$data['id_str']}" . "," . "{$data['in_reply_to_screen_name']}" . "," . "{$data['in_reply_to_status_id_str']}" . "," . "{$data['in_reply_to_user_id_string']}" . "," . "{$place['id']}" . "," . "{$place['place_type']}" . "," . "{$place['full_name']}" . "," . "{$place['country']}" . "," . "{$data['possibly_sensitive']}" . ",";
		$outputString = "{$outputString}" . "{$data['quoted_status_id_str']}" . "," . "{$quotedStatusCoordinates['coordinates']}" . "," . "{$quotedStatus['created_at']}" . ",";
		
		// Quoted Status Entities Array printing
		if(is_array($quotedStatusEntities['urls']))
		{
			foreach($quotedStatusEntities['urls'] as $urls)
			{
				$outputString = "{$outputString}" . "{$urls['expanded_url']}" . ";";
			}
			unset($urls);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($quotedStatusEntities['user_mentions']))
		{
			foreach($quotedStatusEntities['user_mentions'] as $userMentions)
			{
				$outputString = "{$outputString}" . "{$userMentions['id_str']}" . ";";
			}
			unset($userMentions);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($quotedStatusEntities['user_mentions']))
		{
			foreach($quotedStatusEntities['user_mentions'] as $userMentions)
			{
				$outputString = "{$outputString}" . "{$userMentions['name']}" . ";";
			}
			unset($userMentions);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($quotedStatusEntities['user_mentions']))
		{
			foreach($quotedStatusEntities['user_mentions'] as $userMentions)
			{
				$outputString = "{$outputString}" . "{$userMentions['screen_name']}" . ";";
			}
			unset($userMentions);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($quotedStatusEntities['hashtags']))
		{
			foreach($quotedStatusEntities['hashtags'] as $hashtags)
			{
				$outputString = "{$outputString}" . "{$hashtags['text']}" . ";";
			}
			unset($hashtags);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($quotedStatusEntities['media']))
		{
			foreach($quotedStatusEntities['media'] as $media)
			{
				$outputString = "{$outputString}" . "{$media['id_str']}" . ";";
			}
			unset($media);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		if(is_array($quotedStatusEntities['symbols']))
		{
			foreach($quotedStatusEntities['symbols'] as $symbols)
			{
				$outputString = "{$outputString}" . "{$symbols['text']}" . ";";
			}
			unset($symbols);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		
		$outputString = "{$outputString}" . "{$quotedStatus['favorite_count']}" . "," . "{$quotedStatus['in_reply_to_screen_name']}" . "," . "{$quotedStatus['in_reply_to_status_id_str']}" . "," . "{$quotedStatus['in_reply_to_user_id_string']}" . "," . "{$quotedStatusPlace['id']}" . "," . "{$quotedStatusPlace['place_type']}" . "," . "{$quotedStatusPlace['full_name']}" . "," . "{$quotedStatusPlace['country']}" . "," . "{$quotedStatus['retweet_count']}". "," . "{$retweetedStatus['id_str']}" . "," . "{$quotedStatus['source']}" . "," . "{$quotedStatus['text']}" . "," . "{$quotedStatusUser['id_str']}" . "," . "{$quotedStatusUser['name']}" . "," . "{$quotedStatusUser['screen_name']}" . "," . "{$quotedStatusUser['location']}" . "," . "{$quotedStatusUser['created_at']}" . "," . "{$quotedStatusUser['statuses_count']}" . "," . "{$quotedStatusUser['followers_count']}" . "," . "{$quotedStatusUser['friends_count']}" . "," . "{$quotedStatusUser['listed_count']}" . "," . "{$quotedStatusUser['contributors_enabled']}" . "," . "{$quotedStatusUser['geo_enabled']}" . "," . "{$quotedStatusUser['protected']}" . "," . "{$quotedStatusUser['verified']}" . "," . "{$quotedStatusUser['default_profile']}" . "," . "{$quotedStatusUser['default_profile_image']}" . "," . "{$quotedStatusUser['withheld_in_countries']}" . "," . "{$quotedStatusUser['withheld_scope']}" . ",";		
		$outputString = "{$outputString}" . "{$data['scopes']}" . "," . "{$data['retweet_count']}" . ",";
		$outputString = "{$outputString}" . "{$data['retweeted_status']}" . ",";
		$outputString = "{$outputString}" . "{$data['source']}" . "," . "{$data['text']}" . "," . "{$data['truncated']}" . "," . "{$user['id_str']}" . "," . "{$user['name']}" . "," . "{$user['screen_name']}" . "," . "{$user['location']}" . "," . "{$user['created_at']}" . "," . "{$user['statuses_count']}" . "," . "{$user['followers_count']}" . "," . "{$user['friends_count']}" . "," . "{$user['listed_count']}" . "," . "{$user['contributors_enabled']}" . "," . "{$user['geo_enabled']}" . "," . "{$user['protected']}" . "," . "{$user['verified']}" . "," . "{$user['default_profile']}" . "," . "{$user['default_profile_image']}" . "," . "{$user['withheld_in_countries']}" . "," . "{$user['withheld_scope']}" . "," . "{$data['withheld_copyright']}" . "," . "{$data['withheld_in_countries']}" . "," . "{$data['withheld_scope']}";
		// END FEATURE PRINTING
		
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

// Print CSV file headers
$dataHeaders = "contributors,coordinates,created_at,";
$dataHeaders = "{$dataHeaders}" . "urls,user_mentions_id_str,user_mentions_name,user_mentions_screenname,hashtags,media,symbols,";
$dataHeaders = "{$dataHeaders}" . "favorite_count,filter_level,id_str,in_reply_to_screen_name,in_reply_to_status_id_str,in_reply_to_user_id_str,place_id,place_type,place_name,place_country,possibly_sensitive,";
$dataHeaders = "{$dataHeaders}" . "quoted_status_id_str,quoted_status_coordinates,quoted_status_creation_date,quoted_status_urls,quoted_status_user_mentions_id_str,quoted_status_user_mentions_name,quoted_status_user_mentions_screenname,quoted_status_hashtags,quoted_status_media,quoted_status_symbols,quoted_status_favorite_count,quoted_status_in_reply_to_screen_name,quoted_status_in_reply_to_status_id_str,quoted_status_in_reply_to_user_id_str,quoted_status_place_id,quoted_status_place_type,quoted_status_place_name,quoted_status_place_country,quoted_status_retweet_count,quoted_status_retweet_status_id_str,quoted_status_source,quoted_status_text,quoted_status_user_id_str,quoted_status_user_name,quoted_status_user_screenname,quoted_status_user_location,quoted_status_user_creation_date,quoted_status_user_statuses_count,quoted_status_user_followers_count,quoted_status_user_following_count,quoted_status_user_listed_count,quoted_status_user_contributors_enabled,quoted_status_user_geo_enabled,quoted_status_protected_user,quoted_status_verified_user,quoted_status_default_user_profile,quoted_status_default_user_profile_image,quoted_status_user_withheld_in_countries,quoted_status_user_withheld_scope,";
$dataHeaders = "{$dataHeaders}" . "scopes,retweet_count,";
$dataHeaders = "{$dataHeaders}" . "retweeted_status_id_str,retweeted_status_coordinates,retweeted_status_creation_date,retweeted_status_urls,retweeted_status_user_mentions_id_str,retweeted_status_user_mentions_name,retweeted_status_user_mentions_screenname,retweeted_status_hashtags,retweeted_status_media,retweeted_status_symbols,retweeted_status_favorite_count,retweeted_status_in_reply_to_screen_name,retweeted_status_in_reply_to_status_id_str,retweeted_status_in_reply_to_user_id_str,retweeted_status_place_id,retweeted_status_place_type,retweeted_status_place_name,retweeted_status_place_country,retweeted_status_retweet_count,retweeted_status_quoted_status_id_str,retweeted_status_source,retweeted_status_text,retweeted_status_user_id_str,retweeted_status_user_name,retweeted_status_user_screenname,retweeted_status_user_location,retweeted_status_user_creation_date,retweeted_status_user_statuses_count,retweeted_status_user_followers_count,retweeted_status_user_following_count,retweeted_status_user_listed_count,retweeted_status_user_contributors_enabled,retweeted_status_user_geo_enabled,retweeted_status_protected_user,retweeted_status_verified_user,retweeted_status_default_user_profile,retweeted_status_default_user_profile_image,retweeted_status_user_withheld_in_countries,retweeted_status_user_withheld_scope,";
$dataHeaders = "{$dataHeaders}" . "source,text,truncated,";
$dataHeaders = "{$dataHeaders}" . "user_id_str,user_name,user_screenname,user_location,user_creation_date,user_statuses_count,user_followers_count,user_following_count,user_listed_count,user_contributors_enabled,user_geo_enabled,protected_user,verified_user,default_user_profile,default_user_profile_image,user_withheld_in_countries,user_withheld_scope,";
$dataHeaders = "{$dataHeaders}" . "tweet_withheld_copyright,tweet_withheld_in_countries,tweet_withheld_scope\n";
if (file_put_contents($outputFile, $dataHeaders) === FALSE) // Caution: overwrites any current data in the file
{
	// FALSE indicates that an error occurred during the fwrite operation
}

// BEGIN DEBUG: create a data dump so that the appropriate way to expand all fields can be determined
// if (file_put_contents("data_dump.txt", "") === FALSE)
// {
	// FALSE indicates that an error occurred during the fwrite operation
// }
// END DEBUG

$url = 'https://stream.twitter.com/1/statuses/filter.json';
$tmhOAuth->streaming_request('POST', $url, $params, 'my_streaming_callback');

?>
