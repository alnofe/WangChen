<?php

/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */
class Ressio_DI
{
    private $di = array();

    /**
     * @param string $key
     * @param string|array|object $value
     */
    public function set($key, $value)
    {
        $key = strtolower($key);

        $this->di[$key] = $value;
    }

    /**
     * @param string $key
     * @return object|null
     * @throws Exception
     */
    public function get($key)
    {
        $key = strtolower($key);

        if (!isset($this->di[$key])) {
            throw new Exception('Unknown key in Ressio_DI::get: ' . $key);
        }

        $value = $this->di[$key];

        if (is_object($value) || $value === null) {
            return $value;
        }

        /** @var object|null $result */
        $result = null;

        /** @var string $className */
        /** @var string $methodName */
        if (is_string($value)) {
            if (strpos($value, ':') === false) {
                // "classname"
                $className = $value;
                $result = new $className();
            } else {
                // "classname:methodname"
                list($className, $methodName) = explode(':', $value, 2);
                $result = call_user_func(array($className, $methodName));
            }
        } elseif (is_array($value)) {
            if (is_string($value[0])) {
                $className = $value[0];
                $params = isset($value[1]) ? (array)$value[1] : array();
                if (strpos($className, ':') === false) {
                    // array("classname", (array)options)
                    $reflect = new ReflectionClass($className);
                    $result = $reflect->newInstanceArgs($params);
                } else {
                    // array("classname:methodname", (array)options)
                    list($className, $methodName) = explode(':', $className, 2);
                    $result = call_user_func_array(array($className, $methodName), $params);
                }
            } elseif (is_object($value[0])) {
                // array($obj, "methodname" [, (array)option])
                /** @var object $obj */
                $obj = $value[0];
                $methodName = $value[1];
                $params = isset($value[2]) ? (array)$value[2] : array();
                $result = call_user_func_array(array($obj, $methodName), $params);
            }
        }

        if ($result && method_exists($result, 'setDI')) {
            $result->setDI($this);
        }

        $this->di[$key] = $result;
        return $result;
    }
}