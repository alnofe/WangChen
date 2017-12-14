<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version		2.0.7
 * @license		GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright	(C) 2008-2015 Mobile Joomla!
 * @date		September 2015
 */

// no direct access
defined('_JEXEC') or die;

?>
<div class="breadcrumbs<?php echo $moduleclass_sfx; ?>">
<?php
	if($params->get('showHere', 1))
		echo JText::_('MOD_BREADCRUMBS_HERE');

?>
	<div data-role="controlgroup" data-type="horizontal" class="pathway">
<?php
	for($i=0; $i<$count; $i++)
		if($i<$count-1)
		{
			if(!empty($list[$i]->link))
				echo '<a data-role="button" data-icon="arrow-r" data-iconpos="right" href="'.$list[$i]->link.'">'.$list[$i]->name.'</a>';
			else
				echo '<span data-role="button" data-icon="arrow-r" data-iconpos="right" class="ui-btn ui-disabled">'.$list[$i]->name.'</span>';
		}
		elseif($params->get('showLast', 1))
			echo '<span data-role="button" class="last ui-btn ui-disabled">'.$list[$i]->name.'</span>';
?>
	</div>
</div>
