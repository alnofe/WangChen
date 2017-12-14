<?php

/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */
class Ressio_CssCombiner implements IRessio_CssCombiner
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
     * @param $styleList array
     * @param $targetUrl string
     * @return string
     */
    public function combine($styleList, $targetUrl)
    {
        /** @var Ressio_UrlRewriter $urlRewriter */
        $urlRewriter = $this->di->get('urlRewriter');
        /** @var IRessio_Filesystem $fs */
        $fs = $this->di->get('filesystem');
        /** @var Ressio_Dispatcher $dispatcher */
        $dispatcher = $this->di->get('dispatcher');
        /** @var IRessio_CssMinify $minifyCss */
        $minifyCss = $this->di->get('cssMinify');

        $dispatcher->triggerEvent('CssCombineBefore', array(&$styleList, &$targetUrl));

        $css = '';
        $base = $urlRewriter->getBase();
        $targetBase = dirname($targetUrl);
        foreach ($styleList as $item) {
            if ($item['type'] === 'inline') {
                $content = $item['style'];
                $media = $item['media'];
                if (!in_array($media, array('', 'all'), true)) {
                    $content = '@media ' . $media . '{' . $content . '}';
                }
                $dispatcher->triggerEvent('CssInlineMinifyBefore', array(&$content));
                $minified = $minifyCss->minify($content, $base, $targetBase);
                $dispatcher->triggerEvent('JsInlineMinifyAfter', array(&$minified));
                $css .= $minified;
            } else {
                $path = $urlRewriter->urlToFilepath($item['src']);
                $isMinified = preg_match('/\.min\.css$/', $path);
                if (!$isMinified && pathinfo($path, PATHINFO_EXTENSION) === 'css') {
                    $path_min = substr($path, 0, -3) . 'min.css';
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
                $media = $item['media'];
                $embedMedia = !in_array($media, array('', 'all'), true);
                if ($embedMedia) {
                    $content = '@media ' . $media . '{' . $content . '}';
                }
                if (!$isMinified || $embedMedia) {
                    $dispatcher->triggerEvent('CssFileMinifyBefore', array($item['src'], &$content, $targetBase));
                    // @todo: check for errors
                    $minified = $minifyCss->minify($content, dirname($item['src']), $targetBase);
                    $dispatcher->triggerEvent('CssFileMinifyAfter', array($item['src'], &$minified));
                    $css .= $minified;
                } else {
                    $css .= $content;
                }
            }
        }

        if ($this->config->css->crossfileoptimization && count($styleList) > 1) {
            $css = $minifyCss->minify($css, $targetBase, $targetBase);
        }

        $dispatcher->triggerEvent('CssCombineAfter', array(&$css));

        return $css;
    }
}