<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

// @todo Use DI container instead of constants
if (!defined('RESSIO_PATH')) {
    define('RESSIO_PATH', dirname(__FILE__));
}
if (!defined('RESSIO_LIBS')) {
    define('RESSIO_LIBS', RESSIO_PATH . DIRECTORY_SEPARATOR . 'vendor');
}

class Ressio
{
    /** @var Ressio_DI */
    public $di;
    /** @var Ressio_Config */
    public $config;

    /**
     * @param array $override_config
     */
    public function __construct($override_config = null)
    {
        if (!isset($override_config['disable_autoload']) || !$override_config['disable_autoload']) {
            if (function_exists('__autoload')) {
                spl_autoload_register('__autoload');
            }
            spl_autoload_register(array(__CLASS__, 'autoloader'));
        }

        $this->di = new Ressio_DI();

        /** @var Ressio_Config $config */
        $config = self::loadConfig($override_config);

        $this->config = $config;
        $this->di->set('config', $config);

        if (isset($config->di)) {
            /** @var array $config->di */
            foreach ($config->di as $key => $call) {
                $this->di->set($key, $call);
            }
        }

        if (isset($config->plugins)) {
            $dispatcher = $this->di->get('dispatcher');
            foreach ($config->plugins as $pluginClassname => $options) {
                /** @var Ressio_Plugin $plugin */
                $plugin = new $pluginClassname($this->di, $options);
                $priorities = $plugin->getEventPriorities();
                foreach (get_class_methods($plugin) as $method) {
                    /** @var string $method */
                    if (substr($method, 0, 2) === 'on') {
                        $eventName = strtolower(substr($method, 2));
                        $priority = isset($priorities[$eventName]) ? $priorities[$eventName] : 0;
                        $dispatcher->addListener($eventName, array($plugin, $method), $priority);
                    }
                }
            }
        }
    }

    /**
     * @param array $override_config
     * @return Ressio_Config
     */
    public static function loadConfig($override_config = null)
    {
        // @todo Merge config_base.php and config_user.php

        /** @var Ressio_Config $config */
        $config = new stdClass;
        self::merge_objects($config, include(RESSIO_PATH . '/config.php'));

        if ($override_config !== null) {
            self::merge_objects($config, $override_config);
        }

        if (empty($config->webrootpath)) {
            $config->webrootpath = substr($_SERVER['SCRIPT_FILENAME'], 0, -strlen($_SERVER['SCRIPT_NAME']));
        }
        if (DIRECTORY_SEPARATOR !== '/') {
            $config->webrootpath = str_replace('/', DIRECTORY_SEPARATOR, $config->webrootpath);
        }
        if (substr($config->staticdir, 0, 2) === './') {
            $ress_uri = str_replace(DIRECTORY_SEPARATOR, '/', substr(RESSIO_PATH, strlen($config->webrootpath)));
            $config->staticdir = $ress_uri . substr($config->staticdir, 1);
        }

        if (substr($config->cachepath, 0, 2) === './') {
            $config->cachepath = RESSIO_PATH . substr($config->cachepath, 1);
        }
        if (substr($config->fileloaderphppath, 0, 2) === './') {
            $config->fileloaderphppath = RESSIO_PATH . substr($config->fileloaderphppath, 1);
        }
        if (substr($config->amdd->dbPath, 0, 2) === './') {
            $config->amdd->dbPath = RESSIO_PATH . substr($config->amdd->dbPath, 1);
        }

        return $config;
    }

    private static function merge_objects(&$obj, $obj2)
    {
        foreach ($obj2 as $key => $value) {
            if (is_array($value) || is_object($value)) {
                if (!isset($obj->$key)) {
                    $obj->$key = new stdClass;
                }
                self::merge_objects($obj->$key, $value);
            } else {
                $obj->$key = $value;
            }
        }
    }

    /**
     * @param string $class
     */
    static public function autoloader($class)
    {
        // remove possible namespace prefix
        if ($class{0} === '\\') {
            $class = substr($class, 1);
        }

        $isInterface = ($class{0} === 'I');
        $prefix = $isInterface ? 'IRessio_' : 'Ressio_';

        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $dir = $isInterface ? '/classes/interfaces/' : '/classes/';
        $path = RESSIO_PATH . $dir . str_replace('_', '/', strtolower(substr($class, strlen($prefix)))) . '.php';
        if (file_exists($path)) {
            include_once($path);
        }
    }

    /**
     * @param $buffer string
     * @return string
     */
    public function ob_callback($buffer)
    {
        // disable any output in ob handler
        $display_errors = ini_get('display_errors');
        ini_set('display_errors', 0);

        $buffer = Ressio_Helper::removeBOM($buffer);
        $result = $this->run($buffer);

        ini_set('display_errors', $display_errors);
        return $result;
    }

    /**
     * @param $buffer string
     * @return string
     */
    public function run($buffer)
    {
        try {
            $buffer = Ressio_Helper::removeBOM($buffer);

            /** @var IRessio_HtmlOptimizer $optimizer */
            $optimizer = $this->di->get('htmlOptimizer');
            //@todo preoptimize event
            $buffer = $optimizer->run($buffer);
            //@todo postoptimize event

            if ($this->config->html->gzlevel) {
                //@todo move Ressio_CompressOutput to DI
                Ressio_CompressOutput::init($this->config->html->gzlevel, false);
                $buffer = Ressio_CompressOutput::compress($buffer);
            }

            //@todo presend header event
            //@todo Use DI for sendHeaders to simplify intergation with 3rdparty frameworks
            Ressio_Helper::sendHeaders();

            return $buffer;
        } catch (Exception $e) {
            error_log('Catched error in Ressio::run: ' . $e->getMessage());
            return $buffer;
        }
    }

}
