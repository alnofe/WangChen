<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

/**
 * CSS minification
 */
class Ressio_JsMinify_Chain implements IRessio_JsMinify
{
    /** @var Ressio_DI */
    protected $di;

    /** @var Ressio_Config */
    protected $config;

    /** @var IRessio_CssMinify[] */
    protected $processors = array();

    /**
     * @param $di Ressio_DI
     */
    public function setDI($di)
    {
        $this->di = $di;
        $this->config = $di->get('config');
        foreach ($this->config->jsminifychain as $className) {
            $processor = new $className;
            if (method_exists($processor, 'setDI')) {
                $processor->setDI($di);
            }
            $this->processors[] = $processor;
        }
    }

    /**
     * Minify JS
     * @param string $str
     * @return string
     */
    public function minify($str)
    {
        foreach ($this->processors as $processor) {
            $str = $processor->minify($str);
        }
        return $str;
    }

    /**
     * Minify JS in onevent=""
     * @param string $str
     * @return string
     */
    public function minifyInline($str)
    {
        foreach ($this->processors as $processor) {
            $str = $processor->minifyInline($str);
        }
        return $str;
    }
}