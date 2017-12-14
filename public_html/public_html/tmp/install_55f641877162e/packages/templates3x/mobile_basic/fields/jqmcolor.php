<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.4
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die;

class JFormFieldJqmColor extends JFormField
{
    protected $type = 'jqmColor';

    protected function getLabel()
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::getLabel() . '<{jqmend}/>';
    }

    protected function getInput()
    {
        static $loaded = false;
        if (!$loaded) {
            $loaded = true;

            /** @var $doc JDocumentHTML */
            $doc = JFactory::getDocument();

            $colorBase = JUri::root(true) . '/templates/' . $this->form->getValue('template') . '/vendor/colorpicker/';
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
        $html[] = '<input type="text" data-type="colorpicker" name="' . $this->name . '" id="' . $this->id . '"' .
            ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" data-mini="true" size="6" maxlength="6"/>';
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
