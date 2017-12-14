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
defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT . '/classes/mjcontroller.php';

class MjDefaultController extends MjController
{
    public function save($msg = '')
    {
        include_once JPATH_COMPONENT . '/models/settings.php';
        $mjSettings = new MjSettingsModel($this->joomlaWrapper);

        $bindData = array();
        foreach ($_POST as $key => $value) {
            if (substr($key, 0, 3) === 'mj_') {
                $param = substr($key, 3);
                $param = str_replace('-', '.', $param);
                $bindData[$param] = $value;
//                $mjSettings->set($param, $bindData[$param]);
            }
        }

        if (!$mjSettings->bind($bindData)) {
            $msg = 'Error in data.';
        } elseif (!$mjSettings->save()) {
            $msg = 'Cannot save data.';
        } else {
            $msg = 'Data have been saved successfully.';
        }

        parent::save($msg);
    }
}