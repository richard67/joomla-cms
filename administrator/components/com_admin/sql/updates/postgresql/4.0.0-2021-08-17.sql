--
-- Insert a package extension for com_search extensions, but only if
-- there is not yet such package extension and if there are some
-- other extenions present which belong to com_search
--
INSERT INTO "#__extensions" ("name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state")
SELECT 'search', 'package', 'pkg_search', '', 0, 1, 1, 0, '', '', '', 0, NULL, 0, 0
 WHERE (SELECT COUNT(a."extension_id") FROM "#__extensions" a WHERE a."type" = 'package' AND a."element" = 'pkg_search') = 0
   AND (SELECT COUNT(b."extension_id")
          FROM "#__extensions" b
         WHERE (b."type" = 'component' AND b."element" = 'com_search')
            OR (b."type" = 'module' AND b."element" = 'mod_search' AND b."client_id" = 0)
            OR (b."type" = 'plugin' AND b."element" IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags') AND b."folder" = 'search')
       ) > 0;

--
-- Set the package_id of existing com_search extensions to the ID of the
-- package extension if that exists
--
UPDATE "#__extensions"
   SET "package_id" = b."extension_id"
  FROM (SELECT "extension_id" FROM "#__extensions" WHERE "type" = 'package' AND "element" = 'pkg_search') AS b
 WHERE ("type" = 'component' AND "element" = 'com_search')
    OR ("type" = 'module' AND "element" = 'mod_search' AND "client_id" = 0)
    OR ("type" = 'plugin' AND "element" IN ('categories', 'contacts', 'content', 'newsfeeds', 'tags') AND "folder" = 'search');

--
-- Create an update site for the com_search package if that package exists and
-- the update site doesn't already exist
--
INSERT INTO "#__update_sites" ("name", "type", "location", "enabled")
SELECT 'Search Update Site', 'extension', 'https://raw.githubusercontent.com/joomla-extensions/search/main/manifest.xml', 1
  FROM "#__extensions" e
 WHERE e."type" = 'package' AND e."element" = 'pkg_search'
   AND (SELECT COUNT(u."name") FROM "#__update_sites" u WHERE u."location" = 'https://raw.githubusercontent.com/joomla-extensions/search/main/manifest.xml') = 0;

--
-- Insert a cross reference for update site and package extension if both exist
--
INSERT INTO "#__update_sites_extensions" ("update_site_id", "extension_id") VALUES
((SELECT "update_site_id" FROM "#__update_sites" WHERE "location" = 'https://raw.githubusercontent.com/joomla-extensions/search/main/manifest.xml'), (SELECT "extension_id" FROM "#__extensions" WHERE "element" = 'pkg_search' AND "type" = 'package'));
