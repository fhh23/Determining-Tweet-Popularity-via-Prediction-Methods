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

	// BEGIN DEBUG: print the JSON before decoding to see all attribute value pairs
	// echo $data;
	// END DEBUG

	// Create the file to which the data will be written
	// TODO: make the filename dynamic (currently this needs to be changed for any run where we want a unique file)
	// $outputFile = fopen("data_collection_output.csv", "w");
	$outputFile = "data_collection_output.txt";

	$data = json_decode($data, true); // Converts the JSON string to a PHP variable (and converts the output into an associative array)
	$data['text'] = str_replace(PHP_EOL, '', $data['text']);
	// echo "{$data['id_str']}\t{$date}\t{$data['text']}" . PHP_EOL . "<br />";


	$retweetCount = $data['retweet_count'];
	if (0 < $retweetCount)
	{
		if (file_put_contents($outputFile, "WINNER! Non-zero retweet count!", FILE_APPEND) === FALSE)
		{
			// FALSE indicates that an error occurred during the fwrite operation
		}
	}
	if ( "" != $data['id_str'] )
	{
		$outputString = "id_str: " . "{$data['id_str']}" . " date created: " . "{$data['created_at']}" . "\n";
		$outputString = "{$outputString}" . "Tweet text: " . "{$data['text']}" . "\n";
		$outputString = "{$outputString}" . "User Data...\n";
		$userData = json_decode($data['user'], true);
		// BEGIN DEBUG: check the output of the decode functionality
		print_r($data['user']);
		// END DEBUG
		$outputString = "{$outputString}" . "\tFollowing: " . "{$userData['following']}" . "\n";
		$outputString = "{$outputString}" . "\tFollowers: " . "{$userData['followers_count']}" . "\n";
		$outputString = "{$outputString}" . "\tNumber of Friends: " . "{$userData['friends_count']}" . "\n";
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
		$outputString = "{$outputString}" . "\tRetweet data...\n";
		$outputString = "{$outputString}" . "\t\tRetweet Count: " . "{$data['retweet_count']}" . "\n";
		$outputString = "{$outputString}" . "\t\tRetweeted Status: " . "{$data['retweeted_status']}" . "\n";
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

$url = 'https://stream.twitter.com/1/statuses/filter.json';
$tmhOAuth->streaming_request('POST', $url, $params, 'my_streaming_callback');

?>
