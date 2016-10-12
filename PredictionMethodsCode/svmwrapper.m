function err = svmwrapper(xTrain, yTrain, xTest, yTest)
	%Run SVM to train classification model 
    options = statset('MaxIter', 150000);
    model = svmtrain(xTrain, yTrain, 'Options', options);
	
	%Classify the test data based on the SVM model and return the Error Detection Rate
    pred = svmclassify(model, xTest);
    cp = classperf(yTest, pred);
    err = cp.ErrorRate;
end