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

class JFormFieldJqmList extends JFormField
{
    protected $type = 'jqmList';

    protected function getLabel()
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::getLabel() . '<{jqmend}/>';
    }

    protected function getInput()
    {
        $html = array();

        $options = (array)$this->getOptions();

        $html[] = '<{jqmstart}/>';
        $html[] = JHtml::_('select.genericlist', $options, $this->name, ' data-mini="true" data-chosen="done" class="chzn-done"', 'value', 'text', $this->value, $this->id);
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }

    protected function getOptions()
    {
        $options = array();

        foreach ($this->element->children() as $option) {
            if ($option->getName() != 'option')
                continue;

            $options[] = JHtml::_(
                'select.option', (string)$option['value'],
                JText::alt(trim((string)$option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname))
            );
        }

        reset($options);

        return $options;
    }
}
