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

class MjMarkupGenerator_Mobile extends MjMarkupGenerator
{
    /**
     * @param $mj MobileJoomla
     */
    public function __construct($mj)
    {
        parent::__construct($mj);
    }

    public function getMarkup()
    {
        return 'mobile';
    }

    public function showFooter()
    {
        
    }

    public function processPage($text)
    {
        /*        if ($this->mj->getParam('img') == 1)
                    $text = preg_replace('#<img [^>]+>#is', '', $text);
                elseif ($this->mj->getParam('img') >= 2) {
                    $scaletype = $this->mj->getParam('img') - 2;
                    $addstyles = (bool)$this->mj->getParam('img_addstyles');
        //            $text = $this->mj->RescaleImages($text, $scaletype, $addstyles);
                }*/

        return $text;
    }
}
