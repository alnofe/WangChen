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
defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT . '/classes/mjcontroller.php';

class MjPreviewController extends MjController
{
    public function display()
    {
        $this->loadFramework();

        $viewName = $this->joomlaWrapper->getRequestWord('view', '');

        echo $this->renderView('global/page', array(
            'sidebar' => $this->renderView('global/sidebar', array(
                'controllerName' => $this->name,
                'viewName' => $viewName
            )),
            'content' => $this->renderView('preview', array(
                'viewName' => $viewName
            ))
        ));
    }
}