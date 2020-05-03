<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for checking if there are pending extension updates
 *
 * @since  4.0.0
 */
class CheckJoomlaUpdatesCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected static $defaultName = 'core:check-updates';

	/**
	 * Stores the Update Information
	 * @var UpdateModel
	 * @since 4.0
	 */
	private $updateInfo;

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function configure(): void
	{
		$help = <<<'EOF'
The <info>%command.name%</info> Checks for Joomla updates.

  <info>php %command.full_name%</info>
EOF;
		$this->setDescription('Checks for Joomla updates');
		$this->setHelp($help);
	}

	/**
	 * Retrieves Update Information
	 *
	 * @return mixed
	 *
	 * @since 4.0
	 */
	private function getUpdateInformationFromModel()
	{
		$app = $this->getApplication();
		$updatemodel = $app->bootComponent('com_joomlaupdate')->getMVCFactory($app)->createModel('Update', 'Administrator');
		$updatemodel->purge();
		$updatemodel->refreshUpdates(true);

		return $updatemodel;
	}

	/**
	 * Gets the Update Information
	 *
	 * @return mixed
	 *
	 * @since 4.0
	 */
	public function getUpdateInfo()
	{
		if (!$this->updateInfo)
		{
			$this->setUpdateInfo();
		}

		return $this->updateInfo;
	}

	/**
	 * Sets the Update Information
	 *
	 * @param   null  $info  stores update Information
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	public function setUpdateInfo($info = null): void
	{
		if (!$info)
		{
			$this->updateInfo = $this->getUpdateInformationFromModel();
		}
		else
		{
			$this->updateInfo = $info;
		}
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$symfonyStyle = new SymfonyStyle($input, $output);

		$model = $this->getUpdateInfo();
		$data  = $model->getUpdateInformation();
		$symfonyStyle->title('Joomla! Updates');

		if (!$data['hasUpdate'])
		{
			$symfonyStyle->success('You already have the latest Joomla version ' . $data['latest']);

			return 0;
		}

		$symfonyStyle->note('New Joomla Version ' . $data['latest'] . ' is available.');

		if (!isset($data['object']->downloadurl->_data))
		{
			$symfonyStyle->warning('We cannot find an update URL');
		}

		return 0;
	}
}
