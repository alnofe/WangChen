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

class JElementJqmAssets extends JElement
{
    var $_name = 'jqmAssets';

    function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
    {
        return '';
    }

    function fetchElement($name, $value, &$xmlElement, $control_name)
    {
        $template = basename(dirname(dirname(__FILE__)));

        $html = '';
        $html .= $this->getEditLink($template, 'custom.css');
        //$html .= $this->getEditLink($template, 'custom_preload.txt');

        return '<{jqmstart}/>' . $html . '<{jqmend}/>';
    }

    private function getEditLink($template, $file)
    {
        if (!file_exists(JPATH_ROOT . '/templates/' . $template . '/css/' . $file)) {
            return '';
        }

        $url = 'index.php?option=com_templates&amp;task=edit_css&amp;id=' . $template . '&amp;filename=' . $file;

        return "<p><a href=\"$url\" target=\"_blank\">" . JText::_('TPL_MOBILE_JQM__FIELD_EDIT') . " css/$file</a></p>";
    }
}
