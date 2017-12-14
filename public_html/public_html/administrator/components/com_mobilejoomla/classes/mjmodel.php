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

abstract class MjModel
{
    /** @var MjJoomlaWrapper */
    protected $joomlaWrapper;

    /**
     * @param $joomlaWrapper MjJoomlaWrapper
     */
    public function __construct($joomlaWrapper)
    {
        $this->joomlaWrapper = $joomlaWrapper;
    }

    /**
     * @param $data array
     * @return boolean
     */
    abstract public function bind($data);

    /**
     * @return boolean
     */
    abstract public function save();
}