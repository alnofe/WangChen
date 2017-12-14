<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.10
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die('Restricted access');

/** @var MjController $this */
/** @var array $params */
/** @var string $controllerName */
/** @var string $viewName */

$bootstrapTemplate = version_compare(JVERSION, '3.0', '>=');

?>
<div id="mj">
    <?php if (!$bootstrapTemplate) : ?><div class="container-fluid clearfix"><?php endif; ?>
        <div class="row-fluid">
            <div class="span2 sidebar-nav">
                <?php echo $params['sidebar']; ?>
            </div>
            <div class="span10">
                <div id="mjmsgarea"></div>
                <div id="mjupdatearea"></div>
                <div id="mjnotification"></div>
                <?php echo $params['content']; ?>
            </div>
        </div>
    <?php if (!$bootstrapTemplate) : ?></div><?php endif; ?>
</div>