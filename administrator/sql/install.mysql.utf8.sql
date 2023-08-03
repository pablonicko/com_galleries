CREATE TABLE IF NOT EXISTS `#__galleries` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100)  NOT NULL ,
	`user_id` INT(11)  NOT NULL ,
	`state` TINYINT(1)  NULL  DEFAULT 1,
	`ordering` INT(11)  NULL  DEFAULT 0,
	`checked_out` INT(11)  UNSIGNED,
	`checked_out_time` DATETIME NULL  DEFAULT NULL ,
	`created_by` INT(11)  NULL  DEFAULT 0,
	`modified_by` INT(11)  NULL  DEFAULT 0,
	PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__orders` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100)  NOT NULL ,
	`user_id` INT(11)  NOT NULL ,
	`gallery_id` INT(11)  NOT NULL  DEFAULT 0,
	`status` TINYINT(1)  NULL  DEFAULT 0,
	`state` TINYINT(1)  NULL  DEFAULT 1,
	`ordering` INT(11)  NULL  DEFAULT 0,
	`checked_out` INT(11)  UNSIGNED,
	`checked_out_time` DATETIME NULL  DEFAULT NULL ,
	`created_by` INT(11)  NULL  DEFAULT 0,
	`modified_by` INT(11)  NULL  DEFAULT 0,
	`createdate` DATETIME NULL  DEFAULT NULL ,
	PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__images` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(100) NOT NULL,
	`image_url` VARCHAR(255) NOT NULL,
	`createDate` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
) DEFAULT COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `#__gallery_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `gallery_id` INT(11) NOT NULL,
  `image_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `gallery_id_idx` (`gallery_id` ASC),
  INDEX `image_id_idx` (`image_id` ASC),
  CONSTRAINT `gallery_id`
    FOREIGN KEY (`gallery_id`)
    REFERENCES `#__galleries` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `gallery_image_id`
    FOREIGN KEY (`image_id`)
    REFERENCES `#__images` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) DEFAULT COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__orders_images` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `image_id` INT(11) NOT NULL,
  `quantity` TINYINT(4) NOT NULL DEFAULT 1,
  `createDate` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id_idx` (`order_id` ASC),
  INDEX `image_id_idx` (`image_id` ASC),
  CONSTRAINT `order_id`
    FOREIGN KEY (`order_id`)
    REFERENCES `#__orders` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `order_image_id`
    FOREIGN KEY (`image_id`)
    REFERENCES `#__images` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) DEFAULT COLLATE=utf8_unicode_ci;

