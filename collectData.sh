#!/bin/bash

# Authors: Farhan Hormasji and Bonnie Reiff
# CSE 881 Project, Fall 2015
# collectData.sh: Performs all automatic and timed interaction with the Twitter APIs.
#        Runs a 6 hour stream of tweets using common popular words as keywords, which produces
#        one data file of output per 15,000 tweets. Subsequenty searches for each Tweet
#        and reports on the new number of retweets and favorites.

endDC=$((SECONDS+21600)) # 6 hour time limit on the data collection

currentTime=$(date "+%Y-%m-%d-%H%M%S")
currentTime=$currentTime"EST"

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

# Run the program until it reaches the time limit
# (while loop prevents any data collection from beginning after the time limit)
while [ $SECONDS -lt $endDC ]; do

	echo "Start time of data collection program: "$((SECONDS)) # DEBUG: data collection time statistics

	# Call the PHP program to stream 15,000 tweets and output them to a data_collection file
	# Provide the directory as an argument so the program knows where to put the output files
	php streamTweets.php $directoryName $searchIDStringsNameStorage $hashtagAnalysisFilename
	
	echo "End time of data collection program: "$((SECONDS)) # DEBUG: data collection time statistics

	# Pause the program for 16 minutes
	sleep 16m
	
done
echo "Data collection complete at " $(date "+%m-%d-%H%M%S") "Starting searches in 3 hours..."

sleep 3h # Pause for 3 hours after the streaming

echo "[ " $(date "+%H:%M:%S") " ] Begin 3 hour search."
# Start the next three hour timer until the next search
endDelayExecuteSixHr=$((SECONDS+10800))

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_3hr"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 3 hour search."

# Delay until the timer runs out
while [ $SECONDS -lt $endDelayExecuteSixHr ]; do
:
done

echo "[ " $(date "+%H:%M:%S") " ] Begin 6 hour search."
# Start the next three hour timer until the next search
endDelayExecuteNineHr=$((SECONDS+10800))

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_6hr"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 6 hour search."

# Delay until the timer runs out
while [ $SECONDS -lt $endDelayExecuteNineHr ]; do
:
done

echo "[ " $(date "+%H:%M:%S") " ] Begin 9 hour search."
# Start the next three hour timer until the next search
endDelayExecuteTwelveHr=$((SECONDS+10800))

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_9hr"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 9 hour search."

# Delay until the timer runs out
while [ $SECONDS -lt $endDelayExecuteTwelveHr ]; do
:
done

echo "[ " $(date "+%H:%M:%S") " ] Begin 12 hour search."
# Start the next three hour timer until the next search
endDelayExecuteFifteenHr=$((SECONDS+10800))

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_12hr"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 12 hour search."

# Delay until the timer runs out
while [ $SECONDS -lt $endDelayExecuteFifteenHr ]; do
:
done

echo "[ " $(date "+%H:%M:%S") " ] Begin 15 hour search."
# Start the next three hour timer until the next search
endDelayExecuteEighteenHr=$((SECONDS+10800))

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_15hr"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 15 hour search."

# Delay until the timer runs out
while [ $SECONDS -lt $endDelayExecuteEighteenHr ]; do
:
done

echo "[ " $(date "+%H:%M:%S") " ] Begin 18 hour search."
# Start the next three hour timer until the next search
endDelayExecuteTwentyOneHr=$((SECONDS+10800))

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_18hr"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 18 hour search."

# Delay until the timer runs out
while [ $SECONDS -lt $endDelayExecuteTwentyOneHr ]; do
:
done

echo "[ " $(date "+%H:%M:%S") " ] Begin 21 hour search."
# Start the next three hour timer until the next search
endDelayExecuteTwentyFourHr=$((SECONDS+10800))

php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_21hr"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 21 hour search."

# Delay until the timer runs out
while [ $SECONDS -lt $endDelayExecuteTwentyFourHr ]; do
:
done

echo "[ " $(date "+%H:%M:%S") " ] Begin 24 hour search."
php searchTwitter.php $directoryName
cd $directoryName
for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_24hr"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done
cd ..
echo "[ " $(date "+%H:%M:%S") " ] End 24 hour search."