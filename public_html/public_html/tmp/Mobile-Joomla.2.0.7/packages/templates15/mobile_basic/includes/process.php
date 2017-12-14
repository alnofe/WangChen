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
defined('_JEXEC') or die('Restricted access');

//defined('_MJ') or die('Incorrect usage of Mobile Joomla!');


jQMHelper::process($this->params);

class jQMHelper
{
    public static $params;

    public static function process($params)
    {
        jQMHelper::$params = $params;

        jQMHelper::parseComponentBuffer();

        $app =& JFactory::getApplication();
        $app->registerEvent('onAfterRender', 'jQMHelper_onAfterRender');
    }

    private static function parseComponentBuffer()
    {
        /** @var JDocumentHtml $document */
        $document =& JFactory::getDocument();
        $content = $document->getBuffer('component');

        // pagination plugin
        if (function_exists('plgContentNavigation')) {
            $content = preg_replace_callback('#<table align="center" class="pagenav">\s*<tr>(.*?)</tr>\s*</table>#s', array('jQMHelper', 'pagenav_replacer'), $content);
        }

        // pagebreak plugin
        if (function_exists('plgContentPagebreak')) {
            $content = preg_replace_callback('#<table cellpadding="0" cellspacing="0" class="contenttoc">\s*<tr>\s*<th>(.*?)</th>\s*</tr>(.*?)</table>#s', array('jQMHelper', 'pagebreak_toc_replacer'), $content);
            $content = preg_replace_callback('#<div class="pagenavbar">\s*<div>(.*?) - (.*?)</div>\s*</div>#s', array('jQMHelper', 'pagebreak_replacer'), $content);
        }

        $document->setBuffer($content, 'component');
    }

    public static function parseMessageBuffer($message)
    {
        $message = $message[0];
        $document =& JFactory::getDocument();

        $theme_title = $document->params->get('theme_messagetitle');
        $theme_content = $document->params->get('theme_messagetext');
        if ($theme_title) {
            $theme_title = ' data-divider-theme="' . $theme_title . '"';
        }
        if ($theme_content) {
            $theme_content = ' data-theme="' . $theme_content . '"';
        }

        $message = str_replace(array('<ul>', '</ul>', '</dd>'), '', $message);
        $message = preg_replace('#<dd [^>]*>#', '', $message);
        $message = str_replace('<dt ', '<li data-role="list-divider" ', $message);
        $message = str_replace('</dt>', '</li>', $message);
        $message = str_replace('<dl ', '<ul data-role="listview" data-inset="true"' . $theme_content . $theme_title . ' ', $message);
        $message = str_replace('</dl>', '</ul>', $message);

        return $message;
    }

    public static function pagenav_replacer($matches)
    {
        $doc =& JFactory::getDocument();
        $theme = $doc->params->get('theme_pagination');
        if ($theme) {
            $theme = ' data-theme="' . $theme . '"';
        }
        $inner = $matches[1];
        $inner = preg_replace('#<th class="pagenav_prev">\s*<a href="(.*?)">(.*?)</a>\s*</th>#s', '<a data-role="button" data-inline="true" href="\1" data-direction="reverse">\2</a>', $inner);
        $inner = preg_replace('#<th class="pagenav_next">\s*<a href="(.*?)">(.*?)</a>\s*</th>#s', '<a data-role="button" data-inline="true" href="\1">\2</a>', $inner);
        $inner = preg_replace('#<td width="50">.*?</td>#s', '', $inner);
        return '<div data-role="controlgroup" data-type="horizontal"' . $theme . ' class="pagenav">' . $inner . '</div>';
    }

    public static function pagebreak_toc_replacer($matches)
    {
        $title = $matches[1];
        $toc = $matches[2];
        $html = '<div id="article-index">';
        $html .= '<h3>' . $title . '</h3>';
        $html .= '<ul data-role="listview" data-inset="true">';
        $toc = preg_replace('#<tr>\s*<td>#s', '<li>', $toc);
        $toc = preg_replace('#</td>\s*</tr>#s', '</li>', $toc);
        $html .= $toc;
        $html .= '</ul>';
        $html .= '</div>';
        return $html;
    }

    public static function pagebreak_replacer($matches)
    {
        $doc =& JFactory::getDocument();
        $theme = $doc->params->get('theme_pagination');
        if ($theme) {
            $theme = ' data-theme="' . $theme . '"';
        }
        $prev = $matches[1];
        $next = $matches[2];
        if (strpos($prev, '<a ') === 0) {
            $prev = '<a data-role="button" data-inline="true"' . substr($prev, 2);
        } else {
            $prev = '<span data-role="button" data-inline="true" class="ui-btn ui-disabled">' . $prev . '</span>';
        }
        if (strpos($next, '<a ') === 0) {
            $next = '<a data-role="button" data-inline="true"' . substr($next, 2);
        } else {
            $next = '<span data-role="button" data-inline="true" class="ui-btn ui-disabled">' . $next . '</span>';
        }
        return '<div data-role="controlgroup" data-type="horizontal"' . $theme . ' class="pagination pagenav">' . $prev . $next . '</div>';
    }
}

function jQMHelper_onAfterRender()
{
    $buffer = JResponse::getBody();

    // parse message area
    $buffer = preg_replace_callback('#<dl id="system-message">.*?</dl>#s', array('jQMHelper', 'parseMessageBuffer'), $buffer);

    if ($buffer) {
        JResponse::setBody($buffer);
    }

    return true;
}
