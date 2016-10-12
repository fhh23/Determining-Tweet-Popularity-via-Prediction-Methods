#A Model for Determining Tweet Popularity via Prediction Methods #
Project Members: Farhan Hormasji and Bonnie Reiff

## File Contents ##
1. Datasets
	1. large_dataset.csv: This is the dataset referred to as the "Common Keyword" dataset in the paper. It contains 23,068 tweets and is generating by a streaming request using common English keywords for tracking. Only tweets that are not already a Retweet and that are posted by a user with more than 500 followers are collected.
	2. filtered_dataset.csv: This is a filtered version of large_dataset.csv, containing a total of 3,644 Tweets.
	3. hashtag_dataset.csv: This is the dataset referred to as the "Trending Keyword" dataset in the paper. It contains 1,720 tweets and is generating by a streaming request using trending keywords obtained from the Twitter homepage at the beginning of the data collection session. Only tweets that are not already a Retweet and that are posted by a user with more than 2000 followers are collected.
	
2. DataCollectionCode
	1. streamTweets.php: The streamTweets program posts a streaming request via the "statuses/filter" Public Stream API, which returns public statuses that match one or more filter predicates. The program subsequently processes the Tweets returned from the request and creates a CSV file with the chosen attributes for each Tweet. Several other files to be used during the search portion of the data collection are also created and placed in the appropriate directory structure.
	2. searchTwitter.php: The searchTwitter program retrieves data for each specified Tweet ID using the "statuses/lookup" Search API. For each returned Tweet, the program identifies the Retweet and Favorite counts and outputs the data to a CSV file.
	3. collectData.php: Performs all automatic, timed interactions with the Twitter APIs for a user defined number of short data runs. Each data run consists of streaming tweets for 15 minutes and subsequently searching for each Tweet at 10 minutes, 30 minutes, and 60 minutes afterwards to report on the new number of Retweets and Favorites.
	(Additional files required: auth.token, cacert.pem, tmhOAuth.php, tmhUtilities.php)

3. PredictionMethodsCode
	1. classifier.m: This program trains an SVM classifier using the specified features, and reports a Correct Detection Rate, Error Detection Rate, and confusion matrix. The only parameters to edit in this file are the filename and columns to use as features.
	2. feature.m: This program runs SFS on a dataset, using svmwrapper.m as the criteria function to minimize. The only parameters to edit in this file are the filename and feature set to select from.
	
	
## Running the programs ##
1. Data Collection Programs: A single data collection run (15 minute data collection followed by 10 minute, 30 minute, and 1 hour searches) can be performed by calling "./collectData" from any BASH command shell. Note that the "numIter" variable in the file should first be changed to have a value of "1". In order for the PHP to execute properly, there are two requirements: 
	1. System requires PHP 5.x and CURL with OpenSSL enabled
	2. Folder structure requires the Twitter PHP libraries (cacert.pem, tmhOAuth.php, and tmhUtilities.php), the application authentication information (auth.token), the PHP programs, and the shell script to be in the same directory. The auth.token file is not provided as this is specific to the team Twitter login information used for the project.

2. Classification Program: The classification program requires a minimum Matlab version of R2011a. There is no required folder structure as long as the appropriate path to the data file is provided in the code.


## Abstract ##
With the spike in popularity of social media sites over the past 10 years, it is important for some to leverage these sites in order to communicate information to a large user-base. This project focuses on the Twitter social networking service and attempts to answer two questions. The first goal of the project is to determine the features available through the public Twitter API that contribute the most to a particular Tweet’s popularity. Secondly, the project aims to expand on the knowledge gained from the first objective to determine whether the contributing features can determine whether a Tweet will be “popular” after a designated period of time, where popularity is defined by the number of times a Tweet has been “Favorited.” The project uses a stream of Tweets collected using the Twitter Public Stream API. After labeling the data as “popular” or “not popular”, the project then uses sequential feature selection and a binary support vector machine (SVM) classifier with k-fold cross validation to achieve the two objectives. The analysis reveals that if you know the number of URLs in a Tweet, the favorite count of a Tweet after an hour, and if the Tweet under observation quoted another Tweet, then you have approximately 97% chance of correctly predicting if a Tweet will be popular or not using our trained SVM model.

