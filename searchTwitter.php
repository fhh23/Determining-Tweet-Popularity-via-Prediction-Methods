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
// Sample IDs to search (found from data streaming)
$params['id'] = '666244593227288576,666244593176805377';

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

$url = "https://api.twitter.com/1.1/statuses/lookup.json";
$tmhOAuth->request('GET', $url, $params);

if ($tmhOAuth->response['code'] == 200) 
{
	$data = json_decode($tmhOAuth->response['response'], true);
	print_r($data);
    foreach ($data as $tweet) 
	{
    	$date = strtotime($tweet['created_at']);   
        echo $tweet['id_str']."\t". $date."\t".$tweet['text']. "<br />";
    }
	
} 
else 
{
	$data = htmlentities($tmhOAuth->response['response']);
	echo 'There was an error.' . PHP_EOL;
	var_dump($tmhOAuth);
}

?>
