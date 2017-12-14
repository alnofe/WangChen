<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.3
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die ('Restricted access');

/** @var $params Joomla\Registry\Registry */
/** @var $module stdClass */

$position = $params->get('position', '');
$module->showtitle = 0;

// @todo: get chrome data

$doc = JFactory::getDocument();
/** @var JDocumentRendererModules $renderer */
$renderer = $doc->loadRenderer('modules');
echo $renderer->render($position, $attribs);
