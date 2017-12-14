<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

interface IRessio_Cache
{
    /**
     * @param $deps string|array
     * @return string
     */
    public function id($deps);

    /**
     * @param $id string
     * @return string|bool
     */
    public function getOrLock($id);

    /**
     * @param $id string
     * @param $data string
     * @return bool
     */
    public function storeAndUnlock($id, $data);

    /**
     * @param $id string
     * @return bool
     */
    public function delete($id);
}

