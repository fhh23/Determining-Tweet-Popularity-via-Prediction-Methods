<?php
error_reporting(E_ALL ^ E_NOTICE);

// ---------------------------------------------------------
// Include Twitter Libraries
// ---------------------------------------------------------

require 'tmhOAuth.php';
require 'tmhUtilities.php';

// ---------------------------------------------------------
// Define a function for processing a search_idStrings file
// ---------------------------------------------------------
function process_searchQuery_file($tmhObject, $inputFilename, $outputFilename)
{
	// Print the CSV file headers
	$dataHeaders = "id_str,created_at,text,new_retweet_count,new_favorite_count\n";
	// Caution: overwrites any current data in the file
	if (file_put_contents($outputFilename, $dataHeaders) === FALSE)
	{
		// FALSE indicates that an error occurred during the fwrite operation
	}

	$params = array();
	$searchStringFile = fopen(__DIR__ . "/" . $inputFilename, "r") or die("Unable to open the file of ID strings!\n");
	while(!feof($searchStringFile))
	{
		$fileLine = fgets($searchStringFile);
		// Ignore the line separations inserted in the file for readability
		if ((strcmp($fileLine, PHP_EOL) != 0) & (strcmp($fileLine, "\n") != 0) & (strcmp($fileLine, "\r") != 0))
		{
			$fileline = substr($fileline, 0, -1); // remove the newline character at the end of the query
			$params['id'] = "{$fileLine}";
		
			$url = "https://api.twitter.com/1.1/statuses/lookup.json";
			$tmhObject->request('GET', $url, $params);

			if ($tmhObject->response['code'] == 200) 
			{
				$data = json_decode($tmhObject->response['response'], true);
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
		
					if (file_put_contents($outputFilename, $outputString, FILE_APPEND) === FALSE)
					{
						// FALSE indicates that an error occurred during the fwrite operation
					}
		
					// End the data record
					if (file_put_contents($outputFilename, "\n", FILE_APPEND) === FALSE)
					{
						// FALSE indicates that an error occurred during the fwrite operation
					}
    			}
			} 
			else 
			{
				$data = htmlentities($tmhObject->response['response']);
				echo 'There was an error.' . PHP_EOL;
				var_dump($tmhObject);
			}
		}
	}
	fclose($searchStringFile);
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
// Search for the specified tweets
// ------------------------------------------

/*
 * Expects as input one of three filename formats: 
 * (1) search_idStrings_date('Y-m-d-hisT').txt
 * (2) streaming_session_date('Y-m-d-hisT')
 * (3) streaming_session_date('Y-m-d-hisT')/search_input_date('Y-m-d-hisT').txt
 * DO NOT MESS WITH THIS FORMAT!
 */
$inputFile = $argv[1];
$filenameParts = explode("_", $inputFile);

// Determine which filename format was provided
if (strcmp($filenameParts[0], 'search') == 0)
{
	// Case: Input file is a set of at most 180 search queries, each of which can contain up to 100 tweet ID strings
	// Create the file to which the data will be written
	$outputFile = "search_API_output_" . "{$filenameParts[2]}";
	$outputFile = substr($outputFile, 0, -4);
	$outputFile = "{$outputFile}" . ".csv";
	
	process_searchQuery_file($tmhOAuth, $inputFile, $outputFile);
}
elseif (strcmp($filenameParts[0], 'streaming') == 0)
{
	// Case: Input file is a list of of search_idStrings files to be processed at 16 minute intervals
	$pathAndFilename = explode("/", $inputFile);
	$dir = $pathAndFilename[0];
	
	if ($pathAndFilename[1] == "")
	{
		// Sub-case: no filename provided after the directory name
		// Assume that that the directory contains a filename of format search_input_date('Y-m-d-hisT').txt
		// with the date matching the name of the directory
		$searchFileListFilename = $dir . "/" . "search_input_" . "{$filenameParts[2]}";
		$searchFileListFilename = substr($searchFileListFilename, 0, -1);
		$searchFileListFilename = "{$searchFileListFilename}" . ".txt";
		// BEGIN DEBUG
		echo "Expected filename: " . "{$searchFileListFilename}". "\n";
		// END DEBUG
		$searchFileListFile = fopen(__DIR__ . "/" . $searchFileListFilename, "r") or die("Unable to find and open the expected file containing the list of search files!\n");
		
		// Create the file to which the data will be written
		$outputFile = "search_API_output_" . "{$filenameParts[2]}";
		$outputFile = substr($outputFile, 0, -1);
		$outputFile = "{$outputFile}" . ".csv";
	}
	else
	{
		// Sub-case: filename provided after the directory name (filename should be of the specified format)
		$searchFileListFile = fopen(__DIR__ . "/" . $inputFile, "r") or die("Unable to the open the specified file containing the list of search files!\n");
		
		// Create the file to which the data will be written
		$outputFile = "search_API_output_" . "{$filenameParts[2]}";
		$outputFile = substr($outputFile, 0, -4);
		$outputFile = "{$outputFile}" . ".csv";
	}
	
	$initialRun = 1;
	while(!feof($searchFileListFile))
	{		
		// initialRun variable prevents the pause from occurring before the first search
		if ($initialRun == 1)
		{
			$initialRun = 0;
		}
		else
		{
			// Pause the program for 16 minutes to abide by the Twitter API Rate Limit
			echo "Pausing the program for 16 minutes to follow Twitter API Rate Limit...\n";
			sleep(960);
			echo "Restarting the program and performing the next search!\n";
		}
		
		$filename = fgets($searchFileListFile);
		if ((strcmp($filename, PHP_EOL) != 0) & (strcmp($filename, "\n") != 0) & (strcmp($filename, "\r") != 0) & (strcmp($filename, "") != 0))
		{
			$filename = substr($filename, 0, -1); // remove the newline character in the filename
			$filenameParts = explode("_", $filename);
			// TODO: remove the echo to the console use for debugging purposes
			echo "Processing file " . "{$filename}" . "...\n";

			process_searchQuery_file($tmhOAuth, $dir . "/" . $filename, $dir . "/" . $outputFile);
		}
	}
	fclose($searchFileListFile);
}
else
{
	echo "INCORRECT INPUT FILE FORMAT!";
	// TODO: quit the program with an error message to the console
}

?>
