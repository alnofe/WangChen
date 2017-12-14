<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

class Ressio_FileLock_flock implements IRessio_FileLock
{
    private $locks = array();

    public function __construct()
    {
        register_shutdown_function(array($this, 'shutdown'));
    }

    public function shutdown()
    {
        foreach ($this->locks as $filename => $fp) {
            // remove locked file
            @unlink($filename);
            $this->unlock($filename);
        }
    }

    /**
     * @param $filename string
     * @return bool
     */
    public function lock($filename)
    {
        $lockfile = $filename . '.lock';
        while (true) {
            $fp = @fopen($lockfile, 'rb+');
            if (!$fp) {
                return false;
            }
            if (!flock($fp, LOCK_EX)) {
                fclose($fp);
                return false;
            }

            $fp_stat = fstat($fp);
            $file_stat = stat($lockfile);
            if ($fp_stat['st_ino'] === $file_stat['st_ino']) {
                break;
            }

            fclose($fp);
        }
        $this->locks[$filename] = $fp;
        return true;
    }

    /**
     * @param $filename string
     * @param $local bool
     * @return bool
     */
    public function isLocked($filename, $local = false)
    {
        if (isset($this->locks[$filename])) {
            return true;
        }
        if ($local) {
            return false;
        }
        $fp = @fopen($filename . '.lock', 'rb+');
        if (!$fp) {
            return false;
        }
        $locked = !flock($fp, LOCK_EX | LOCK_NB);
        if (!$locked) {
            flock($fp, LOCK_UN);
        }
        fclose($fp);
        return $locked;
    }

    /**
     * @param $filename string
     * @return bool
     */
    public function unlock($filename)
    {
        if (!isset($this->locks[$filename])) {
            return false;
        }
        $fp = $this->locks[$filename];
        unlink($filename . '.lock');
        flock($fp, LOCK_UN);
        fclose($fp);
        unset($this->locks[$filename]);
        return true;
    }
}
