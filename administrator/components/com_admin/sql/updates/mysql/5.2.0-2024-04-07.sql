ALTER TABLE `#__banners` DROP INDEX `idx_metakey_prefix` /** CAN FAIL **/;
ALTER TABLE `#__banners` ADD INDEX `idx_metakey_prefix_v2` (`metakey_prefix`) /** CAN FAIL **/;

ALTER TABLE `#__banner_clients` DROP INDEX `idx_metakey_prefix` /** CAN FAIL **/;
ALTER TABLE `#__banner_clients` ADD INDEX `idx_metakey_prefix_v2` (`metakey_prefix`) /** CAN FAIL **/;

ALTER TABLE `#__categories` DROP INDEX `idx_alias` /** CAN FAIL **/;
ALTER TABLE `#__categories` ADD INDEX `idx_alias_v2` (`alias`) /** CAN FAIL **/;

ALTER TABLE `#__categories` DROP INDEX `idx_path` /** CAN FAIL **/;
ALTER TABLE `#__categories` ADD INDEX `idx_path_v2` (`path`) /** CAN FAIL **/;

ALTER TABLE `#__content` DROP INDEX `idx_alias` /** CAN FAIL **/;
ALTER TABLE `#__content` ADD INDEX `idx_alias_v2` (`alias`) /** CAN FAIL **/;

ALTER TABLE `#__content_types` DROP INDEX `idx_alias` /** CAN FAIL **/;
ALTER TABLE `#__content_types` ADD INDEX `idx_alias_v2` (`type_alias`) /** CAN FAIL **/;

ALTER TABLE `#__fields` DROP INDEX `idx_context` /** CAN FAIL **/;
ALTER TABLE `#__fields` ADD INDEX `idx_context_v2` (`context`) /** CAN FAIL **/;

ALTER TABLE `#__fields_groups` DROP INDEX `idx_context` /** CAN FAIL **/;
ALTER TABLE `#__fields_groups` ADD INDEX `idx_context_v2` (`context`) /** CAN FAIL **/;

ALTER TABLE `#__fields_values` DROP INDEX `idx_item_id` /** CAN FAIL **/;
ALTER TABLE `#__fields_values` ADD INDEX `idx_item_id_v2` (`item_id`) /** CAN FAIL **/;

ALTER TABLE `#__finder_links` DROP INDEX `idx_title` /** CAN FAIL **/;
ALTER TABLE `#__finder_links` ADD INDEX `idx_title_v2` (`title`) /** CAN FAIL **/;

ALTER TABLE `#__finder_links` DROP INDEX `idx_url` /** CAN FAIL **/;
ALTER TABLE `#__finder_links` ADD INDEX `idx_url_v2` (`url`) /** CAN FAIL **/;

ALTER TABLE `#__finder_logging` DROP INDEX `searchterm` /** CAN FAIL **/;
ALTER TABLE `#__finder_logging` ADD INDEX `searchterm_v2` (`searchterm`) /** CAN FAIL **/;

ALTER TABLE `#__finder_taxonomy` DROP INDEX `idx_alias` /** CAN FAIL **/;
ALTER TABLE `#__finder_taxonomy` ADD INDEX `idx_alias_v2` (`alias`) /** CAN FAIL **/;

ALTER TABLE `#__finder_taxonomy` DROP INDEX `idx_path` /** CAN FAIL **/;
ALTER TABLE `#__finder_taxonomy` ADD INDEX `idx_path_v2` (`path`) /** CAN FAIL **/;

ALTER TABLE `#__guidedtours` DROP INDEX `idx_uid` /** CAN FAIL **/;
ALTER TABLE `#__guidedtours` ADD INDEX `idx_uid_v2` (`uid`) /** CAN FAIL **/;

ALTER TABLE `#__menu` DROP INDEX `idx_alias` /** CAN FAIL **/;
ALTER TABLE `#__menu` ADD INDEX `idx_alias_v2` (`alias`) /** CAN FAIL **/;

