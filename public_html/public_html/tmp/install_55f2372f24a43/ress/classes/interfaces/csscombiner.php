<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

interface IRessio_CssCombiner
{
    /**
     * @param $styleList array
     * @param $targetUrl string
     * @return string
     */
    public function combine($styleList, $targetUrl);
}