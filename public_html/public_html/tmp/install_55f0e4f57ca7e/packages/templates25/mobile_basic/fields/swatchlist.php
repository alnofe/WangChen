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

class JFormFieldSwatchList extends JFormField
{
    protected $type = 'SwatchList';

    protected function getLabel()
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::getLabel() . '<{jqmend}/>';
    }

    protected function getInput()
    {
        $options = array(JHtml::_('select.option', $this->value));

        $html = array();
        $html[] = '<{jqmstart}/>';
        $html[] = JHtml::_('select.genericlist', $options, $this->name, ' data-mini="true"', 'value', 'text', $this->value, $this->id);
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
