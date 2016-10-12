<?php
error_reporting(E_ALL ^ E_NOTICE);

/*
 * Authors: Bonnie Reiff and Farhan Hormasji
 * Modified from getTwitter.php provided by Dr. Tan
 * CSE 881 Project, Fall 2015
 */

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
$time_limit = 900; // Short run: 15 minute data collection
set_time_limit($time_limit + 30);

// Set the comma separate list of longitude/latitude pairs
// to specify bounding boxes for filtering tweets
// $params['locations'] = ...;

// -------------------------------------------------------
// Set the keywords to search for in streaming tweets
// -------------------------------------------------------

// Common English word search:
$params['track'] = 'the,i,to,a,and,is,in,it,you,of,for,on,my,that,at,with,me,do,have,be,are,but';
// Trending word search: 
// $params['track'] = 'TrumpIsDisqualifiedParty,JohnLennon,SurviveFamilyGatheringsBy,TrumpOnCNN,TravelTuesday,Bow Wow,iOS 9.2,Craig David,Jon Jay,Varela,Google Fiber,Troy Riggs,camerondallas,ImagineIfThereWas,MUFC,Zobrist,RoyalVariety,Man U,THE 100 TRAILER COMING SOON,Signup Now,Satoshi Nakamoto,Trump';

// ---------------------------------------------
// Define callback function for Streaming API
// ---------------------------------------------

