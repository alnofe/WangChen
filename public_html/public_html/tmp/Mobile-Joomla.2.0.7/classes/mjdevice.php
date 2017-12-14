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

class MjDevice
{
    public $markup = false;
    public $default_markup = false;
    public $real_markup = false;
    public $screenwidth = 0;
    public $screenheight = 0;
    public $pixelratio = 1;
    public $imageformats;
    public $mimetype;
    public $param = array();
} 