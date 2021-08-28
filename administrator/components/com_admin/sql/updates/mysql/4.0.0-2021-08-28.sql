--
-- Delete the com_search package extension including its update site if no other
-- com_search extensions exist
--
DELETE FROM `#__update_sites_extensions`
 WHERE `update_site_id` IN (SELECT `update_site_id` FROM `#__update_sites` WHERE `name` = 'Search Update Site')
   AND `extension_id` IN (SELECT `extension_id` FROM `#__extensions` WHERE `element` = 'pkg_search' AND `type` = 'package')
   AND (SELECT COUNT(a.`extension_id`)
          FROM `#__extensions` a
         WHERE (a.`type` = 'component' AND a.`element` = 'com_search')
            OR (a.`type` = 'module' AND a.`element` = 'mod_search' AND a.`client_id` = 0)
            OR (a.`type` = 'plugin' AND a.`element` IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags') AND a.`folder` = 'search')
       ) = 0;

DELETE FROM `#__update_sites`
 WHERE `name` = 'Search Update Site'
   AND (SELECT COUNT(a.`extension_id`)
          FROM `#__extensions` a
         WHERE (a.`type` = 'component' AND a.`element` = 'com_search')
            OR (a.`type` = 'module' AND a.`element` = 'mod_search' AND a.`client_id` = 0)
            OR (a.`type` = 'plugin' AND a.`element` IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags') AND a.`folder` = 'search')
       ) = 0;

DELETE FROM `#__extensions`
 WHERE `type` = 'package' AND `element` = 'pkg_search'
   AND (SELECT COUNT(a.`extension_id`)
          FROM `#__extensions` a
         WHERE (a.`type` = 'component' AND a.`element` = 'com_search')
            OR (a.`type` = 'module' AND a.`element` = 'mod_search' AND a.`client_id` = 0)
            OR (a.`type` = 'plugin' AND a.`element` IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags') AND a.`folder` = 'search')
       ) = 0;
