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
$params['delimited'] = "length"; // Provides the length of the status messages

// ---------------------------------------------------------
// Streaming API time limit and bounding box settings
// ---------------------------------------------------------

// Start timer
$time_pre = microtime(true);
$time_limit = 60;
set_time_limit($time_limit + 30);

// $boundingBox = $_POST["boundingBox"];
// $params['locations'] = $boundingBox;

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
	// keep running time
	global $time_pre;
	$time_post = microtime(true);
	$exec_time = $time_post - $time_pre;
	global $time_limit;
	if($exec_time > $time_limit){
		return true;
	}
	$data = json_decode($data, true);
	$date = strtotime($data['created_at']);
	$data['text'] = str_replace(PHP_EOL, '', $data['text']);
	echo "{$data['id_str']}\t{$date}\t{$data['text']}" . PHP_EOL . "<br />";
	flush();
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