function my_streaming_callback($data, $length, $metrics) 
{
	global $idStrCount; global $idStrString; global $searchAPIFile;
	
	// Keep running time
	global $time_pre;
	// Returns the Unix timestamp with microseconds as a floating point value
	$time_post = microtime(true); 
	$exec_time = $time_post - $time_pre;
	global $time_limit;
	if ( $exec_time > $time_limit )
	{
		echo "Reached end of time limit! ";
		// Write the final idStrString contents to the appropriate file			
		if (file_put_contents($searchAPIFile, $idStrString, FILE_APPEND) === FALSE)
		{
			// FALSE indicates that an error occurred during the fwrite operation
		}
		return true;
	}

	// Use the filename created before the streaming request post
	global $outputFile;
	// Use the previously created global variable to track hashtag values for frequency analysis
	global $hashtagFrequencies;

	// Converts the JSON string to a PHP variable 
	// (and converts the output into an associative array)
	$data = json_decode($data, true); 
	
	// Only prints the tweet information if $data has valid contents,
	// the tweet is not already a retweet, and the user has more than 500 followers
	$retweetedStatus = $data['retweeted_status'];
	$user = $data['user'];
	if((!is_null($data['text'])) & (strcmp($retweetedStatus['id_str'], '') == 0) & ($user['followers_count'] > 500))
	{
		// Maintain a count of the numbers of tweets in an output file
		global $outputFileDatapointCounter; global $outputFilesListFile;
		if ($outputFileDatapointCounter > 14999) 
		{
			echo "15,000 tweets! Pausing for 16 minutes and then starting new file...\n";
			return true; // Exit the streaming program
		}
		else
		{
			$outputFileDatapointCounter = $outputFileDatapointCounter + 1;
		}
		
		// Add the id_str to the global variable to be passed to the Search API
		if ($idStrCount > 98)
		{
			# echo "Search API Tweet Limit Reached. Printing variable to file!\n";
			$idStrString = "{$idStrString}" . "," ."{$data['id_str']}" . "\n\n";
			if (file_put_contents($searchAPIFile, $idStrString, FILE_APPEND) === FALSE)
			{
				// FALSE indicates that an error occurred during the fwrite operation
			}
			// Reset the variables
			$idStrCount = 0;
			$idStrString = '';
		}
		elseif ($idStrCount == 0)
		{
			$idStrCount = $idStrCount + 1;
			$idStrString = "{$data['id_str']}";
		}
		else
		{
			$idStrCount = $idStrCount + 1;
			$idStrString = "{$idStrString}" . "," . "{$data['id_str']}";
		}
		
		// Attempts to replace all newline characters in the tweets with an empty string
		$newlineChars = array( PHP_EOL, '\n', '\r' );
		// $data['text'] = str_replace(PHP_EOL, '', $data['text']);
		$data['text'] = str_replace($newlineChars, '', $data['text']);
		
		// Create a data dump to have a log of all information 
		// provided for each tweet
		// Call to the streamTweets.php function should be formatted as
		// "php streamTwitter.php > [ data_dump_filename ]"
		// print_r($data); 
		
		// Save any fields Array fields into separate variables for 
		// easier parsing
		$entities = $data['entities'];
		$place = $data['place'];
		
		/*
		* Comma pre-processing: all commas needed to be removed 
		* or re-encoded to not confuse the CSV file format
		*/
		
		// Substitude the HTML comma code for any comma in text
		$data['text'] = str_replace(',', '&#44;', $data['text']); 
		// Remove the commas in other fields and replace with spaces or empty characters
		$user['name'] = str_replace(',', '', $user['name']);
		$place['name'] = str_replace(',', '', $place['name']);
		
		
		/* BEGIN FEATURE PRINTING */
		$outputString = "{$data['id_str']}" . "," . "{$data['created_at']}" . "," . "{$data['text']}" . ",";
		$outputString = "{$outputString}" . "{$data['retweet_count']}" . "," . "{$data['favorite_count']}" . ",";
		
		// Entities Array printing
		$urlAndMediaCount = 0;
		if(is_array($entities['urls']))
		{
			foreach($entities['urls'] as $urls)
			{
				// $outputString = "{$outputString}" . "{$urls['expanded_url']}" . ";";
				$urlAndMediaCount = $urlAndMediaCount + 1;
			}
			unset($urls);
			// $outputString = rtrim($outputString, ';');
		}
		if(is_array($entities['media']))
		{
			foreach($entities['media'] as $media)
			{
				// $outputString = "{$outputString}" . "{$media['id_str']}" . ";";
				$urlAndMediaCount = $urlAndMediaCount + 1;
			}
			unset($media);
			// $outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . "{$urlAndMediaCount}" . ",";
		unset($urlAndMediaCount);
		
		$userMentionsCount = 0;
		if(is_array($entities['user_mentions']))
		{
			foreach($entities['user_mentions'] as $userMentions)
			{
				// $outputString = "{$outputString}" . "{$userMentions['id_str']}" . ";";
				$userMentionsCount = $userMentionsCount + 1;
			}
			unset($userMentions);
			// $outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . "{$userMentionsCount}" . ",";
		unset($userMentionsCount);
		if(is_array($entities['user_mentions']))
		{
			foreach($entities['user_mentions'] as $userMentions)
			{
				// Remove commas from the usernames and replace the commas with blank spaces
				$processedUsername = str_replace(',', '', $userMentions['name']);
				$outputString = "{$outputString}" . "{$processedUsername}" . ";";
			}
			unset($userMentions);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . ",";
		
		$hashtagCount = 0;
		if(is_array($entities['hashtags']))
		{
			foreach($entities['hashtags'] as $hashtags)
			{
				$outputString = "{$outputString}" . "{$hashtags['text']}" . ";";
				$hashtagCount = $hashtagCount + 1;
				
				// Frequency analysis of hashtags
				if (array_key_exists($hashtags['text'], $hashtagFrequencies))
				{
					$hashtagFrequencies[$hashtags['text']] = $hashtagFrequencies[$hashtags['text']] + 1;
				}
				else
				{
					$hashtagFrequencies[$hashtags['text']] = 1;
				}
			}
			unset($hashtags);
			$outputString = rtrim($outputString, ';');
		}
		$outputString = "{$outputString}" . "," . "{$hashtagCount}" . ",";
		unset($hashtagCount);
		
		if (strcmp($data['quoted_status_id_str'], '') == 0)
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		$outputString = "{$outputString}" . "{$data['quoted_status_id_str']}" . ",";
		if (strcmp($data['in_reply_to_user_id_str'], '') == 0)
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		$outputString = "{$outputString}" . "{$data['in_reply_to_user_id_str']}" . ",";
		if (strcmp($data['in_reply_to_screen_name'], '') == 0)
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		$outputString = "{$outputString}" . "{$data['in_reply_to_screen_name']}" . ",";
		if (strcmp($data['in_reply_to_status_id_str'], '') == 0)
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		$outputString = "{$outputString}" . "{$data['in_reply_to_status_id_str']}" . ",";

		$outputString = "{$outputString}" . "{$place['name']}" . ",";
		
		if ($data['possibly_sensitive'] == 1)
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		
		$outputString = "{$outputString}" . "{$user['id_str']}" . "," . "{$user['name']}" . "," . "{$user['created_at']}" . "," . "{$user['statuses_count']}" . "," . "{$user['followers_count']}" . "," . "{$user['friends_count']}" . "," . "{$user['listed_count']}" . ",";
		
		if ($user['contributors_enabled'] == 1)
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		
		if ($user['geo_enabled'] == 1)
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		
		if ($user['protected']== 1)
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		
		if($user['verified'] == 1)
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "0" . ",";
		}	
			
		if ($user['default_profile'] == 1)
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		
		if ($user['default_profile_image'] == 1)
		{
			$outputString = "{$outputString}" . "1" . ",";
		}
		else
		{
			$outputString = "{$outputString}" . "0" . ",";
		}
		/* END FEATURE PRINTING */
		
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

// Store the inputs to the program from the collectData shell script
$outputDir = $argv[1];
$outputFilesListFile = $argv[2];
$hashtagAnalysisFile=$argv[3];
chdir($outputDir);

// Create the files to which the data will be written
$outputFile = "data_collection_output_" . date('Y-m-d-hisT') . ".csv";
$searchAPIFile = "search_idStrings_" . date('Y-m-d-hisT') . ".txt";
// Keep track of all the file names used in the streaming session to pass to the searchTwitter PHP program
if (file_put_contents($outputFilesListFile, "{$searchAPIFile}" . "\n", FILE_APPEND) === FALSE)
{
	// FALSE indicates that an error occurred during the fwrite operation
}

// Create a variable for the number of tweets currently in the output file data
$outputFileDatapointCounter = 0;
// Create a variable for frequency analysis of hashtags used in the stream data
$hashtagFrequencies = array();
// Create the variables related to building the queries for the Search API
$idStrCount = 0;
$idStrString = '';

// Print CSV file headers
$dataHeaders = "id_str,created_at,text,retweet_count,favorite_count,";
$dataHeaders = "{$dataHeaders}" . "url_and_media_count,user_mentions_count,uer_mentions_names,hashtags,hashtag_count,";
$dataHeaders = "{$dataHeaders}" . "quoted_status,quoted_status_id_str,";
$dataHeaders = "{$dataHeaders}" . "binary_in_reply_to_user_id,in_reply_to_user_id,";
$dataHeaders = "{$dataHeaders}" . "binary_in_reply_to_screen_name,in_reply_to_screen_name,";
$dataHeaders = "{$dataHeaders}" . "binary_in_reply_to_status_id,in_reply_to_status_id,";
$dataHeaders = "{$dataHeaders}" . "place,possibly_sensitive,";
$dataHeaders = "{$dataHeaders}" . "user_id_str,username,user_created_at,user_statuses_count,user_followers_count,user_following_count,user_list_count,user_contributors_enabled,user_geo_enabled,user_protected,user_verified,user_default_profile,user_default_profile_image\n";

// Caution: overwrites any current data in the file
if (file_put_contents($outputFile, $dataHeaders) === FALSE)
{
	// FALSE indicates that an error occurred during the fwrite operation
}

$url = 'https://stream.twitter.com/1/statuses/filter.json';
$tmhOAuth->streaming_request('POST', $url, $params, 'my_streaming_callback');

// Sort the hashtag frequency analysis array for output purposes
echo "Streaming complete. Performing all end of script processing.\n";
if (arsort($hashtagFrequencies) === FALSE)
{
	// FALSE indicates that the arsort function was unsuccessful
}
// Print a list of the 20 most popular hashtags and their frequencies to the specified file
$hashtagFreqHumanReadable = print_r(array_slice($hashtagFrequencies, 0, 20, TRUE), TRUE);
if (file_put_contents($hashtagAnalysisFile, "{$hashtagFreqHumanReadable}" . "\n", FILE_APPEND) === FALSE)
{
	// FALSE indicates that an error occurred during the fwrite operation
}

// Change directories back to the calling directory
chdir("..");

?>
