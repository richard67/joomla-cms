<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_menu
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$id = '';

if ($tagId = $params->get('tag_id', ''))
{
	$id = ' id="' . $tagId . '"';
}

$isDropdown = false;

// The menu class is deprecated. Use mod-menu instead
?>
<ul<?php echo $id; ?> class="mod-menu nav mr-auto<?php echo $class_sfx; ?>">
<?php foreach ($list as $i => &$item)
{
	$itemParams = $item->getParams();

	if ($isDropdown)
	{
		$class = 'dropdown-item';
	}
	else
	{
		$class = 'nav-item';

		if ($item->deeper)
		{
			$class .= ' dropdown';
			$isDropdown = true;
		}
	}

	if ($item->id == $default_id)
	{
		$class .= ' default';
	}

	if ($item->id == $active_id || ($item->type === 'alias' && $itemParams->get('aliasoptions') == $active_id))
	{
		$class .= ' current';
	}

	if (in_array($item->id, $path))
	{
		$class .= ' active';
	}
	elseif ($item->type === 'alias')
	{
		$aliasToId = $itemParams->get('aliasoptions');

		if (count($path) > 0 && $aliasToId == $path[count($path) - 1])
		{
			$class .= ' active';
		}
		elseif (in_array($aliasToId, $path))
		{
			$class .= ' alias-parent-active';
		}
	}

	if ($item->type === 'separator')
	{
		$class .= ' divider';
	}

	if ($item->deeper)
	{
		$class .= ' deeper';
		//$item->anchor_css = 'nav-link';
	}

	if ($item->parent)
	{
		$class .= ' parent';
	}

	echo '<li class="' . $class . '" data-debug-level="' . $item->level . '" data-debug-level-diff="' . $item->level_diff . '" data-debug-shallower="' . $item->shallower . '">';

	switch ($item->type) :
		case 'separator':
		case 'component':
		case 'heading':
		case 'url':
			require ModuleHelper::getLayoutPath('mod_menu', 'default_' . $item->type);
			break;

		default:
			require ModuleHelper::getLayoutPath('mod_menu', 'default_url');
			break;
	endswitch;

	// The next item is deeper.
	if ($item->deeper)
	{
		echo '<a class="nav-link dropdown-toggle dropdown-toggle-split" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></a>';
		echo '<ul class="mod-menu__sub dropdown-menu" aria-labelledby="navbarDropdown">';
	}
	// The next item is shallower.
	elseif ($item->shallower)
	{
		echo '</li>';
		echo str_repeat('</ul></li>', $item->level_diff);
		$isDropdown = false;
	}
	// The next item is on the same level.
	else
	{
		echo '</li>';
	}
}
?></ul>
