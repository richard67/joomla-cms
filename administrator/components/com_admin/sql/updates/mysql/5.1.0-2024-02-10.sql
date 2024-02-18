ALTER TABLE `#__guidedtour_steps` ADD COLUMN `params` text NOT NULL /** CAN FAIL **/;
UPDATE `#__guidedtour_steps` SET `params` = '{}' WHERE `params` = '';
