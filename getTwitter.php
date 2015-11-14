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

$params['track'] = 'a,the,colts,panthers,paris,isis';

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
		
		$outputString = "{$data['contributors']}" . "," . "{$data['coordinates']}" . "," . "{$data['created_at']}" . "," .  "{$data['entities']}" . "," . "{$data['favorite_count']}" . "," . "{$data['filter_level']}" . "," . "{$data['id_str']}" . "," . "{$data['in_reply_to_screen_name']}" . "," . "{$data['in_reply_to_status_id_str']}" . "," . "{$data['in_reply_to_user_id_string']}" . "," . "{$data['place']}" . "," . "{$data['possibly_sensitive']}" . "," . "{$data['quoted_status_id_str']}" . "," . "{$data['quoted_status']}" . "," . "{$data['scopes']}" . "," . "{$data['retweet_count']}" . "," . "{$data['retweeted_status']}" . "," . "{$data['source']}" . "," . "{$data['text']}" . "," . "{$data['truncated']}" . "," . "{$data['user']}" . "," . "{$data['witheld_copyright']}" . "," . "{$data['witheld_in_countries']}" . "," . "{$data['witheld_scope']}";
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
$dataHeaders = "contributors,coordinates,created_at,entities,favorite_count,filter_level,id_str,in_reply_to_screen_name,in_reply_to_status_id_str,in_reply_to_user_id_str,place,possibly_sensitive,quoted_status_id_str,quoted_status,scopes,retweet_count,retweeted_status,source,text,truncated,user,withheld_copyright,withheld_in_countries,witheld_scope\n";
if (file_put_contents($outputFile, $dataHeaders) === FALSE) // Caution: overwrites any current data in the file
{
	// FALSE indicates that an error occurred during the fwrite operation
}

$url = 'https://stream.twitter.com/1/statuses/filter.json';
$tmhOAuth->streaming_request('POST', $url, $params, 'my_streaming_callback');

?>
