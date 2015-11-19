<?php
error_reporting(E_ALL ^ E_NOTICE);

// ---------------------------------------------------------
// Include Twitter Libraries
// ---------------------------------------------------------

require 'tmhOAuth.php';
require 'tmhUtilities.php';

// ------------------------------------------
// Parameter array
// ------------------------------------------

$params = array();
// IDs to search (found from data streaming)
$params['id'] = '667155188076138496,667155188109701120,667155187967123456,667155188025679872,667155188235509760,667155188273324035,667155188046798848,667155188243918849,667155188050993152,667155188050956289,667155188327710721,667155188180996096,667155188965367808,667155188482895872,667155188856303616,667155188378128384,667155189011378177,667155188835360768,667155188113784832,667155188419969024,667155189070082048,667155189087002624,667155189078470656,667155188814372864,667155188981981188,667155188550094848,667155188919107584,667155188877275136,667155187937624064,667155188810014723,667155188940218368,667155189019754496,667155189170872320,667155192354336768,667155192224288770,667155192283025408,667155192299712512,667155192467603456,667155192413093888,667155192354381824,667155191586799616,667155192261967872,667155191238553600,667155192123539456,667155192110907392,667155192152915969,667155192400519173,667155192173981696,667155192324993024,667155192287137792,667155192236797952,667155192283013121,667155192211619840,667155192249479168,667155191792185344,667155192220131329,667155192366899200,667155192370982912,667155192312233984,667155192207552512,667155192308178944,667155192069124096,667155192496971776,667155192387919872,667155192547295233,667155192211705856,667155196636758016,667155196611571716,667155196452171776,667155196351520768,667155196355682305,667155196250836992,667155196540252160,667155196582072323,667155196544335872,667155196720619521,667155196481376256,667155196548685826,667155196611420164,667155196422688770,667155196158615552,667155196150026240,667155196225564673,667155196519297024,667155196439601152,667155196708069377,667155196527538176,667155196489809920,667155196330426368,667155196473188352,667155196728975360,667155195667742720,667155196569612288,667155196443783168,667155196741488640,667155195709685761,667155196347203584,667155196754173954,667155196628344833,667155200747167744';

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
// Search for the specified tweets
// ------------------------------------------

// Create the file to which the data will be written
// TODO: edit the suffix to match the input file of ID strings to search
$outputFile = "search_API_output_" . date('Y-m-d-hisT') . ".csv";

// Print the CSV file headers
$dataHeaders = "id_str,created_at,text,new_retweet_count,new_favorite_count\n";
// Caution: overwrites any current data in the file
if (file_put_contents($outputFile, $dataHeaders) === FALSE)
{
	// FALSE indicates that an error occurred during the fwrite operation
}

$url = "https://api.twitter.com/1.1/statuses/lookup.json";
$tmhOAuth->request('GET', $url, $params);

if ($tmhOAuth->response['code'] == 200) 
{
	$data = json_decode($tmhOAuth->response['response'], true);
	// print_r($data);
    foreach ($data as $tweet) 
	{
		// Attempts to replace all newline characters in the tweets with an empty string
		$newlineChars = array( PHP_EOL, '\n', '\r' );
		// $data['text'] = str_replace(PHP_EOL, '', $data['text']);
		$tweet['text'] = str_replace($newlineChars, '', $tweet['text']);
		
		// Substitude the HTML comma code for any comma in text
		$tweet['text'] = str_replace(',', '&#44;', $tweet['text']); 
		
		$outputString = "{$tweet['id_str']}" . "," . "{$tweet['created_at']}" . "," . "{$tweet['text']}" . ",";
		$outputString = "{$outputString}" . "{$tweet['retweet_count']}" . ",";
		$outputString = "{$outputString}" . "{$tweet['favorite_count']}" . ",";
		
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
	
} 
else 
{
	$data = htmlentities($tmhOAuth->response['response']);
	echo 'There was an error.' . PHP_EOL;
	var_dump($tmhOAuth);
}

?>
