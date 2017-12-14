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
defined('_JEXEC') or die;

class JElementJqmDemo extends JElement
{
    var $_name = 'jqmDemo';

    function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::fetchTooltip($label, $description, $xmlElement, $control_name, $name) . '<{jqmend}/>';
    }

    function fetchElement($name, $value, &$xmlElement, $control_name)
    {
        $html = array();
        $html[] = '<{jqmstart}/>';
        $html[] = '<a href="http://www.mobilejoomla.com/templates.html" target="_blank">Available in Premium Templates</a>';
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
