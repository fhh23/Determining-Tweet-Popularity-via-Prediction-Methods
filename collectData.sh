#!/bin/bash

# Authors: Farhan Hormasji and Bonnie Reiff
# CSE 881 Fall 2015 Term Project
# collectData.sh: TODO Description

end=$((SECONDS+7200)) # 2 hour time limit on the script

# Create a directory for the streaming session
directoryPrefix=streaming_session_
currentTime=$(date "+%Y_%m_%d-%H%M%S")
directoryName=$directoryPrefix$currentTime"ETC"
# echo $directoryName # DEBUG: print the directory name
mkdir $directoryName
# Set the correct permissions on the directory
chmod 664 $directoryName

while [ $SECONDS -lt $end ]; do

	# Call the PHP program to stream 18,000 tweets and output them to a data_collection file
	# Provide the directory as an argument so the program knows where to put the output files
	php streamTweets.php $(directoryName)

	# Pause the program for 16 minutes
	sleep 16m
	
done

