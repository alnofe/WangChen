<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.3
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die('Restricted access');

$mjSettings->set('enabled', 1);

$configfile = JPATH_ADMINISTRATOR . '/components/com_mobilejoomla/config.php';
if (is_file($configfile)) {
    include($configfile);
    /** @var $MobileJoomla_Settings array */

    $map = array(
        'caching' => 'caching',
        'httpcaching' => 'httpcaching',
        'jpegquality' => 'jpegquality',
        'mobile_sitename' => 'mobile_sitename',
        'global.removetags' => '.removetags',
        'global.img' => '.img',
        'global.img_addstyles' => '.img_addstyles',
        'global.homepage' => '.homepage',
//        'global.componenthome' => '.componenthome',
//        'global.gzip' => '.gzip',
        'xhtml.homepage' => 'mobile.homepage',
//        'xhtml.gzip' => 'mobile.gzip',
        'xhtml.domain' => 'mobile.domain',
//        'xhtml.componenthome' => 'mobile.componenthome',
        'xhtml.jfooter' => 'mobile.jfooter',
        'xhtml.removetags' => 'mobile.removetags',
        'xhtml.img' => 'mobile.img',
        'xhtml.img_addstyles' => 'mobile.img_addstyles'
    );
    foreach ($map as $old => $new) {
        if (isset($MobileJoomla_Settings[$old])) {
            $mjSettings->set($new, $MobileJoomla_Settings[$old]);
        }
    }

    if (!in_array($MobileJoomla_Settings['xhtml.template'],
        array('mobile_iphone', 'mobile_smartphone', 'mobile_imode', 'mobile_wap'))
    ) {
        $mjSettings->set('mobile.template', MjInstaller::getTemplateStyleId($MobileJoomla_Settings['xhtml.template']));
        $mjSettings->set('mobile.buffer_width', $MobileJoomla_Settings['xhtml.buffer_width']);
    }

    if (isset($MobileJoomla_Settings['global.img'])) {
        $mjSettings->set('.img', ($MobileJoomla_Settings['global.img'] > 1) ? 1 : 0);
    }

    if (isset($MobileJoomla_Settings['xhtml.img']) && $MobileJoomla_Settings['xhtml.img'] !== '') {
        $mjSettings->set('mobile.img', ($MobileJoomla_Settings['xhtml.img'] > 1) ? 1 : 0);
    }

    MjInstaller::UninstallPlugin('system', 'mobilebot');

    $db = JFactory::getDbo();

    try {
        // update Select Markup modules
        $query = "UPDATE `#__modules` SET `module`='mod_mj_switcher' WHERE `module`='mod_mj_markupchooser'";
        $db->setQuery($query);
        $db->query();
    } catch (Exception $e) {
    }
    MjInstaller::UninstallModule('mod_mj_markupchooser');

    // @todo replace mod_mj_menu modules by mod_menu one
    //MjInstaller::UninstallModule('mod_mj_menu');

    // relocate modules from old positions
    $prefix = ($MobileJoomla_Settings['xhtml.template'] === 'mobile_iphone') ? 'mj_iphone_' : 'mj_smartphone_';
    try {
        $positions = array(
            'mj_top' => array('mj_all_header'),
            'mj_top2' => array($prefix . 'header'),
            'mj_top3' => array($prefix . 'header2'),
            'mj_middle' => array('mj_all_middle', $prefix . 'middle'),
            'mj_middle2' => array($prefix . 'middle2'),
            'mj_footer' => array('mj_all_footer', $prefix . 'footer'),
            'mj_footer2' => array($prefix . 'footer2')
        );

        foreach ($positions as $newname => $oldnames) {
            $query = "UPDATE `#__modules` SET `position`='$newname' WHERE `position` IN ('" . implode("', '", $oldnames) . "')";
            $db->setQuery($query);
            $db->query();
        }
    } catch (Exception $e) {
    }

    // update from MJ 2.0.alpha
    try {
        $query = "ALTER TABLE `#__mj_modules` CHANGE `markup` `device` varchar(32) NOT NULL";
        $db->setQuery($query);
        $db->query();

        $query = "ALTER TABLE `#__mj_plugins` CHANGE `markup` `device` varchar(32) NOT NULL";
        $db->setQuery($query);
        $db->query();
    } catch (Exception $e) {
    }

    // @todo release old templates with hardcoded template position names (or custome in template's settings)
    // @todo release detecting plugin with support of old modes
}
