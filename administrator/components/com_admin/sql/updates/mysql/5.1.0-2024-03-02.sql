ALTER TABLE `#__fields_values` DROP INDEX `idx_item_id` /** CAN FAIL **/;
ALTER TABLE `#__fields_values` ADD INDEX `idx_item_id_2` (`item_id`) /** CAN FAIL **/;
ALTER TABLE `#__fields_values` ADD INDEX `idx_field_id_item_id` (`field_id`, `item_id`) /** CAN FAIL **/;
