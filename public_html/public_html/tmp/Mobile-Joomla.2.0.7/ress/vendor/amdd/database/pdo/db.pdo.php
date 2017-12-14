<?php

/**
 * Advanced Mobile Device Detection
 *
 * @version        2.0.7
 * @license        GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright    (C) 2008-2015 Mobile Joomla!
 * @date        September 2015
 */
class AmddDatabasePDO extends AmddDatabase
{
    /** @var PDO */
    private $resource;
    private $sql;

    private $dbHost;
    private $dbUser;
    private $dbPassword;
    private $dbDatabase;
    private $dbTableName;

    private $dbDriver;
    private $dbDriverOptions;

    private $table;
    private $tableCache;

    public function __construct($options)
    {
        if (!class_exists('PDO')) {
            throw new AmddDatabaseException('The "pdo" extension is not available', 1);
        }
        $this->dbDriver = $options['dbDriver'];
        $this->dbDriverOptions = $options['dbDriverOptions'];

        $this->dbHost = $options['dbHost'];
        $this->dbUser = $options['dbUser'];
        $this->dbPassword = $options['dbPassword'];
        $this->dbDatabase = $options['dbDatabase'];
        $this->dbTableName = $options['dbTableName'];

        $this->connect();
        $this->table = $this->dbTableName;
        $this->tableCache = $this->dbTableName . '_cache';
    }

    public function getDevice($ua)
    {
        $query = "SELECT `data` FROM `{$this->table}` WHERE `ua`=" . $this->Quote($ua);
        $this->setQuery($query);
        return $this->loadResult();
    }

    public function getDevices($group)
    {
        $query = "SELECT `ua`, `data` FROM `{$this->table}` WHERE `group`=" . $this->Quote($group);
        $this->setQuery($query);
        return $this->loadObjectList();
    }

    public function getDeviceFromCache($ua)
    {
        $query = "SELECT `data` FROM `{$this->tableCache}` WHERE `ua`=" . $this->Quote($ua);
        $this->setQuery($query);
        $data = $this->loadResult();

        if ($data !== null) {
            $query = "UPDATE `{$this->tableCache}` SET time=" . time() . " WHERE `ua`=" . $this->Quote($ua);
            $this->setQuery($query);
            $this->query();
        }

        return $data;
    }

    public function putDeviceToCache($ua, $data, $limit = 0)
    {
        if ($limit >= 0) {
            $query = "SELECT COUNT(*) FROM `{$this->tableCache}`";
            $this->setQuery($query);
            $cacheSize = $this->loadResult();

            if ($cacheSize > $limit) {
                // @todo simplify, maybe DELETE FROM ... ORDER BY time LIMIT 1, $cacheSize-$limit
                // @todo check that all DBs support LIMIT
                $query = "DELETE FROM `{$this->tableCache}` WHERE `time` <="
                    . " (SELECT `time` FROM"
                    . "   (SELECT `time` FROM `{$this->tableCache}` ORDER BY `time` DESC LIMIT $limit, 1)"
                    . " foo)";
                $this->setQuery($query);
                $this->query();
            }
        }

        if ($limit !== 0) {
            $x_ua = $this->Quote($ua);
            $x_data = $this->Quote($data);
            $x_time = time();
            // @todo Does DBs supports ON DUPLICATE KEY UPDATE?
            $query = "INSERT INTO `{$this->tableCache}` (`ua`, `data`, `time`)"
                . " VALUES ($x_ua, $x_data, $x_time)"
                . " ON DUPLICATE KEY UPDATE `data`=$x_data, `time`=$x_time";
            $this->setQuery($query);
            $this->query();
        }
    }

    public function clearCache()
    {
        //@todo TRUNCATE or TRUNCATE TABLE???
        $query = "TRUNCATE `{$this->tableCache}`";
        $this->setQuery($query);
        $this->query();
    }

    private function connect()
    {
        try {
            $this->resource = new PDO($this->dbDriver, $this->dbUser, $this->dbPassword, $this->dbDriverOptions);
        } catch (PDOException $e) {
            throw new AmddDatabaseException('Could not connect to PDO: ' . $e->getMessage(), 2, $e);
        }

        if (!$this->resource) {
            throw new AmddDatabaseException('Could not connect to PDO', 2);
        }
    }

    private function setQuery($query)
    {
        $this->sql = $query;
    }

    private function Quote($text)
    {
        if (is_int($text) || is_float($text)) {
            return $text;
        }
        $text = str_replace("'", "''", $text);
        return "'" . addcslashes($text, "\000\n\r\\\032") . "'";
    }

