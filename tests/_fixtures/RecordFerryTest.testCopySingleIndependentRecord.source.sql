DROP TABLE IF EXISTS `list`;
CREATE TABLE `list` (
	id INTEGER NOT NULL AUTO_INCREMENT,
	title VARCHAR(255) NOT NULL,
	created DATETIME NOT NULL,
	content TEXT,
	PRIMARY KEY (id)
) ENGINE=InnoDB;