ALTER TABLE `#__menu` DROP INDEX `idx_client_id_parent_id_alias_language` /** CAN FAIL **/;
ALTER TABLE `#__menu` ADD INDEX `idx_client_id_parent_id_alias_language_v2` (`client_id`,`parent_id`,`alias`,`language`) /** CAN FAIL **/;

ALTER TABLE `#__menu` DROP INDEX `idx_path` /** CAN FAIL **/;
ALTER TABLE `#__menu` ADD INDEX `idx_path_v2` (`path`(768)) /** CAN FAIL **/;

ALTER TABLE `#__redirect_links` DROP INDEX `idx_old_url` /** CAN FAIL **/;
ALTER TABLE `#__redirect_links` ADD INDEX `idx_old_url_v2` (`old_url`(768)) /** CAN FAIL **/;

ALTER TABLE `#__tags` DROP INDEX `idx_alias` /** CAN FAIL **/;
ALTER TABLE `#__tags` ADD INDEX `idx_alias_v2` (`alias`) /** CAN FAIL **/;

ALTER TABLE `#__tags` DROP INDEX `idx_path` /** CAN FAIL **/;
ALTER TABLE `#__tags` ADD INDEX `idx_path_v2` (`path`) /** CAN FAIL **/;

ALTER TABLE `#__ucm_content` DROP INDEX `idx_alias` /** CAN FAIL **/;
ALTER TABLE `#__ucm_content` ADD INDEX `idx_alias_v2` (`core_alias`) /** CAN FAIL **/;

ALTER TABLE `#__ucm_content` DROP INDEX `idx_content_type` /** CAN FAIL **/;
ALTER TABLE `#__ucm_content` ADD INDEX `idx_content_type_v2` (`core_type_alias`) /** CAN FAIL **/;

ALTER TABLE `#__ucm_content` DROP INDEX `idx_title` /** CAN FAIL **/;
ALTER TABLE `#__ucm_content` ADD INDEX `idx_title_v2` (`core_title`) /** CAN FAIL **/;

ALTER TABLE `#__users` DROP INDEX `idx_name` /** CAN FAIL **/;
ALTER TABLE `#__users` ADD INDEX `idx_name_v2` (`name`) /** CAN FAIL **/;

ALTER TABLE `#__user_keys` DROP INDEX `series` /** CAN FAIL **/;
ALTER TABLE `#__user_keys` MODIFY `series` varchar(255) NOT NULL;
ALTER TABLE `#__user_keys` ADD UNIQUE INDEX `series_v2` (`series`) /** CAN FAIL **/;

ALTER TABLE `#__user_keys` DROP INDEX `user_id` /** CAN FAIL **/;
ALTER TABLE `#__user_keys` MODIFY `user_id` varchar(255) NOT NULL;
ALTER TABLE `#__user_keys` ADD INDEX `user_id_v2` (`user_id`) /** CAN FAIL **/;

ALTER TABLE `#__webauthn_credentials` DROP INDEX `user_id` /** CAN FAIL **/;
ALTER TABLE `#__webauthn_credentials` ADD INDEX `idx_user_id` (`user_id`) /** CAN FAIL **/;

ALTER TABLE `#__workflows` DROP INDEX `idx_title` /** CAN FAIL **/;
ALTER TABLE `#__workflows` ADD INDEX `idx_title_v2` (`title`) /** CAN FAIL **/;

ALTER TABLE `#__workflow_stages` DROP INDEX `idx_title` /** CAN FAIL **/;
ALTER TABLE `#__workflow_stages` ADD INDEX `idx_title_v2` (`title`) /** CAN FAIL **/;

ALTER TABLE `#__workflow_transitions` DROP INDEX `idx_title` /** CAN FAIL **/;
ALTER TABLE `#__workflow_transitions` ADD INDEX `idx_title_v2` (`title`) /** CAN FAIL **/;
