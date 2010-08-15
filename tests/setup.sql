CREATE DATABASE recordferry_test_source;
CREATE DATABASE recordferry_test_target;

GRANT ALL ON recordferry_test_source.* TO 'recordferry_test'@'localhost' IDENTIFIED BY 'recordferry_test';
GRANT ALL ON recordferry_test_target.* TO 'recordferry_test'@'localhost' IDENTIFIED BY 'recordferry_test';
FLUSH PRIVILEGES;

