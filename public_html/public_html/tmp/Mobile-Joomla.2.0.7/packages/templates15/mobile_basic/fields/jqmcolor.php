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

class JElementJqmColor extends JElement
{
    var $_name = 'jqmColor';

    function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::fetchTooltip($label, $description, $xmlElement, $control_name, $name) . '<{jqmend}/>';
    }

    function fetchElement($name, $value, &$xmlElement, $control_name)
    {
        static $loaded = false;
        if (!$loaded) {
            $loaded = true;

            /** @var $doc JDocumentHTML */
            $doc = JFactory::getDocument();

            $template = basename(dirname(dirname(__FILE__)));
            $colorBase = JUri::root(true) . '/templates/' . $template . '/vendor/colorpicker/';
            $doc->addStyleSheet($colorBase . 'css/colorpicker.css');
            $doc->addStyleDeclaration('div.colorpicker{z-index:1}'); // to fix jQM's collapsible header
            $doc->addScript($colorBase . 'js/colorpicker.js');
            $doc->addScriptDeclaration('
jqm(document).ready(function(){
	jqm("input:jqmData(type=\'colorpicker\')").ColorPicker({
		onSubmit: function(hsb,hex,rgb,el){
			jqm(el).val(hex);
			jqm(el).ColorPickerHide();
		},
		onBeforeShow: function(){
			jqm(this).ColorPickerSetColor(this.value);
		}
	}).on("keyup", function(){
		jqm(this).ColorPickerSetColor(this.value);
	});
});
			');
        }

        $html = array();
        $html[] = '<{jqmstart}/>';
        $html[] = '<input type="text" data-type="colorpicker" name="' . $control_name . '[' . $name . ']' . '" id="' . $control_name . $name . '"' .
            ' value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '" data-mini="true" size="6" maxlength="6"/>';
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
