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

class JFormFieldJqmAssets extends JFormField
{
    protected $type = 'jqmAssets';

    protected function getLabel()
    {
        return '';
    }

    protected function getInput()
    {
        $style_id = JFactory::getApplication()->input->getInt('id');

        $joomlaWrapper = MjJoomlaWrapper::getInstance();
        $db = $joomlaWrapper->getDbo();

        $query = new MjQueryBuilder($db);
        $template = $query
            ->select('e.extension_id AS ' . $query->qn('id') . ', e.element AS ' . $query->qn('name'))
            ->from($query->qn('#__extensions') . ' AS e')
            ->leftJoin($query->qn('#__template_styles') . ' AS ts ON ts.template=e.element')
            ->where('e.type=' . $query->q('template'))
            ->where('ts.id=' . $style_id)
            ->setQuery()
            ->loadObject();

        $html = '';
        $html .= $this->getEditLink($template, 'css/custom.css');
        $html .= $this->getEditLink($template, 'css/custom_preload.txt');
        $html .= $this->getEditLink($template, 'js/custom.js');
        $html .= $this->getEditLink($template, 'js/custom_preload.txt');

        return '<{jqmstart}/>' . $html . '<{jqmend}/>';
    }

    private function getEditLink($template, $file)
    {
        if (!file_exists(JPATH_ROOT . '/templates/' . $template->name . '/' . $file))
            return '';

        $url = 'index.php?option=com_templates&amp;task=source.edit&amp;id=' . base64_encode($template->id . ':' . $file);

        return "<p><a href=\"$url\" target=\"_blank\">" . JText::_('TPL_MOBILE_JQM__FIELD_EDIT') . " $file</a></p>";
    }
}
