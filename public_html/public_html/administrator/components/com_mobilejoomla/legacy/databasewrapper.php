<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.10
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die('Restricted access');

class MjDatabaseWrapper
{
    /** @var JDatabase */
    protected $dbo;
    /** @var  MjDatabaseQuery */
    protected $wrapperSql;

    public function __construct($dbo)
    {
        require_once dirname(__FILE__) . '/databasequery.php';
        $this->dbo = $dbo;
    }

    public function getQuery($new = false)
    {
        if ($new) {
            return new MjDatabaseQuery;
        } else {
            return $this->wrapperSql;
        }
    }

    public function setQuery($query, $offset = 0, $limit = 0)
    {
        $this->wrapperSql = $query;
        $this->dbo->setQuery((string)$query, $offset, $limit);
    }

    public function dropTable($tableName, $ifExists = true)
    {
        $this->dbo->setQuery('DROP TABLE ' . ($ifExists ? 'IF EXISTS ' : '') . $this->dbo->nameQuote($tableName));
        $this->dbo->query();
    }

    public function renameTable($oldTable, $newTable)
    {
        $this->dbo->setQuery('RENAME TABLE ' . $this->dbo->nameQuote($oldTable) . ' TO ' . $this->dbo->nameQuote($newTable));
        $this->dbo->query();
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->dbo, $name), $arguments);
    }
}