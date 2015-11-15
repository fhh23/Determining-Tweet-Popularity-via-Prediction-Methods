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
		// if (file_put_contents("data_dump.txt", $data, FILE_APPEND) === FALSE)
		// {
			// FALSE indicates that an error occurred during the fwrite operation
		// }
		print_r($data);
		// END DEBUG
		
		$data['text'] = str_replace(PHP_EOL, '', $data['text']); 
		
		// Save any fields that may contain an array in their own variable
		$coordinates = $data['coordinates'];
		$entities = $data['entities'];
		$place = $data['place'];
		$quotedStatus = $data['quoted_status'];
		$retweetedStatus = $data['retweeted_status'];
		$user = $data['user'];
		$witheldCopyright = $data['witheld_copyright'];
		$witheldInCountries = $data['witheld_in_countries'];
		$withld_scope = $data['witheld_scope'];
		
		// Comma pre-processing: all commas needed to be removed or re-encoded to not confuse the CSV file format
		$data['text'] = str_replace(',', '&#44;', $data['text']);
		$coordinates['coordinates'] = str_replace(',', ' ', $coordinates['coordinates']);
		$user['location'] = str_replace(',', '', $user['location']);
		$user['name'] = str_replace(',', '', $user['location']);
		$place['full_name'] = str_replace(',', '', $place['full_name']);
		
		// BEGIN FEATURE PRINTING
		$outputString = "{$data['contributors']}" . "," . "{$coordinates['coordinates']}" . "," . "{$data['created_at']}" . ",";
		
		// Entities Array printing
		$entities = $data['entities'];
		foreach($entities['urls'] as $urls)
		{
			$outputString = "{$outputString}" . "{$urls['expanded_url']}" . ";";
		}
		unset($urls);
		$outputString = rtrim($outputString, ';');
		$outputString = "{$outputString}" . ",";
		foreach($entities['user_mentions'] as $userMentions)
		{
			$outputString = "{$outputString}" . "{$userMentions['id_str']}" . ";";
		}
		unset($userMentions);
		$outputString = rtrim($outputString, ';');
		$outputString = "{$outputString}" . ",";
		foreach($entities['user_mentions'] as $userMentions)
		{
			$outputString = "{$outputString}" . "{$userMentions['name']}" . ";";
		}
		unset($userMentions);
		$outputString = rtrim($outputString, ';');
		$outputString = "{$outputString}" . ",";
		foreach($entities['user_mentions'] as $userMentions)
		{
			$outputString = "{$outputString}" . "{$userMentions['screen_name']}" . ";";
		}
		unset($userMentions);
		$outputString = rtrim($outputString, ';');
		$outputString = "{$outputString}" . ",";
		foreach($entities['hashtags'] as $hashtags)
		{
			$outputString = "{$outputString}" . "{$hashtags['text']}" . ";";
		}
		unset($hashtags);
		$outputString = rtrim($outputString, ';');
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
		foreach($entities['symbols'] as $symbols)
		{
			$outputString = "{$outputString}" . "{$symbols['text']}" . ";";
		}
		unset($symbols);
		$outputString = rtrim($outputString, ';');
		
		$outputString = "{$outputString}" . "," . "{$data['favorite_count']}" . "," . "{$data['filter_level']}" . "," . "{$data['id_str']}" . "," . "{$data['in_reply_to_screen_name']}" . "," . "{$data['in_reply_to_status_id_str']}" . "," . "{$data['in_reply_to_user_id_string']}" . "," . "{$place['id']}" . "," . "{$place['place_type']}" . "," . "{$place['full_name']}" . "," . "{$place['country']}" . "," . "{$data['possibly_sensitive']}" . "," . "{$data['quoted_status_id_str']}" . "," . "{$data['quoted_status']}" . "," . "{$data['scopes']}" . "," . "{$data['retweet_count']}" . "," . "{$data['retweeted_status']}" . "," . "{$data['source']}" . "," . "{$data['text']}" . "," . "{$data['truncated']}" . "," . "{$user['id_str']}" . "," . "{$user['name']}" . "," . "{$user['screen_name']}" . "," . "{$user['location']}" . "," . "{$user['created_at']}" . "," . "{$user['statuses_count']}" . "," . "{$user['followers_count']}" . "," . "{$user['friends_count']}" . "," . "{$user['listed_count']}" . "," . "{$user['contributors_enabled']}" . "," . "{$user['geo_enabled']}" . "," . "{$user['protected']}" . "," . "{$user['verified']}" . "," . "{$user['default_profile']}" . "," . "{$user['default_profile_image']}" . "," . "{$user['withheld_in_countries']}" . "," . "{$user['withheld_scope']}" . "," . "{$data['withheld_copyright']}" . "," . "{$data['withheld_in_countries']}" . "," . "{$data['withheld_scope']}";
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
$dataHeaders = "contributors,coordinates,created_at,urls,user_mentions_id_str,user_mentions_name,user_mentions_screenname,hashtags,media,symbols,favorite_count,filter_level,id_str,in_reply_to_screen_name,in_reply_to_status_id_str,in_reply_to_user_id_str,place_id,place_type,place_name,place_country,possibly_sensitive,quoted_status_id_str,quoted_status,scopes,retweet_count,retweeted_status,source,text,truncated,user_id_str,user_name,user_screenname,user_location,user_creation_date,user_statuses_count,user_followers_count,user_following_count,user_listed_count,user_contributors_enabled,user_geo_enabled,protected_user,verified_user,default_user_profile,default_user_profile_image,user_withheld_in_countries,user_withheld_scope,tweet_withheld_copyright,tweet_withheld_in_countries,tweet_withheld_scope\n";
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
