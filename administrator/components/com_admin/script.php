<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Admin\Administrator\Script\DeletedFiles;
use Joomla\Component\Admin\Administrator\Script\DeletedFolders;
use Joomla\Component\Admin\Administrator\Script\RenamedFiles;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Script file of Joomla CMS
 *
 * @since  1.6.4
 */
class JoomlaInstallerScript
{
    /**
     * The Joomla Version we are updating from
     *
     * @var    string
     * @since  3.7
     */
    protected $fromVersion = null;

    /**
     * Callback for collecting errors. Like function(string $context, \Throwable $error){};
     *
     * @var callable
     *
     * @since  4.4.0
     */
    protected $errorCollector;

    /**
     * Set the callback for collecting errors.
     *
     * @param   callable  $callback  The callback Like function(string $context, \Throwable $error){};
     *
     * @return  void
     *
     * @since  4.4.0
     */
    public function setErrorCollector(callable $callback)
    {
        $this->errorCollector = $callback;
    }

    /**
     * Collect errors.
     *
     * @param  string      $context  A context/place where error happened
     * @param  \Throwable  $error    The error that occurred
     *
     * @return  void
     *
     * @since  4.4.0
     */
    protected function collectError(string $context, \Throwable $error)
    {
        // The errorCollector are required
        // However when someone already running the script manually the code may fail.
        if ($this->errorCollector) {
            \call_user_func($this->errorCollector, $context, $error);
        } else {
            Log::add($error->getMessage(), Log::ERROR, 'Update');
        }
    }

    /**
     * Function to act prior to installation process begins
     *
     * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   3.7.0
     */
    public function preflight($action, $installer)
    {
        if ($action === 'update') {
            // Get the version we are updating from
            if (!empty($installer->extension->manifest_cache)) {
                $manifestValues = json_decode($installer->extension->manifest_cache, true);

                if (\array_key_exists('version', $manifestValues)) {
                    $this->fromVersion = $manifestValues['version'];

                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Method to update Joomla!
     *
     * @param   Installer  $installer  The class calling this method
     *
     * @return  void
     */
    public function update($installer)
    {
        // Uninstall extensions before removing their files and folders
        try {
            Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_UNINSTALL_EXTENSIONS'), Log::INFO, 'Update');
            $this->uninstallExtensions();
        } catch (\Throwable $e) {
            $this->collectError('uninstallExtensions', $e);
        }

        // Remove old files
        try {
            Log::add(Text::_('COM_JOOMLAUPDATE_UPDATE_LOG_DELETE_FILES'), Log::INFO, 'Update');
            $this->deleteUnexistingFiles();
        } catch (\Throwable $e) {
            $this->collectError('deleteUnexistingFiles', $e);
        }

        // Further update
        try {
            $this->updateManifestCaches();
            $this->updateDatabase();
            $this->updateAssets($installer);
            $this->clearStatsCache();
        } catch (\Throwable $e) {
            $this->collectError('Further update', $e);
        }

        // Clean cache
        try {
            $this->cleanJoomlaCache();
        } catch (\Throwable $e) {
            $this->collectError('cleanJoomlaCache', $e);
        }
    }

    /**
     * Method to clear our stats plugin cache to ensure we get fresh data on Joomla Update
     *
     * @return  void
     *
     * @since   3.5
     */
    protected function clearStatsCache()
    {
        $db = Factory::getDbo();

        try {
            // Get the params for the stats plugin
            $params = $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('stats'))
            )->loadResult();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }

        $params = json_decode($params, true);

        // Reset the last run parameter
        if (isset($params['lastrun'])) {
            $params['lastrun'] = '';
        }

        $params = json_encode($params);

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('stats'));

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }
    }

    /**
     * Method to update Database
     *
     * @return  void
     */
    protected function updateDatabase()
    {
        if (Factory::getDbo()->getServerType() === 'mysql') {
            $this->updateDatabaseMysql();
        }
    }

