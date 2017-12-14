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

class JElementTitleList extends JElement
{
    var $_name = 'TitleList';

    function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::fetchTooltip($label, $description, $xmlElement, $control_name, $name) . '<{jqmend}/>';
    }

    function fetchElement($name, $value, &$xmlElement, $control_name)
    {
        static $js = true;
        if ($js) {
            $js = false;

            $title_id = $control_name . $name;
            $logo_id = $control_name . 'logo';

            $doc = JFactory::getDocument();
            $doc->addScriptDeclaration("
function checkTitleValue(){
	var disabled = (jqm('#$title_id').prop('selectedIndex')==0);
 	jqm('#$logo_id').textinput(disabled ? 'disable' : 'enable');
 	jqm('#$logo_id-lbl').parent().find('a.ui-btn').removeClass('ui-disabled').addClass(disabled ? 'ui-disabled' : '');
};
jqm(function(){
	checkTitleValue();
	jqm('#$title_id' ).on('change', function(){checkTitleValue(); });
});
			");
        }
        $options = array();
        foreach ($xmlElement->children() as $option) {
            if ($option->name() === 'option') {
                $options[] = JHtml::_('select.option', (string)$option->attributes('value'), JText::_(trim((string)$option->data())), 'value', 'text', false);
            }
        }
        reset($options);

        $html = array();
        $html[] = '<{jqmstart}/>';
        $html[] = JHtml::_('select.genericlist', $options, $control_name . '[' . $name . ']', ' data-mini="true"', 'value', 'text', $value, $control_name . $name);
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
