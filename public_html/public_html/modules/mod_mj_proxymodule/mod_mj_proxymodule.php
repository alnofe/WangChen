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
defined('_JEXEC') or die ('Restricted access');

/** @var $params Joomla\Registry\Registry */
/** @var $module stdClass */
/** @var $attribs array */

$module_id = $params->get('module_id', 0);

$joomlaWrapper = MjJoomlaWrapper::getInstance();
$db = $joomlaWrapper->getDbo();

$query = new MjQueryBuilder($db);
$result = $query
    ->select('module', 'title')
    ->from('#__modules')
    ->where($query->qn('id') . '=' . (int)$module_id)
    ->setQuery()
    ->loadObject();
if (!is_object($result)) {
    return;
}

include_once dirname(__FILE__) . '/helper.php';
$module->showtitle = 0;
echo mjProxyModuleRender($result->module, $result->title, $attribs);
