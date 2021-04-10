<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div id="installer-database" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=database'); ?>" method="post" name="adminForm" id="adminForm">

	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>
		<?php if ($this->errorCount > 0) : ?>
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'problems')); ?>
		<?php elseif ($this->badCreatedCount > 0) : ?>
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'badCreatedDates')); ?>
		<?php else : ?>
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'other')); ?>
		<?php endif; ?>
		<?php if ($this->errorCount > 0) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'problems', JText::plural('COM_INSTALLER_MSG_N_DATABASE_ERROR_PANEL', $this->errorCount)); ?>
				<fieldset class="panelform">
					<ul>
						<?php if (!$this->filterParams) : ?>
							<li><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_FILTER_ERROR'); ?></li>
						<?php endif; ?>

						<?php if ($this->schemaVersion != $this->changeSet->getSchema()) : ?>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_ERROR', $this->schemaVersion, $this->changeSet->getSchema()); ?></li>
						<?php endif; ?>

						<?php if (version_compare($this->updateVersion, JVERSION) != 0) : ?>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATEVERSION_ERROR', $this->updateVersion, JVERSION); ?></li>
						<?php endif; ?>

						<?php foreach ($this->errors as $line => $error) : ?>
							<?php $key = 'COM_INSTALLER_MSG_DATABASE_' . $error->queryType;
							$msgs = $error->msgElements;
							$file = basename($error->file);
							$msg0 = isset($msgs[0]) ? $msgs[0] : ' ';
							$msg1 = isset($msgs[1]) ? $msgs[1] : ' ';
							$msg2 = isset($msgs[2]) ? $msgs[2] : ' ';
							$message = JText::sprintf($key, $file, $msg0, $msg1, $msg2); ?>
							<li><?php echo $message; ?></li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>
		<?php if ($this->badCreatedCount > 0) : ?>
			<?php $lang = JFactory::getLanguage();
				$lang->load('com_banners.sys', JPATH_ADMINISTRATOR, null, false, true);
				$lang->load('com_contact', JPATH_ADMINISTRATOR, null, false, true);
				$lang->load('com_redirect', JPATH_ADMINISTRATOR, null, false, true);
				$lang->load('com_users', JPATH_ADMINISTRATOR, null, false, true); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'badCreatedDates', JText::plural('COM_INSTALLER_MSG_N_DATABASE_BAD_CREATED_DATES_PANEL', $this->badCreatedCount)); ?>
				<?php echo JText::_('COM_INSTALLER_MSG_DATABASE_BAD_CREATED_DATES_DETAILS'); ?>
				<?php $tableNumberPrevious = null;
					$db = JFactory::getDbo();
					$dbPrefix = $db->getPrefix(); ?>
				<?php foreach ($this->badCreatedDates as $line => $badDate) : ?>
					<?php if ($badDate->table_number !== $tableNumberPrevious) : ?>
						<?php if ($tableNumberPrevious !== null) : ?>
					</tbody>
				</table>
						<?php endif; ?>
						<?php $tableNumberPrevious = $badDate->table_number;
							$tableAndColumnsDb = explode('/', $badDate->table_columns_db);
							$table = isset($tableAndColumnsDb[0]) ? str_replace('#__', $dbPrefix, $tableAndColumnsDb[0]) : '?';
							$colCreated = isset($tableAndColumnsDb[1]) ? $tableAndColumnsDb[1] : '?';
							$colId = isset($tableAndColumnsDb[2]) ? $tableAndColumnsDb[2] : '?';
							$colName = isset($tableAndColumnsDb[3]) ? $tableAndColumnsDb[3] : '?';
							$tableAndColumnsTxt = explode('/', $badDate->table_columns_txt);
							$contentType = isset($tableAndColumnsTxt[0]) ? JText::_($tableAndColumnsTxt[0]) : '?';
							$fieldCreated = isset($tableAndColumnsTxt[1]) ? JText::_($tableAndColumnsTxt[1]) : '?';
							$fieldId = isset($tableAndColumnsTxt[2]) ? JText::_($tableAndColumnsTxt[2]) : '?';
							$fieldName = isset($tableAndColumnsTxt[3]) ? JText::_($tableAndColumnsTxt[3]) : '?'; ?>
				<fieldset class="panelform">
					<legend class="label">
						<h4><?php echo $contentType . ' - ' . $fieldCreated . ' (' . $db->quoteName($table) . '.' . $db->quoteName($colCreated) . ')'; ?></h4>
					</legend>
					<table class="table table-striped">
						<thead>
							<tr>
								<th scope="col" class="nowrap" width="1%">
									<?php echo $fieldId . ' (' .  $colId. ')'; ?>
								</th>
								<th scope="col">
									<?php echo $fieldName . ' (' .  $colName. ')'; ?>
								</th>
							</tr>
						</thead>
						<tbody>
					<?php endif; ?>
							<tr>
								<td>
									<?php echo $badDate->id; ?>
								</td>
								<td>
									<?php echo $badDate->name; ?>
								</td>
							</tr>
				<?php endforeach; ?>
						</tbody>
					</table>
				</fieldset>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'other', JText::_('COM_INSTALLER_MSG_DATABASE_INFO')); ?>
				<div class="control-group" >
					<fieldset class="panelform">
						<ul>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_VERSION', $this->schemaVersion); ?></li>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATE_VERSION', $this->updateVersion); ?></li>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_DRIVER', JFactory::getDbo()->name); ?></li>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_CHECKED_OK', count($this->results['ok'])); ?></li>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SKIPPED', count($this->results['skipped'])); ?></li>
						</ul>
					</fieldset>
				</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
