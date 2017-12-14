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
defined('_JEXEC') or die();

include_once dirname(__FILE__) . '/classes/mjinstaller.php';

class Com_MobilejoomlaInstallerScript
{
    /**
     * @param string $type
     * @param JInstallerComponent $adapter
     * @return bool
     */
    public function preflight($type, $adapter)
    {
        $path = $adapter->getParent()->getPath('source');
        $xmldest = $path . '/mobilejoomla.xml';
        $xmlsrc = $path . '/mobilejoomla.j2x.xml';
        if (JFile::exists($xmlsrc)) {
            if (JFile::exists($xmldest)) {
                JFile::delete($xmldest);
            }
            JFile::move($xmlsrc, $xmldest);
        }
        $adapter->getParent()->setPath('manifest', $xmldest);
        return true;
    }

    /**
     * @param JInstallerComponent $adapter
     * @return bool
     */
    public function install($adapter)
    {
        return MjInstaller::install();
    }

    /**
     * @param JInstallerComponent $adapter
     * @return bool
     */
    public function update($adapter)
    {
        return MjInstaller::install();
    }

    /**
     * @param JInstallerComponent $adapter
     * @return bool
     */
    public function uninstall($adapter)
    {
        return MjInstaller::uninstall();
    }
}