    /**
     * Method to update MySQL Database
     *
     * @return  void
     */
    protected function updateDatabaseMysql()
    {
        $db = Factory::getDbo();

        $db->setQuery('SHOW ENGINES');

        try {
            $results = $db->loadObjectList();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }

        foreach ($results as $result) {
            if ($result->Support != 'DEFAULT') {
                continue;
            }

            $db->setQuery('ALTER TABLE #__update_sites_extensions ENGINE = ' . $result->Engine);

            try {
                $db->execute();
            } catch (Exception $e) {
                $this->collectError(__METHOD__, $e);

                return;
            }

            break;
        }
    }

    /**
     * Uninstall extensions and optionally migrate their parameters when
     * updating from a version older than 5.0.1.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    protected function uninstallExtensions()
    {
        // Don't uninstall extensions when not updating from a version older than 5.0.1
        if (empty($this->fromVersion) || version_compare($this->fromVersion, '5.0.1', 'ge')) {
            return true;
        }

        $extensions = [
            /**
             * Define here the extensions to be uninstalled and optionally migrated on update.
             * For each extension, specify an associative array with following elements (key => value):
             * 'type'         => Field `type` in the `#__extensions` table
             * 'element'      => Field `element` in the `#__extensions` table
             * 'folder'       => Field `folder` in the `#__extensions` table
             * 'client_id'    => Field `client_id` in the `#__extensions` table
             * 'pre_function' => Name of an optional migration function to be called before
             *                   uninstalling, `null` if not used.
             */
            ['type' => 'plugin', 'element' => 'demotasks', 'folder' => 'task', 'client_id' => 0, 'pre_function' => null],
            ['type' => 'plugin', 'element' => 'compat', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateCompatPlugin'],
            ['type' => 'plugin', 'element' => 'logrotation', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateLogRotationPlugin'],
            ['type' => 'plugin', 'element' => 'recaptcha', 'folder' => 'captcha', 'client_id' => 0, 'pre_function' => null],
            ['type' => 'plugin', 'element' => 'sessiongc', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateSessionGCPlugin'],
            ['type' => 'plugin', 'element' => 'updatenotification', 'folder' => 'system', 'client_id' => 0, 'pre_function' => 'migrateUpdatenotificationPlugin'],
        ];

        $db = Factory::getDbo();

        foreach ($extensions as $extension) {
            $row = $db->setQuery(
                $db->getQuery(true)
                    ->select('*')
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote($extension['type']))
                    ->where($db->quoteName('element') . ' = ' . $db->quote($extension['element']))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote($extension['folder']))
                    ->where($db->quoteName('client_id') . ' = ' . $db->quote($extension['client_id']))
            )->loadObject();

            // Skip migrating and uninstalling if the extension doesn't exist
            if (!$row) {
                continue;
            }

            // If there is a function for migration to be called before uninstalling, call it
            if ($extension['pre_function'] && method_exists($this, $extension['pre_function'])) {
                $this->{$extension['pre_function']}($row);
            }

            try {
                $db->transactionStart();

                // Unlock and unprotect the plugin so we can uninstall it
                $db->setQuery(
                    $db->getQuery(true)
                        ->update($db->quoteName('#__extensions'))
                        ->set($db->quoteName('locked') . ' = 0')
                        ->set($db->quoteName('protected') . ' = 0')
                        ->where($db->quoteName('extension_id') . ' = :extension_id')
                        ->bind(':extension_id', $row->extension_id, ParameterType::INTEGER)
                )->execute();

                // Uninstall the plugin
                $installer = new Installer();
                $installer->setDatabase($db);
                $installer->uninstall($extension['type'], $row->extension_id);

                $db->transactionCommit();
            } catch (\Exception $e) {
                $db->transactionRollback();
                throw $e;
            }
        }
    }

    /**
     * Migrate plugin parameters of obsolete compat system plugin to compat behaviour plugin
     *
     * @param   \stdClass  $rowOld  Object with the obsolete plugin's record in the `#__extensions` table
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function migrateCompatPlugin($rowOld)
    {
        $db = Factory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = :enabled')
                ->set($db->quoteName('params') . ' = :params')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('compat'))
                ->where($db->quoteName('folder') . ' = ' . $db->quote('behaviour'))
                ->where($db->quoteName('client_id') . ' = 0')
                ->bind(':enabled', $rowOld->enabled, ParameterType::INTEGER)
                ->bind(':params', $rowOld->params)
        )->execute();
    }

    /**
     * This method is for migration for old logrotation system plugin migration to task.
     *
     * @param   \stdClass  $data  Object with the extension's record in the `#__extensions` table
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function migrateLogRotationPlugin($data)
    {
        if (!$data->enabled) {
            return;
        }

        /** @var SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);

        // Get the timeout, as configured in plg_system_logrotation
        $params       = new Registry($data->params);
        $cachetimeout = (int) $params->get('cachetimeout', 30);
        $lastrun      = (int) $params->get('lastrun', time());

        $task = [
            'title'           => 'Rotate Logs',
            'type'            => 'rotation.logs',
            'execution_rules' => [
                'rule-type'     => 'interval-days',
                'interval-days' => $cachetimeout,
                'exec-time'     => gmdate('H:i', $lastrun),
                'exec-day'      => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'logstokeep' => $params->get('logstokeep', 1),
            ],
        ];
        $model->save($task);
    }

    /**
     * This method is for migration for old updatenotification system plugin migration to task.
     *
     * @param   \stdClass  $data  Object with the extension's record in the `#__extensions` table
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function migrateSessionGCPlugin($data)
    {
        if (!$data->enabled) {
            return;
        }

        // Get the plugin parameters
        $params = new Registry($data->params);

        /** @var SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $task  = [
            'title'           => 'Session GC',
            'type'            => 'session.gc',
            'execution_rules' => [
                'rule-type'      => 'interval-hours',
                'interval-hours' => 24,
                'exec-time'      => gmdate('H:i'),
                'exec-day'       => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'enable_session_gc'          => $params->get('enable_session_gc', 1),
                'enable_session_metadata_gc' => $params->get('enable_session_metadata_gc', 1),
            ],
        ];
        $model->save($task);
    }

    /**
     * This method is for migration for old updatenotification system plugin migration to task.
     *
     * @param   \stdClass  $data  Object with the extension's record in the `#__extensions` table
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function migrateUpdatenotificationPlugin($data)
    {
        if (!$data->enabled) {
            return;
        }

        // Get the timeout for Joomla! updates, as configured in com_installer's component parameters
        $component    = ComponentHelper::getComponent('com_installer');
        $paramsc      = $component->getParams();
        $cachetimeout = (int) $paramsc->get('cachetimeout', 6);
        $params       = new Registry($data->params);
        $lastrun      = (int) $params->get('lastrun', time());

        /** @var SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $task  = [
            'title'           => 'Update Notification',
            'type'            => 'update.notification',
            'execution_rules' => [
                'rule-type'      => 'interval-hours',
                'interval-hours' => $cachetimeout,
                'exec-time'      => gmdate('H:i', $lastrun),
                'exec-day'       => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'email'             => $params->get('email', ''),
                'language_override' => $params->get('language_override', ''),
            ],
        ];
        $model->save($task);
    }

    /**
     * Update the manifest caches
     *
     * @return  void
     */
    protected function updateManifestCaches()
    {
        $extensions = ExtensionHelper::getCoreExtensions();

        // Attempt to refresh manifest caches
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__extensions');

        foreach ($extensions as $extension) {
            $query->where(
                'type=' . $db->quote($extension[0])
                . ' AND element=' . $db->quote($extension[1])
                . ' AND folder=' . $db->quote($extension[2])
                . ' AND client_id=' . $extension[3],
                'OR'
            );
        }

        $db->setQuery($query);

        try {
            $extensions = $db->loadObjectList();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return;
        }

        $installer = new Installer();
        $installer->setDatabase($db);

        foreach ($extensions as $extension) {
            if (!$installer->refreshManifestCache($extension->extension_id)) {
                $this->collectError(
                    __METHOD__,
                    new \Exception(sprintf(
                        'Error on updating manifest cache: (type, element, folder, client) = (%s, %s, %s, %s)',
                        $extension->type,
                        $extension->element,
                        $extension->name,
                        $extension->client_id
                    ))
                );
            }
        }
    }

    /**
     * Delete files that should not exist
     *
     * @param bool  $dryRun          If set to true, will not actually delete files, but just report their status for use in CLI
     * @param bool  $suppressOutput   Set to true to suppress echoing any errors, and just return the $status array
     *
     * @return  array
     */
    public function deleteUnexistingFiles($dryRun = false, $suppressOutput = false)
    {
        $status = [
            'files_exist'     => [],
            'folders_exist'   => [],
            'files_deleted'   => [],
            'folders_deleted' => [],
            'files_errors'    => [],
            'folders_errors'  => [],
            'folders_checked' => [],
            'files_checked'   => [],
        ];

        $deletedFiles   = new DeletedFiles();
        $deletedFolders = new DeletedFolders();

        $status['files_checked']   = $deletedFiles->files;
        $status['folders_checked'] = $deletedFolders->folders;

        foreach ($deletedFiles->files as $file) {
            if ($fileExists = is_file(JPATH_ROOT . $file)) {
                $status['files_exist'][] = $file;

                if ($dryRun === false) {
                    if (File::delete(JPATH_ROOT . $file)) {
                        $status['files_deleted'][] = $file;
                    } else {
                        $status['files_errors'][] = sprintf('Error on deleting file or folder %s', $file);
                    }
                }
            }
        }

        foreach ($deletedFolders->folders as $folder) {
            if ($folderExists = Folder::exists(JPATH_ROOT . $folder)) {
                $status['folders_exist'][] = $folder;

                if ($dryRun === false) {
                    if (Folder::delete(JPATH_ROOT . $folder)) {
                        $status['folders_deleted'][] = $folder;
                    } else {
                        $status['folders_errors'][] = sprintf('Error on deleting file or folder %s', $folder);
                    }
                }
            }
        }

        $this->fixFilenameCasing();

        if ($suppressOutput === false && \count($status['folders_errors'])) {
            echo implode('<br>', $status['folders_errors']);
        }

        if ($suppressOutput === false && \count($status['files_errors'])) {
            echo implode('<br>', $status['files_errors']);
        }

        return $status;
    }

    /**
     * Method to create assets for newly installed components
     *
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean
     *
     * @since   3.2
     */
    public function updateAssets($installer)
    {
        // List all components added since 4.0
        $newComponents = [
            // Components to be added here
        ];

        foreach ($newComponents as $component) {
            /** @var \Joomla\CMS\Table\Asset $asset */
            $asset = Table::getInstance('Asset');

            if ($asset->loadByName($component)) {
                continue;
            }

            $asset->name      = $component;
            $asset->parent_id = 1;
            $asset->rules     = '{}';
            $asset->title     = $component;
            $asset->setLocation(1, 'last-child');

            if (!$asset->store()) {
                $this->collectError(__METHOD__, new \Exception($asset->getError(true)));

                // Install failed, roll back changes
                $installer->abort(Text::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $asset->getError(true)));

                return false;
            }
        }

        return true;
    }

    /**
     * This method clean the Joomla Cache using the method `clean` from the com_cache model
     *
     * @return  void
     *
     * @since   3.5.1
     */
    private function cleanJoomlaCache()
    {
        /** @var \Joomla\Component\Cache\Administrator\Model\CacheModel $model */
        $model = Factory::getApplication()->bootComponent('com_cache')->getMVCFactory()
            ->createModel('Cache', 'Administrator', ['ignore_request' => true]);

        // Clean frontend cache
        $model->clean();

        // Clean admin cache
        $model->setState('client_id', 1);
        $model->clean();
    }

    /**
     * Called after any type of action
     *
     * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   4.0.0
     */
    public function postflight($action, $installer)
    {
        if ($action !== 'update') {
            return true;
        }

        if (empty($this->fromVersion) || version_compare($this->fromVersion, '5.0.0', 'ge')) {
            return true;
        }

        // Add here code which shall be executed only when updating from an older version than 5.0.0
        if (!$this->migrateTinymceConfiguration()) {
            return false;
        }

        if (!$this->migrateDeleteActionlogsConfiguration()) {
            return false;
        }

        if (!$this->migratePrivacyconsentConfiguration()) {
            return false;
        }

        $this->setGuidedToursUid();

        // Refresh versionable assets cache.
        Factory::getApplication()->flushAssets();

        return true;
    }

    /**
     * Migrate Deleteactionlogs plugin configuration
     *
     * @return  boolean  True on success
     *
     * @since   5.0.0
     */
    private function migrateDeleteActionlogsConfiguration(): bool
    {
        $db = Factory::getDbo();

        try {
            // Get the ActionLogs system plugin's parameters
            $row = $db->setQuery(
                $db->getQuery(true)
                    ->select([$db->quotename('enabled'), $db->quoteName('params')])
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('actionlogs'))
            )->loadObject();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        // If not existing or disabled there is nothing to migrate
        if (!$row || !$row->enabled) {
            return true;
        }

        $params = new Registry($row->params);

        // If deletion of outdated logs was disabled there is nothing to migrate
        if (!$params->get('logDeletePeriod', 0)) {
            return true;
        }

        /** @var SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $task  = [
            'title'           => 'Delete Action Logs',
            'type'            => 'delete.actionlogs',
            'execution_rules' => [
                'rule-type'      => 'interval-hours',
                'interval-hours' => 24,
                'exec-time'      => gmdate('H:i', $params->get('lastrun', time())),
                'exec-day'       => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'logDeletePeriod' => $params->get('logDeletePeriod', 0),
            ],
        ];

        try {
            $model->save($task);
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        return true;
    }
    /**
     * Migrate privacyconsents system plugin configuration
     *
     * @return  boolean  True on success
     *
     * @since   5.0.0
     */
    private function migratePrivacyconsentConfiguration(): bool
    {
        $db = Factory::getDbo();

        try {
            // Get the PrivacyConsent system plugin's parameters
            $row = $db->setQuery(
                $db->getQuery(true)
                    ->select([$db->quotename('enabled'), $db->quoteName('params')])
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('privacyconsent'))
            )->loadObject();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        // If not existing or disabled there is nothing to migrate
        if (!$row || !$row->enabled) {
            return true;
        }

        $params = new Registry($row->params);

        // If consent expiration was disabled there is nothing to migrate
        if (!$params->get('enabled', 0)) {
            return true;
        }

        /** @var SchedulerComponent $component */
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var TaskModel $model */
        $model = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $task  = [
            'title'           => 'Privacy Consent',
            'type'            => 'privacy.consent',
            'execution_rules' => [
                'rule-type'     => 'interval-days',
                'interval-days' => $params->get('cachetimeout', 30),
                'exec-time'     => gmdate('H:i', $params->get('lastrun', time())),
                'exec-day'      => gmdate('d'),
            ],
            'state'  => 1,
            'params' => [
                'consentexpiration' => $params->get('consentexpiration', 360),
                'remind'            => $params->get('remind', 30),
            ],
        ];

        try {
            $model->save($task);
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        // Refresh versionable assets cache.
        Factory::getApplication()->flushAssets();

        return true;
    }

    /**
     * Migrate TinyMCE editor plugin configuration
     *
     * @return  boolean  True on success
     *
     * @since   5.0.0
     */
    private function migrateTinymceConfiguration(): bool
    {
        $db = Factory::getDbo();

        try {
            // Get the TinyMCE editor plugin's parameters
            $params = $db->setQuery(
                $db->getQuery(true)
                    ->select($db->quoteName('params'))
                    ->from($db->quoteName('#__extensions'))
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('editors'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('tinymce'))
            )->loadResult();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        $params = json_decode($params, true);

        // If there are no toolbars there is nothing to migrate
        if (!isset($params['configuration']['toolbars'])) {
            return true;
        }

        // Each set has its own toolbar configuration
        foreach ($params['configuration']['toolbars'] as $setIdx => $toolbarConfig) {
            // Migrate menu items if there is a menu
            if (isset($toolbarConfig['menu'])) {
                /**
                 * Replace array values with menu item names ("old name" -> "new name"):
                 * "blockformats" -> "blocks"
                 * "fontformats"  -> "fontfamily"
                 * "fontsizes"    -> "fontsize"
                 * "formats"      -> "styles"
                 * "template"     -> "jtemplate"
                 */
                $search  = ['blockformats', 'fontformats', 'fontsizes', 'formats'];
                $replace = ['blocks', 'fontfamily', 'fontsize', 'styles'];

                // Don't redo the template
                if (!\in_array('jtemplate', $params['configuration']['toolbars'][$setIdx]['menu'])) {
                    $search[]  = 'template';
                    $replace[] = 'jtemplate';
                }

                $params['configuration']['toolbars'][$setIdx]['menu'] = str_replace($search, $replace, $toolbarConfig['menu']);
            }

            // There could be no toolbar at all, or only toolbar1, or both toolbar1 and toolbar2
            foreach (['toolbar1', 'toolbar2'] as $toolbarIdx) {
                // Migrate toolbar buttons if that toolbar exists
                if (isset($toolbarConfig[$toolbarIdx])) {
                    /**
                     * Replace array values with button names ("old name" -> "new name"):
                     * "fontselect"     -> "fontfamily"
                     * "fontsizeselect" -> "fontsize"
                     * "formatselect"   -> "blocks"
                     * "styleselect"    -> "styles"
                     * "template"       -> "jtemplate"
                     */
                    $search  = ['fontselect', 'fontsizeselect', 'formatselect', 'styleselect'];
                    $replace = ['fontfamily', 'fontsize', 'blocks', 'styles'];

                    // Don't redo the template
                    if (!\in_array('jtemplate', $params['configuration']['toolbars'][$setIdx][$toolbarIdx])) {
                        $search[]  = 'template';
                        $replace[] = 'jtemplate';
                    }

                    $params['configuration']['toolbars'][$setIdx][$toolbarIdx] = str_replace($search, $replace, $toolbarConfig[$toolbarIdx]);
                }
            }
        }

        $params = json_encode($params);

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = ' . $db->quote($params))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('editors'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('tinymce'));

        try {
            $db->setQuery($query)->execute();
        } catch (Exception $e) {
            $this->collectError(__METHOD__, $e);

            return false;
        }

        return true;
    }

    /**
     * setup Guided Tours Unique Identifiers
     *
     * @return  boolean  True on success
     *
     * @since   5.0.0
     */
    private function setGuidedToursUid()
    {
        /** @var \Joomla\Component\Cache\Administrator\Model\CacheModel $model */
        $model = Factory::getApplication()->bootComponent('com_guidedtours')->getMVCFactory()
            ->createModel('Tours', 'Administrator', ['ignore_request' => true]);

        $items = $model->getItems();

        foreach ($items as $item) {
            // Set uid for tours where it is empty
            if (empty($item->uid)) {
                $tourItem = $model->getTable('Tour');
                $tourItem->load($item->id);

                // Tour follows Joomla naming convention
                if (str_starts_with($tourItem->title, 'COM_GUIDEDTOURS_TOUR_') && str_ends_with($tourItem->title, '_TITLE')) {
                    $uidTitle = 'joomla_' . str_replace('COM_GUIDEDTOURS_TOUR_', '', $tourItem->title);

                    // Remove the last _TITLE part
                    $pos = strrpos($uidTitle, '_TITLE');
                    if ($pos !== false) {
                        $uidTitle = substr($uidTitle, 0, $pos);
                    }
                } elseif (preg_match('#COM_(\w+)_TOUR_#', $tourItem->title) && str_ends_with($tourItem->title, '_TITLE')) {
                    // Tour follows component naming pattern
                    $uidTitle = preg_replace('#COM_(\w+)_TOUR_#', '$1.', $tourItem->title);

                    // Remove the last _TITLE part
                    $pos = strrpos($uidTitle, "_TITLE");
                    if ($pos !== false) {
                        $uidTitle = substr($uidTitle, 0, $pos);
                    }
                } else {
                    $uri      = Uri::getInstance();
                    $host     = $uri->toString(['host']);
                    $host     = ApplicationHelper::stringURLSafe($host, $tourItem->language);
                    $uidTitle = $host . ' ' . str_replace('COM_GUIDEDTOURS_TOUR_', '', $tourItem->title);
                    // Remove the last _TITLE part
                    if (str_ends_with($uidTitle, '_TITLE')) {
                        $pos      = strrpos($uidTitle, '_TITLE');
                        $uidTitle = substr($uidTitle, 0, $pos);
                    }
                }
                // ApplicationHelper::stringURLSafe will replace a period (.) separator so we split the construction into multiple parts
                $uidTitleParts = explode('.', $uidTitle);
                array_walk($uidTitleParts, function (&$value, $key, $tourLanguage) {
                    $value = ApplicationHelper::stringURLSafe($value, $tourLanguage);
                }, $tourItem->language);
                $tourItem->uid = implode('.', $uidTitleParts);

                $tourItem->store();
            }
        }
    }

    /**
     * Renames or removes incorrectly cased files.
     *
     * @return  void
     *
     * @since   3.9.25
     */
    protected function fixFilenameCasing()
    {
        $renamedFiles = new RenamedFiles();

        foreach ($renamedFiles->files as $old => $expected) {
            $oldRealpath = realpath(JPATH_ROOT . $old);

            // On Unix without incorrectly cased file.
            if ($oldRealpath === false) {
                continue;
            }

            $oldBasename      = basename($oldRealpath);
            $newRealpath      = realpath(JPATH_ROOT . $expected);
            $newBasename      = basename($newRealpath);
            $expectedBasename = basename($expected);

            // On Windows or Unix with only the incorrectly cased file.
            if ($newBasename !== $expectedBasename) {
                // Rename the file.
                File::move(JPATH_ROOT . $old, JPATH_ROOT . $old . '.tmp');
                File::move(JPATH_ROOT . $old . '.tmp', JPATH_ROOT . $expected);

                continue;
            }

            // There might still be an incorrectly cased file on other OS than Windows.
            if ($oldBasename === basename($old)) {
                // Check if case-insensitive file system, eg on OSX.
                if (fileinode($oldRealpath) === fileinode($newRealpath)) {
                    // Check deeper because even realpath or glob might not return the actual case.
                    if (!\in_array($expectedBasename, scandir(\dirname($newRealpath)))) {
                        // Rename the file.
                        File::move(JPATH_ROOT . $old, JPATH_ROOT . $old . '.tmp');
                        File::move(JPATH_ROOT . $old . '.tmp', JPATH_ROOT . $expected);
                    }
                } else {
                    // On Unix with both files: Delete the incorrectly cased file.
                    if (is_file(JPATH_ROOT . $old)) {
                        File::delete(JPATH_ROOT . $old);
                    }
                }
            }
        }
    }
}
