<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_related_items
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<ul data-role="listview" class="relateditems<?php echo $moduleclass_sfx; ?>">
<?php foreach ($list as $item) :	?>
<li>
	<a href="<?php echo $item->route; ?>"><?php echo $item->title; ?></a>
	<?php if ($showDate): ?>
		<p class="ui-li-aside"><?php echo JHTML::_('date', $item->created, JText::_('DATE_FORMAT_LC4')). " - "; ?></p>
	<?php endif; ?>
</li>
<?php endforeach; ?>
</ul>
