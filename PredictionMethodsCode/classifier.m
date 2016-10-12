%Read in file
filename = '12.7_1/training5.csv';
all_data = csvread(filename, 1, 1);

%Get indices for k-fold cross validation
total_classification = all_data(:,size(all_data,2)-2);
k = 3;
cvFolds = crossvalind('Kfold', total_classification, k);
cp = classperf(total_classification);

for i = 1:k
    testIdx = (cvFolds == i);
    trainIdx = ~testIdx;
    
    training = all_data(trainIdx,1:size(all_data,2)-5);
    classification = total_classification(trainIdx);
	
    %Run SVM to train classification model 
    options = statset('MaxIter', 150000);
    svmModel = svmtrain(training, classification, 'Options', options);%);
    
	%Classify the test data based on the SVM model and return the Error Detection Rate
    test = all_data(testIdx,1:size(all_data,2)-5);
    pred = svmclassify(svmModel, test);
    cp = classperf(cp, pred, testIdx);
end

%Return confusion matrix and correct and error detection rate
cp.CountingMatrix
cp.CorrectRate
cp.ErrorRate