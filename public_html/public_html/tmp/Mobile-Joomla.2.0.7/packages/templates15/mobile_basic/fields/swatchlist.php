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

class JElementSwatchList extends JElement
{
    var $_name = 'SwatchList';

    function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::fetchTooltip($label, $description, $xmlElement, $control_name, $name) . '<{jqmend}/>';
    }

    function fetchElement($name, $value, &$xmlElement, $control_name)
    {
        $options = array(JHtml::_('select.option', $value));

        $html = array();
        $html[] = '<{jqmstart}/>';
        $html[] = JHtml::_('select.genericlist', $options, $control_name . '[' . $name . ']', ' data-mini="true"', 'value', 'text', $value, $control_name . $name);
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
