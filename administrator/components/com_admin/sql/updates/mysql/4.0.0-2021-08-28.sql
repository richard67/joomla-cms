--
-- Delete the package extension for com_search if there are no other com_search extensions
--
DELETE FROM `#__extensions`
 WHERE `type` = 'package' AND `element` = 'pkg_search'
   AND (SELECT COUNT(a.`extension_id`)
          FROM `#__extensions` a
         WHERE (a.`type` = 'component' AND a.`element` = 'com_search')
            OR (a.`type` = 'module' AND a.`element` = 'mod_search' AND a.`client_id` = 0)
            OR (a.`type` = 'plugin' AND a.`element` IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags') AND a.`folder` = 'search')
       ) = 0;
