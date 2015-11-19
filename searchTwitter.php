<?php
error_reporting(E_ALL ^ E_NOTICE);

// ---------------------------------------------------------
// Include Twitter Libraries
// ---------------------------------------------------------

require 'tmhOAuth.php';
require 'tmhUtilities.php';


// IDs to search (found from data streaming)
$params['id'] = '667163276611084293,667163276392964098,667163276476850176,667163276543832064,667163275667382273,667163276023832576,667163276862730240,667163276015341568,667163276288110592,667163276422201344,667163276367822848,667163276078264320,667163276241977344,667163276296527872,667163276678029312,667163276443258884,667163276690722820,667163276317298688,667163276032118784,667163276766244869,667163276892065794,667163276904636417,667163276514430977,667163276732530688,667163276971745282,667163276002787329,667163276006985728,667163278968246272,667163278829879296,667163278955663361,667163278477537281,667163278569660417,667163277944754180,667163278980874241,667163278808846336,667163278808891396,667163278632599552,667163278808735745,667163278985011200,667163279006003200,667163278775336961,667163278930542593,667163279035392000,667163278901174272,667163279010234368,667163278909444096,667163278833938432,667163279106686976,667163278712446976,667163278439682048,667163278854918144,667163278972465152,667163278930526210,667163279119286272,667163282747228160,667163282910928896,667163281749069824,667163282466193408,667163282818646016,667163282738843650,667163282487119872,667163282994675713,667163283208712192,667163283204493312,667163282302566400,667163283254861825,667163283128913920,667163283019821056,667163282990469120,667163282881380352,667163283053383680,667163282742992896,667163283313528833,667163282717863936,667163283120525312,667163282747232256,667163282009145346,667163287402991616,667163287407230976,667163287117762560,667163287004516353,667163286568222720,667163287247802368,667163287063171072,667163286610321408,667163287214292992,667163287201693697,667163287377813504,667163287402975234,667163287398797312,667163287151243264,667163287147139073,667163287201542144,667163287285444608,667163287163789312,667163287356747777,667163287486930945,667163286228459520,667163287667253248,667163287491072000';

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

/*
 * Expects as input a file of format: 
 * data_collection_output_date('Y-m-d-hisT').csv
 * DO NOT MESS WITH THIS FORMAT!
 */
$inputFile = $argv[1];
$filenameParts = explode("_", $inputFile);

// Create the file to which the data will be written
// TODO: edit the suffix to match the input file of ID strings to search
$outputFile = "search_API_output_" . "{$filenameParts[3]}";

// Print the CSV file headers
$dataHeaders = "id_str,created_at,text,new_retweet_count,new_favorite_count\n";
// Caution: overwrites any current data in the file
if (file_put_contents($outputFile, $dataHeaders) === FALSE)
{
	// FALSE indicates that an error occurred during the fwrite operation
}

$params = array();
$searchStringFile = fopen($inputFile, "r") or die("Unable to open the file of ID strings!");
while(!feof($searchStringFile))
{
	$fileLine = fgets($searchStringFile);
	if ((strcmp($fileLine, PHP_EOL) != 0) & (strcmp($fileLine, "\n") != 0) & (strcmp($fileLine, "\r") != 0))
	{
		$params['id'] = "{$fileLine}";
		
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
	}
}
fclose($searchStringFile);

?>
