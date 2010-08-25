DROP TABLE IF EXISTS `comment`;
DROP TABLE IF EXISTS `post`;
DROP TABLE IF EXISTS `bookmark`;
DROP TABLE IF EXISTS `user`;


CREATE TABLE  `user` (
	id INTEGER NOT NULL AUTO_INCREMENT,
	username VARCHAR(255) NOT NULL,
	password_hash VARCHAR(255),
	password_salt VARCHAR(255),
	created DATETIME NOT NULL,
	UNIQUE KEY (username),
	PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE `bookmark` (
	id INTEGER NOT NULL AUTO_INCREMENT,
	user_id INTEGER NOT NULL,
	uri VARCHAR(255) NOT NULL,
	title VARCHAR(255),
	PRIMARY KEY (id),
	CONSTRAINT `fk_bookmark_user_id` FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `post` (
	id INTEGER NOT NULL AUTO_INCREMENT,
	user_id INTEGER NOT NULL,
	title VARCHAR(255) NOT NULL,
	body TEXT NOT NULL,
	created DATETIME NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT `fk_comment_user_id` FOREIGN KEY (`user_id`)
		REFERENCES `user` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `comment` (
	id INTEGER NOT NULL AUTO_INCREMENT,
	post_id INTEGER NOT NULL,
	body TEXT,
	created DATETIME NOT NULL,
	PRIMARY KEY (id),
	CONSTRAINT `fk_comment_post_id` FOREIGN KEY (`post_id`)
		REFERENCES `post` (`id`)
		ON UPDATE CASCADE
		ON DELETE CASCADE
) ENGINE=InnoDB;

