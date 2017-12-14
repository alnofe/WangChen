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
class Ressio_CssMinify_Ress implements IRessio_CssMinify
{
    /** @var Ressio_DI */
    protected $di;

    /** @var Ressio_Config */
    protected $config;

    /**
     * @param $di Ressio_DI
     */
    public function setDI($di)
    {
        $this->di = $di;
        $this->config = $di->get('config');
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
        $cssOptimizer = $this->di->get('cssOptimizer');
        $ret = $cssOptimizer->run($str, $srcBase, $targetBase);

        return (string)$ret;
    }

    /**
     * Minify CSS in style=""
     * @param string $str
     * @param string $srcBase
     * @return string
     */
    public function minifyInline($str, $srcBase = null)
    {
        $str = '*{' . $str . '}';

        $str = $this->minify($str, $srcBase);
        $str = trim($str, '* {}');

        return $str;
    }
}