    /**
     * @return PDOStatement
     * @throws AmddDatabaseException
     */
    private function query()
    {
        if (!is_object($this->resource)) {
            return false;
        }
        $sql = $this->sql;

        $cursor = $this->resource->prepare($sql);

        if (!$cursor->execute()) {
            $errorNum = (int)$this->resource->errorCode();
            // @todo: check connection
            if (2006 === $errorNum || 2013 === $errorNum) {
                $this->connect();
                if ($this->resource) {
                    $cursor = $this->resource->prepare($sql);
                    if ($cursor->execute()) {
                        return $cursor;
                    }
                }
            }
            throw new AmddDatabaseException(implode(', ', $this->resource->errorInfo()) . " SQL=$sql", $this->resource->errorCode());
        }
        return $cursor;
    }

    private function loadResult()
    {
        if (!($cur = $this->query())) {
            return null;
        }
        $ret = null;
        $row = $cur->fetch(PDO::FETCH_NUM);
        if ($row) {
            $ret = $row[0];
        }
        $cur->closeCursor();
        return $ret;
    }

    private function loadObjectList($key = '')
    {
        if (!($cur = $this->query())) {
            return null;
        }
        $array = array();
        while ($row = $cur->fetchObject('stdClass')) {
            if ($key) {
                $array[$row->$key] = $row;
            } else {
                $array[] = $row;
            }
        }
        $cur->closeCursor();
        return $array;
    }

    /**
     * @param array $queries
     * @throws AmddDatabaseException
     */
    private function batchQueries($queries)
    {
        foreach ($queries as $query) {
            $this->setQuery($query);
            $this->query();
        }
    }

    public function updateDatabase($stream)
    {
        $amdd_prefix = $this->dbTableName;

        $this->batchQueries(array(
            "DROP TABLE IF EXISTS `{$amdd_prefix}_tmp`",

            "CREATE TABLE `{$amdd_prefix}_tmp` ("
            . "  `ua` varchar(255) collate utf8_bin NOT NULL,"
            . "  `group` varchar(32) collate utf8_bin NOT NULL,"
            . "  `data` varchar(255) collate utf8_bin NOT NULL,"
            . "  UNIQUE KEY `ua` (`ua`),"
            . "  KEY `group` (`group`)"
            . ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

            "ALTER TABLE `{$amdd_prefix}_tmp` DISABLE KEYS"
        ));

        $insert_sql_head = "INSERT INTO `{$amdd_prefix}_tmp` VALUES ";
        $insert_sql = '';

        while (!feof($stream)) {
            $ua = rtrim(fgets($stream));
            $group = rtrim(fgets($stream));
            $data = rtrim(fgets($stream));
            $empty = rtrim(fgets($stream));
            if ($ua === '' || $data === '' || !empty($empty)) {
                break;
            }

            if ($insert_sql !== '') {
                $insert_sql .= ',';
            }
            $insert_sql .= '('
                . $this->Quote($ua) . ','
                . $this->Quote($group) . ','
                . $this->Quote($data)
                . ')';

            if (strlen($insert_sql) > 50000) {
                $query = $insert_sql_head . $insert_sql;
                $this->setQuery($query);
                $this->query();
                $insert_sql = '';
            }
        }

        if ($insert_sql !== '') {
            $query = $insert_sql_head . $insert_sql;
            $this->setQuery($query);
            $this->query();
        }

        $this->batchQueries(array(
            "ALTER TABLE `{$amdd_prefix}_tmp` ENABLE KEYS",

            "CREATE TABLE IF NOT EXISTS `{$amdd_prefix}_cache` ("
            . "  `ua` varchar(255) collate utf8_bin NOT NULL,"
            . "  `data` varchar(255) collate utf8_bin NOT NULL,"
            . "  `time` int(10) unsigned NOT NULL"
            . ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

            "CREATE TABLE IF NOT EXISTS `{$amdd_prefix}` ("
            . "  `dummy` int"
            . ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin",

            "RENAME TABLE `{$amdd_prefix}` TO `{$amdd_prefix}_old`, `{$amdd_prefix}_tmp` TO `{$amdd_prefix}`;",

            "TRUNCATE TABLE `{$amdd_prefix}_cache`",

            "DROP TABLE `{$amdd_prefix}_old`"
        ));
    }

    public function checkDatabase()
    {
        $this->setQuery("SHOW TABLES LIKE `{$this->dbTableName}``");
        return ($this->loadResult() !== null);
    }

    public function dropDatabase()
    {
        $this->setQuery("DROP TABLE `{$this->table}`, `{$this->tableCache}`");
        $this->query();
    }
}