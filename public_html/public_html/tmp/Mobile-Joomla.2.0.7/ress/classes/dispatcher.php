<?php

/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */
class Ressio_Dispatcher implements IRessio_Dispatcher
{
    /** @var Ressio_DI */
    private $di;
    /** @var Ressio_Config */
    public $config;

    /** @var array */
    private $listeners = array();
    /** @var int */
    private $counter = 0;

    /**
     */
    public function __construct()
    {
    }

    /**
     * @param $di Ressio_DI
     */
    public function setDI($di)
    {
        $this->di = $di;
        $this->config = $di->get('config');
    }

    /**
     * @param $eventNames array|string
     * @param $callableObj array|string
     * @param $priority int
     * @throws Exception
     */
    public function addListener($eventNames, $callableObj, $priority = self::ORDER_STANDARD)
    {
        if (is_array($eventNames)) {
            foreach ($eventNames as $eventName) {
                $this->addListener($eventName, $callableObj, $priority);
            }
        } elseif (is_string($eventNames)) {
            $eventName = strtolower($eventNames);
            $this->counter++;
            if (!isset($this->listeners[$eventName])) {
                $this->listeners[$eventName] = array();
            }
            $this->listeners[$eventName][$priority * (1 << 24) + $this->counter] = $callableObj;
        } else {
            throw new Exception('Wrong event name parameter');
        }
    }

    /**
     * @param $eventNames array|string
     * @param $callableObj array|string
     * @throws Exception
     */
    public function removeListener($eventNames, $callableObj)
    {
        if (is_array($eventNames)) {
            foreach ($eventNames as $eventName) {
                $this->removeListener($eventName, $callableObj);
            }
        } elseif (is_string($eventNames)) {
            $eventName = strtolower($eventNames);
            if (is_array($this->listeners[$eventName])) {
                foreach ($this->listeners[$eventName] as $i => $listener) {
                    if ($listener === $callableObj) {
                        unset($this->listeners[$eventName][$i]);
                    }
                }
            }
        } else {
            throw new Exception('Wrong event name parameter');
        }
    }

    /**
     * @param $eventNames array|string
     * @throws Exception
     */
    public function clearListeners($eventNames)
    {
        if (is_array($eventNames)) {
            foreach ($eventNames as $eventName) {
                $this->clearListeners($eventName);
            }
        } elseif (is_string($eventNames)) {
            $eventName = strtolower($eventNames);
            unset($this->listeners[$eventName]);
        } else {
            throw new Exception('Wrong event name parameter');
        }
    }

    /**
     * @param $eventName string
     * @param $params array
     * @throws Exception
     */
    public function triggerEvent($eventName, $params = array())
    {
        $eventName = strtolower($eventName);
        if (isset($this->listeners[$eventName])) {
            $event = new Ressio_Event($eventName);
            array_unshift($params, $event);
            // Trick from http://php.net/manual/en/function.call-user-func-array.php#91503
            $Args = array();
            foreach ($params as $k => &$arg) {
                $Args[$k] = &$arg;
            }
            foreach ($this->listeners[$eventName] as $listener) {
                call_user_func_array($listener, $Args);
                if ($event->isStopped()) {
                    break;
                }
            }
        }
    }
}