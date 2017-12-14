<?php

/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version     2.0.10
 * @license     GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright   (C) 2008-2015 Mobile Joomla!
 * @date        September 2015
 */

class JDocumentRenderer_MjBase extends JDocumentRenderer
{
    public function render($name = null, $params = array(), $content = null)
    {
        static $done = false;
        if (!$done) {
            $done = true;
            /** @var MjJqmFramework $mjJqmHelper */
            $mjJqmHelper = $this->_doc->mjJqmHelper;
            $mjJqmHelper->prepareHead();
        }
        return null;
    }
}

class JDocumentRendererMjHead extends JDocumentRenderer_MjBase
{
    public function render($name = null, $params = array(), $content = null)
    {
        parent::render($name, $params, $content);
        return $this->_doc->getBuffer('mjhead');
    }
}

class JDocumentRendererMjBody extends JDocumentRenderer_MjBase
{
    public function render($name = null, $params = array(), $content = null)
    {
        parent::render($name, $params, $content);
        return $this->_doc->getBuffer('mjbody');
    }
}
