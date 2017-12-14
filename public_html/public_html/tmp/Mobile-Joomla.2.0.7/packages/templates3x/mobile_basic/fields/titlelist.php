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

class JFormFieldTitleList extends JFormField
{
    protected $type = 'TitleList';

    protected function getLabel()
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::getLabel() . '<{jqmend}/>';
    }

    protected function getInput()
    {
        static $js = true;
        if ($js) {
            $js = false;

            $title_id = $this->id;
            $logo_id = $this->getId('logo', null);

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
        foreach ($this->element->children() as $option)
            if ($option->getName() == 'option')
                $options[] = JHtml::_('select.option', (string)$option['value'], JText::alt(trim((string)$option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text', false);
        reset($options);

        $html = array();
        $html[] = '<{jqmstart}/>';
        $html[] = JHtml::_('select.genericlist', $options, $this->name, ' data-mini="true" data-chosen="done" class="chzn-done"', 'value', 'text', $this->value, $this->id);
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
