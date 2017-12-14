<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

interface IRessio_HtmlOptimizer
{
    /**
     * @param $buffer string
     * @return string
     */
    public function run($buffer);

    /**
     * @param $file string
     * @param $attribs array|null
     */
    public function appendScript($file, $attribs = null);

    /**
     * @param $content string
     * @param $attribs array|null
     */
    public function appendScriptDeclaration($content, $attribs = null);

    /**
     * @param $file string
     * @param $attribs array|null
     */
    public function appendStylesheet($file, $attribs = null);

    /**
     * @param $content string
     * @param $attribs array|null
     */
    public function appendStyleDeclaration($content, $attribs = null);
}
