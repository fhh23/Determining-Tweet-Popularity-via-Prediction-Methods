#!/bin/bash

# Authors: Farhan Hormasji and Bonnie Reiff
# CSE 881 Project, Fall 2015
# collectData_shortRun.sh: Performs all automatic and timed interaction with the Twitter APIs 
#        for a short data run. Obtains a stream of 15,000 tweets using common popular words
#        as keywords. Subsequenty searches for each Tweet at 10 minutes, 30 minutes, and
#        60 minutes afterwards and reports on the new number of retweets and favorites.

currentTime=$(date "+%Y-%m-%d-%H%M%S")
currentTime=$currentTime"ETC"

# Create a directory for the streaming session
directoryPrefix=streaming_session_
directoryName=$directoryPrefix$currentTime
# echo $directoryName # DEBUG: print the directory name
mkdir $directoryName
# Set the correct permissions on the directory
chmod 775 $directoryName

# Create a filename to store the names of the ID strings for the search program
searchIDStringsNameStoragePrefix=search_input_
searchIDStringsNameStorage=$searchIDStringsNameStoragePrefix$currentTime".txt"
# echo $searchIDStringsNameStorage # DEBUG: print the filename

hashtagAnalysisFilename=hashtagFrequencyAnalysis.txt

# Call the PHP program to stream 15,000 tweets and output them to a data_collection file
# Provide the directory as an argument so the program knows where to put the output files
php streamTweets.php $directoryName $searchIDStringsNameStorage $hashtagAnalysisFilename
echo "Data collection complete at " $(date "+%m-%d-%H%M%S") "Starting searches in 10 minutes..."

sleep 10m # Pause for 10 minutes after the streaming

echo "[ " $(date "+%H:%M:%S") " ] Begin 10 minute search."
# Start the timer until the next search (20 minute timer)
endDelayExecute30Min=$((SECONDS+1200))

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_10min"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 10 minute search."

# Delay until the timer runs out
while [ $SECONDS -lt $endDelayExecute30Min ]; do
:
done

echo "[ " $(date "+%H:%M:%S") " ] Begin 30 minute search."
# Start the timer until the next search (30 minute timer)
endDelayExecute1Hr=$((SECONDS+1800))

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_30min"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 30 minute search."

# Delay until the timer runs out
while [ $SECONDS -lt $endDelayExecute1Hr ]; do
:
done

echo "[ " $(date "+%H:%M:%S") " ] Begin 1 hour search."

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_1hr"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 1 hour search."