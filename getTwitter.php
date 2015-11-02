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

// ---------------------------------------------------------
// Streaming API time limit and bounding box settings
// ---------------------------------------------------------

// Start timer
$time_pre = microtime(true);
$time_limit = 60;
set_time_limit($time_limit + 30);


// $params['locations'] = ...;

// ------------------------------------------
// Read the query string
// ------------------------------------------

// $params['track'] = " ";
$params['track'] = "mets";

// ------------------------------------------
// Define callback function for Streaming API
// ------------------------------------------

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

	$outputString = "id_str: " . "{$data['id_str']}" . " date created: " . "{$data['created_at']}" . "\n";
	if (file_put_contents($outputFile, $outputString, FILE_APPEND) === FALSE)
	{
		// FALSE indicates that an error occurred during the fwrite operation
	}
	$outputString = "\tTweet text: " . "{$data['text']}" . "\n";
	if (file_put_contents($outputFile, $outputString, FILE_APPEND) === FALSE)
	{
		// FALSE indicates that an error occurred during the fwrite operation
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
