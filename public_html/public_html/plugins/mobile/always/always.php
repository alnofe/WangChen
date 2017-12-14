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

jimport('joomla.plugin.plugin');

class plgMobileAlways extends JPlugin
{
    public function plgMobileAlways(& $subject, $config)
    {
        parent::__construct($subject, $config);
        if (!isset($this->params)) {
            $this->params = new JParameter(null);
        }
    }

    public function onMjGetDeviceList()
    {
        return array(
            'desktop' => 'Desktop',
            'mobile' => 'Mobile'
        );
    }

    public function onDeviceDetection($mj)
    {
        /** @var MjDevice $mjDevice */
        $mjDevice = $mj->device;

        $markup = $this->params->get('markup', '');
        if ($markup) {
            $mjDevice->markup = $markup;
        }
    }
}
