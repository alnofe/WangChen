<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

class Ressio_Plugin
{
    /** @var Ressio_DI */
    protected $di;
    /** @var Ressio_Config */
    protected $config;
    /** @var stdClass */
    protected $options;

    /**
     * @param $di Ressio_DI
     * @param $options stdClass|null
     */
    public function __construct($di, $options = null)
    {
        $this->di = $di;
        $this->config = $di->get('config');
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getEventPriorities()
    {
        return array();
    }
}