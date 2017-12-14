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
defined('_JEXEC') or die;

class JElementJqmText extends JElement
{
    var $_name = 'jqmText';

    function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::fetchTooltip($label, $description, $xmlElement, $control_name, $name) . '<{jqmend}/>';
    }

    function fetchElement($name, $value, &$xmlElement, $control_name)
    {
        // Initialize some field attributes.
        $size = $xmlElement->attributes('size') ? ' size="' . (int)$xmlElement->attributes('size') . '"' : '';
        $maxLength = $xmlElement->attributes('maxlength') ? ' maxlength="' . (int)$xmlElement->attributes('maxlength') . '"' : '';

        $html = array();
        $html[] = '<{jqmstart}/>';
        $html[] = '<input type="text" name="' . $control_name . '[' . $name . ']' . '" id="' . $control_name . $name . '"'
            . ' value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"'
            . ' placeholder="' . htmlspecialchars($xmlElement->attributes('default'), ENT_COMPAT, 'UTF-8') . '"'
            . ' data-mini="true"' . $size . $maxLength . '/>';
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
