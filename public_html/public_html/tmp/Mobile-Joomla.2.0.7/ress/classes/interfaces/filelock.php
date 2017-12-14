<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

interface IRessio_FileLock
{
    /**
     * @param $filename string
     * @return bool
     */
    public function lock($filename);

    /**
     * @param $filename string
     * @return bool
     */
    public function unlock($filename);

    /**
     * @param $filename string
     * @param $local bool
     * @return bool
     */
    public function isLocked($filename, $local = false);
}

