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
$time_limit = 14400;
set_time_limit($time_limit + 30);

// Set the comma separate list of longitude/latitude pairs
// to specify bounding boxes for filtering tweets
// $params['locations'] = ...;

// -------------------------------------------------------
// Set the keywords to search for in streaming tweets
// -------------------------------------------------------

$params['track'] = 'a,the,colts,panthers';

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
		$data['text'] = str_replace(PHP_EOL, '', $data['text']);
		
		// Print CSV file headers
		$dataHeaders = "contributors,coordinates,created_at,entities,favorite_count,filter_level,id_str,in_reply_to_screen_name,in_reply_to_status_id_str,in_reply_to_user_id_str,place,possibly_sensitive,quoted_status_id_str,quoted_status,scopes,retweet_count,retweeted_status,source,text,truncated,user,withheld_copyright,withheld_in_countries,witheld_scope\n";
		if (file_put_contents($outputFile, $dataHeaders) === FALSE) // Caution: overwrites any current data in the file
		{
			// FALSE indicates that an error occurred during the fwrite operation
		}

		$outputString = "id_str: " . "{$data['id_str']}" . " date created: " . "{$data['created_at']}" . "\n";
		$outputString = "{$outputString}" . "Tweet text: " . "{$data['text']}" . "\n";
		$userData = $data['user'];
		$outputString = "{$outputString}" . "User Data for " . "{$userData['name']}" . "...\n";
		$outputString = "{$outputString}" . "\tScreen Name: " . "{$userData['screen_name']}" . "\n";
		$outputString = "{$outputString}" . "\tUTC Creation Date: " . "{$userData['created_at']}" . "\n";
		$outputString = "{$outputString}" . "\tDefault Profile: " . "{$userData['default_profile']}" . "\n";
		$outputString = "{$outputString}" . "\tDefault Profile Image: " . "{$userData['default_profile_image']}" . "\n";
		$outputString = "{$outputString}" . "\tFollowers: " . "{$userData['followers_count']}" . "\n";
		$outputString = "{$outputString}" . "\tNumber of Friends: " . "{$userData['friends_count']}" . "\n";
		$outputString = "{$outputString}" . "\tMember List Count: " . "{$userData['listed_count']}" . "\n";
		$outputString = "{$outputString}" . "\tStatuses Count: " . "{$userData['statuses_count']}" . "\n";
		$outputString = "{$outputString}" . "\tAssociated URL: " . "{$userData['url']}" . "\n";
		$outputString = "{$outputString}" . "Coordinates: " . "{$data['coordinates']}" . "\n";
		if (file_put_contents($outputFile, $outputString, FILE_APPEND) === FALSE)
		{
			// FALSE indicates that an error occurred during the fwrite operation
		}
		// TODO: order the "features" according to their believed importance for this project (not alphabetical)
		// TODO: check and address null or empty values?
		$outputString = "\tContributors: " . "{$data['contributors']}" . "\n";
		$outputString = "{$outputString}" . "\tEntities: " . "{$data['entities']}" . "\n"; // TODO: add additional processing for these
		$outputString = "{$outputString}" . "\tFavorite Count: " . "{$data['favorite_count']}" . "\n";
		$outputString = "{$outputString}" . "\tFilter Level: " . "{$data['filter_level']}" . "\n";
		$outputString = "{$outputString}" . "\tIn reply to data...\n";
		$outputString = "{$outputString}" . "\t\tIn reply to Screen Name: " . "{$data['in_reply_to_screen_name']}" . "\n";
		$outputString = "{$outputString}" . "\t\tIn reply to Status ID: " . "{$data['in_reply_to_status_id_str']}" . "\n";
		$outputString = "{$outputString}" . "\t\tIn reply to User ID: " . "{$data['in_reply_to_user_id_str']}" . "\n";
		$outputString = "{$outputString}" . "\tPlace: " . "{$data['place']}" . "\n"; // Note that this is different from Coordinates
		$outputString = "{$outputString}" . "\tPossibly Sensitive: " . "{$data['possibly_sensitive']}" . "\n";
		$outputString = "{$outputString}" . "\tQuoted Status: id = " . "{$data['quoted_status_id_str']}" . " status = " . "{$data['quoted_status']}" . "\n";
		$outputString = "{$outputString}" . "\tScopes: " . "{$data['scopes']}" . "\n";
		$retweetedStatus = $data['retweeted_status'];
		// BEGIN DEBUG: print the retweet data
		// printf_r($retweetedStatus);
		// END DEBUG
		$outputString = "{$outputString}" . "\tRetweet data...\n";
		$outputString = "{$outputString}" . "\t\tRetweeted Text: " . "{$retweetedStatus['text']}" . "\n";
		$outputString = "{$outputString}" . "\t\tRetweet Count: " . "{$retweetedStatus['retweet_count']}" . "\n";
		$outputString = "{$outputString}" . "\t\tFavorite Count: " . "{$retweetedStatus['favorite_count']}" . "\n";
		// TODO print the entities embedded in the Retweet attributes
		$outputString = "{$outputString}" . "\tSource: " . "{$data['source']}" . "\n";
		if (file_put_contents($outputFile, $outputString, FILE_APPEND) === FALSE)
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

$url = 'https://stream.twitter.com/1/statuses/filter.json';
$tmhOAuth->streaming_request('POST', $url, $params, 'my_streaming_callback');

?>
