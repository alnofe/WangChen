<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

/**
 * Abstract JS minification class
 */
interface IRessio_JsMinify
{
    /**
     * Minify JS
     * @param string $str
     * @return string
     */
    public function minify($str);

    /**
     * Minify JS in style=""
     * @param string $str
     * @return string
     */
    public function minifyInline($str);
}
