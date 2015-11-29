#!/bin/bash

# Authors: Farhan Hormasji and Bonnie Reiff
# CSE 881 Project, Fall 2015
# collectData.sh: TODO Description

end=$((SECONDS+14400)) # 4 hour time limit on the script

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

# Run the program until it reaches the time limit
# (while loop prevents any data collection from beginning after the time limit)
while [ $SECONDS -lt $end ]; do

	echo "Start time of data collection program: "$((SECONDS)) # DEBUG: data collection time statistics

	# Call the PHP program to stream 18,000 tweets and output them to a data_collection file
	# Provide the directory as an argument so the program knows where to put the output files
	php streamTweets.php $directoryName $searchIDStringsNameStorage $hashtagAnalysisFilename
	
	echo "End time of data collection program: "$((SECONDS)) # DEBUG: data collection time statistics

	# Pause the program for 16 minutes
	sleep 16m
	
done

echo "Pausing for 6 hours. 6 hour SearchTwitter check to be done afterwards."
sleep 6h # 6 hours after the streaming
php searchTwitter.php $directoryName

for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_6hr_"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done

echo "Pausing for 6 hours. 12 hour SearchTwitter check to be done afterwards."
sleep 6h # 12 hours after the streaming
php searchTwitter.php $directoryName

for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_8hr_"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done

echo "Pausing for 6 hours. 18 hour SearchTwitter check to be done afterwards."
sleep 6h # 18 hours after the streaming
php searchTwitter.php $directoryName

for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_18hr_"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done

echo "Pausing for 6 hours. 24 hour SearchTwitter check to be done afterwards."
sleep 6h # 24 hours after the streaming
php searchTwitter.php $directoryName

for searchTwitterOutputFile in search_API_output*UTC.csv; do
	# echo $searchTwitterOutputFile # DEBUG: print all filenames affected by this for loop
	filenameLength=${#searchTwitterOutputFile}
	extension=${searchTwitterOutputFile:(-4)}
	filenameNoExtension=${searchTwitterOutputFile:0:`expr $filenameLength - 4`}
	# echo $filenameNoExtension # DEBUG: check that the expression above evaluated correctly
	newFilename=$filenameNoExtension"_24hr_"$extension
	# echo $newFilename # DEBUG: print the new filename to the console
	mv $searchTwitterOutputFile $newFilename # Change the name of the output file to reflect the search time
done

