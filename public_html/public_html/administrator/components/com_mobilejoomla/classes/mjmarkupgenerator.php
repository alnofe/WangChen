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

class MjMarkupGenerator
{
    /** @var MobileJoomla */
    protected $mj;

    /**
     * @param $mj MobileJoomla
     */
    public function __construct($mj)
    {
        $this->mj = $mj;
    }

    public function setHeader()
    {
    }

    public function showFooter()
    {
    }

    public function processPage($text)
    {
        return $text;
    }
}