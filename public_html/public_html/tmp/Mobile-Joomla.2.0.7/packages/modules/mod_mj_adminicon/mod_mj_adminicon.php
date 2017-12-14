<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.7
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die('Restricted Access');

if (!is_file(JPATH_ADMINISTRATOR . '/components/com_mobilejoomla/mobilejoomla.html.php')) {
    return;
}

if (version_compare(JVERSION, '1.7', '>=')) {
    $pluginPath = JPATH_ROOT . '/plugins/quickicon/mjcpanel/mjcpanel.php';
    $iconclass = 'icon17';
} elseif (version_compare(JVERSION, '1.6', '>=')) {
    $pluginPath = JPATH_ROOT . '/plugins/quickicon/mjcpanel/mjcpanel.php';
    $iconclass = 'icon16';
} else {
    $pluginPath = JPATH_ROOT . '/plugins/quickicon/mjcpanel.php';
    $iconclass = 'icon15';
}

if (!is_file($pluginPath)) {
    return;
}

include_once $pluginPath;

$icons = plgQuickiconMjcpanel::getIcons();
if (count($icons) === 0) {
    return;
}

$lang = JFactory::getLanguage();
$rtl = ($lang->isRTL()) ? 'right' : 'left';
?>
<div id="mjicon">
<?php foreach ($icons as $icon) : ?>
    <div id="<?php echo $icon['id']; ?>" class="icon-wrapper" style="float:<?php echo $rtl; ?>">
        <div class="icon <?php echo $iconclass; ?>">
            <a href="<?php echo $icon['link']; if (isset($icon['target'])) echo '" target="' . $icon['target']; ?>">
                <img src="<?php echo $icon['image']; ?>"/>
                <span><?php echo $icon['text']; ?></span>
            </a>
        </div>
    </div>
<?php endforeach; ?>
</div>