<?php

/**
 * @package    "JExtBOX Page Navigation Plus" plugin for Joomla!
 * @copyright  Copyright (c) 2013-2015 Galaa
 * @author     Galaa
 * @link       http://jextbox.com
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;
?>
<ul class="pager pagenav">
<?php if ($row->prev) : ?>
	<li class="previous">
		<a href="<?php echo $row->prev; ?>" rel="prev"><?php echo '<span class="icon-chevron-left"></span>' . $pnSpace . $row->prev_title; ?></a>
	</li>
<?php endif; ?>
<?php if ($row->next) : ?>
	<li class="next">
		<a href="<?php echo $row->next; ?>" rel="next"><?php echo $row->next_title . $pnSpace . '<span class="icon-chevron-right"></span>'; ?></a>
	</li>
<?php endif; ?>
</ul>
