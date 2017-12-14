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

class JFormFieldJqmDemo extends JFormField
{
    protected $type = 'jqmDemo';

    protected function getLabel()
    {
        return '<{jqmstart}/><div class="ui-field-contain">' . parent::getLabel() . '<{jqmend}/>';
    }

    protected function getInput()
    {
        $html = array();
        $html[] = '<{jqmstart}/>';
        $html[] = '<a href="http://www.mobilejoomla.com/templates.html" target="_blank">Available in Premium Templates</a>';
        $html[] = '</div><{jqmend}/>';

        return implode($html);
    }
}
