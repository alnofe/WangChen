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
defined('_JEXEC') or die ('Restricted access');

/**
 * @param string $module
 * @param string $title
 * @param array $attribs
 * @return string
 */
function mjProxyModuleRender($module, $title, $attribs)
{
    $module =& JModuleHelper::getModule($module, $title);
    if (!is_object($module)) {
        return '';
    }

    $doc = JFactory::getDocument();
    /** @var JDocumentRendererModule $renderer */
    $renderer = $doc->loadRenderer('module');
    return $renderer->render($module, $attribs);
}
