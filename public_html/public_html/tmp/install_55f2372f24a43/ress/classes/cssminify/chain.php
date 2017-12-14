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
class Ressio_CssMinify_Chain implements IRessio_CssMinify
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
        foreach ($this->config->cssminifychain as $className) {
            $processor = new $className;
            if (method_exists($processor, 'setDI')) {
                $processor->setDI($di);
            }
            $this->processors[] = $processor;
        }
    }

    /**
     * Minify CSS
     * @param string $str
     * @param string $srcBase
     * @param string $targetBase
     * @return string
     */
    public function minify($str, $srcBase = null, $targetBase = null)
    {
        $i = 0;
        foreach ($this->processors as $processor) {
            if (!$i) { // $i === 0;
                $str = $processor->minify($str, $srcBase, $targetBase);
            } else {
                $str = $processor->minify($str, $targetBase, $targetBase);
            }
            ++$i;
        }
        return $str;
    }

    /**
     * Minify CSS in style=""
     * @param string $str
     * @param string $srcBase
     * @return string
     */
    public function minifyInline($str, $srcBase = null)
    {
        foreach ($this->processors as $processor) {
            $str = $processor->minifyInline($str, $srcBase);
        }
        return $str;
    }
}