<?php

/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */
class Ressio_Plugin_Lazyload extends Ressio_Plugin
{
    /**
     * @param $event Ressio_Event
     * @param $optimizer IRessio_HtmlOptimizer
     * @param $node HTML_Node
     */
    public function onHtmlIterateTagImg($event, $optimizer, $node)
    {
        if ($node->parent === null) {
            return;
        }

        if (isset($node->attributes['ress-nolazy'])) {
            $node->deleteAttribute('ress-nolazy');
        } else {
            if ($this->config->img->lazyload) {
                $blankImage = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                /** @var HTML_Node $noscript */
                $noscript = new $node->childClass('noscript', null);
                $noscript->addText($node->toString());
                $node->parent->insertChild($noscript, $node->index());
                $node->setAttribute('data-src', $node->getAttribute('src'));
                $node->setAttribute('src', $blankImage);
                $node->setAttribute('class', trim($node->getAttribute('class') . ' lazy'));
            }
        }
    }

    /**
     * @param $event Ressio_Event
     * @param $optimizer IRessio_HtmlOptimizer
     */
    public function onHtmlIterateTagBODYBefore($event, $optimizer, $node)
    {
        if ($this->config->img->lazyload) {
            /** @var Ressio_UrlRewriter $urlRewriter */
            $urlRewriter = $this->di->get('urlRewriter');
            $ress_path = $urlRewriter->filepathToUrl(RESSIO_PATH . DIRECTORY_SEPARATOR);

            if ($this->config->img->lazyloaddomtastic) {
                $optimizer->appendScript($ress_path . 'js/domtastic.min.js');
            }
            $optimizer->appendScript($ress_path . 'js/jquery.lazyloadxt.min.js');
        }
    }
}