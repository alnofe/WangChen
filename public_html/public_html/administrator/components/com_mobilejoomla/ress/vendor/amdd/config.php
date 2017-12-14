<?php
/**
 * Advanced Mobile Device Detection
 *
 * @version        2.0.10
 * @license        GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright    (C) 2008-2015 Mobile Joomla!
 * @date        September 2015
 */

class AmddConfig
{
    /* Preinstalled handlers:
       plaintext - store AMDD in plain text files
       pdo       - store AMDD in SQL database using PDO
       mysqli    - store AMDD in MySQL database
       joomla    - integration with Joomla!CMS (store AMDD in Joomla's database)
    */
    public static $handler = 'plaintext';
    public static $cacheSize = 1000;

    /** Plaintext */
    public static $dbPath;

    /** MySQLi / other DBs */
    public static $dbUser = '';
    public static $dbPassword = '';
    public static $dbHost = 'localhost';
    public static $dbDatabase = '';
    public static $dbTableName = 'amdd';

    /** PDO */
    public static $dbDriver = 'pgsql:host=localhost;port=5432;dbname=';
    public static $dbDriverOptions = array();
}

/** PlaintextConfig */
AmddConfig::$dbPath = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'devices';
