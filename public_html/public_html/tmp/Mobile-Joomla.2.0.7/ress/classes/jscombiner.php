<?php

/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */
class Ressio_JsCombiner implements IRessio_JsCombiner
{
    /** @var Ressio_DI */
    private $di;
    /** @var Ressio_Config */
    private $config;

    /**
     * @param $di Ressio_DI
     */
    public function setDI($di)
    {
        $this->di = $di;
        $this->config = $di->get('config');
    }

    /**
     * @param $scriptList array
     * @return string
     */
    public function combine($scriptList)
    {
        /** @var Ressio_UrlRewriter $urlRewriter */
        $urlRewriter = $this->di->get('urlRewriter');
        /** @var IRessio_Filesystem $fs */
        $fs = $this->di->get('filesystem');
        /** @var Ressio_Dispatcher $dispatcher */
        $dispatcher = $this->di->get('dispatcher');
        /** @var IRessio_JsMinify $minifyJs */
        $minifyJs = $this->di->get('jsMinify');

        $dispatcher->triggerEvent('JsCombineBefore', array(&$scriptList));

        $js = '';
        foreach ($scriptList as $item) {
            if ($item['type'] === 'inline') {
                $content = $item['script'];
                $dispatcher->triggerEvent('JsInlineMinifyBefore', array(&$content));
                $minified = $minifyJs->minifyInline($content);
                $dispatcher->triggerEvent('JsInlineMinifyAfter', array(&$minified));
                if ($this->config->js->wraptrycatch) {
                    $minified = 'try{' . $minified . '}catch(e){console.log(e)}';
                }
                $js .= $minified;
            } else {
                $path = $urlRewriter->urlToFilepath($item['src']);
                $isMinified = preg_match('/\.min\.js$/', $path);
                if (!$isMinified && pathinfo($path, PATHINFO_EXTENSION) === 'js') {
                    $path_min = substr($path, 0, -2) . 'min.js';
                    if ($fs->isFile($path_min)) {
                        $path = $path_min;
                        $isMinified = true;
                    }
                }
                $content = $fs->getContents($path);
                // @todo check $content===false
                if (substr($content, 0, 2) === "\x1f\x8b") {
                    $content = gzinflate(substr($content, 10, -8));
                }
                if (!$isMinified) {
                    $dispatcher->triggerEvent('JsFileMinifyBefore', array($item['src'], &$content));
                    $minified = $minifyJs->minify($content);
                    $dispatcher->triggerEvent('JsFileMinifyAfter', array($item['src'], &$minified));
                } else {
                    $minified = $content;
                }
                if ($this->config->js->wraptrycatch) {
                    $minified = 'try{' . $minified . '}catch(e){console.log(e)}';
                }
                $js .= $minified;
            }
        }

        if ($this->config->js->crossfileoptimization && count($scriptList) > 1) {
            $js = $minifyJs->minify($js);
        }

        $dispatcher->triggerEvent('JsCombineAfter', array(&$js));

        return $js;
    }
}