<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

/**
 * No JS minification
 */
class Ressio_JsMinify_None implements IRessio_JsMinify
{
    /**
     * Minify JS
     * @param string $str
     * @return string
     */
    public function minify($str)
    {
        return $str;
    }

    /**
     * Minify JS in onevent=""
     * @param string $str
     * @return string
     */
    public function minifyInline($str)
    {
        return $str;
    }
}