ALTER TABLE `#__assets` MODIFY `name` varchar(180) NOT NULL;
ALTER TABLE `#__assets` MODIFY `title` varchar(255) NOT NULL;
ALTER TABLE `#__categories` MODIFY `extension` varchar(100) DEFAULT '' NOT NULL;
