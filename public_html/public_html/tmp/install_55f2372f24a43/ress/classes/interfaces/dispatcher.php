<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

interface IRessio_Dispatcher
{
    const ORDER_FIRST = -5;
    const ORDER_STANDARD = 0;
    const ORDER_LAST = 5;

    /**
     * @param $eventNames array|string
     * @param $callableObj array|string
     * @param $priority int
     * @throws Exception
     */
    public function addListener($eventNames, $callableObj, $priority = self::ORDER_STANDARD);

    /**
     * @param $eventNames array|string
     * @param $callableObj array|string
     * @throws Exception
     */
    public function removeListener($eventNames, $callableObj);

    /**
     * @param $eventNames array|string
     * @throws Exception
     */
    public function clearListeners($eventNames);

    /**
     * @param $eventName string
     * @param $params array
     * @throws Exception
     */
    public function triggerEvent($eventName, $params = array());
}