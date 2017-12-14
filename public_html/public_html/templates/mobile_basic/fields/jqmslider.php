<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.10
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die;

class JFormFieldJqmSlider extends JFormField
{
    protected $type = 'jqmSlider';

    protected function getLabel()
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::getLabel() . '<{jqmend}/>';
    }

    protected function getInput()
    {
        $html = array();
        $html[] = '<{jqmstart}/>';
        $html[] = '<input type="number" data-type="range" name="' . $this->name . '" id="' . $this->id . '"' .
            ' min="0" max="100" value="' . intval($this->value) .
            '" data-mini="true" data-highlight="true" />';
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
