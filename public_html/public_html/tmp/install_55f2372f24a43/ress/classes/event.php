<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

class Ressio_Event
{
    /** @var string */
    private $name;
    /** @var bool */
    private $stopped = false;

    /**
     * @param $name string
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function stop()
    {
        $this->stopped = true;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->stopped;
    }
}
