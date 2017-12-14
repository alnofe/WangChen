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
defined('_JEXEC') or die('Restricted access');

/** @var $params Joomla\Registry\Registry */
/** @var $links array */

$parts = array();
foreach ($links as $link) {
    if ($link['url']) {
        $parts[] = '<a href="' . $link['url'] . '" rel="nofollow" data-ajax="false">' . $link['text'] . '</a>';
    } else {
        $parts[] = '<span class="active">' . $link['text'] . '</span>';
    }
}
?>
<div class="mjswitcher">
    <?php echo $params->get('show_text', ' '); ?>
    <?php echo implode('<span> | </span>', $parts); ?>
</div>