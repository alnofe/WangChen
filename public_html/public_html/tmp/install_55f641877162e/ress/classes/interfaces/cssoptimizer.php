<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

interface IRessio_CssOptimizer
{
    /**
     * @param $buffer string
     * @param $srcBase string
     * @param $targetBase string
     * @return string
     */
    public function run($buffer, $srcBase = null, $targetBase = null);
}
