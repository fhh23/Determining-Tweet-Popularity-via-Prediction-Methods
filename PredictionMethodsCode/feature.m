%Read in csv file
filename = '12.7_1/training5.csv';
all_data = csvread(filename, 1, 1);

%Store classification and training data
classification = all_data(:,size(all_data,2)-2);
training = all_data(:,1:size(all_data,2)-5);

%Run SFS, adding features and trying to minimize Error Detection Rate
fs = sequentialfs(@svmwrapper, training, classification,'cv',3,'nfeatures',4)