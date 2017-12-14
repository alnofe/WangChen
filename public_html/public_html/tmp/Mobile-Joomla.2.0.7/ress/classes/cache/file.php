<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

class Ressio_Cache_File implements IRessio_Cache
{
    /** @var Ressio_DI */
    private $di;
    /** @var IRessio_FileLock */
    private $filelock;

    private $cachedir = '';

    /**
     * @param $dir string
     */
    public function __construct($dir = './cache')
    {
        $this->cachedir = $dir;
    }

    /**
     * @param $di Ressio_DI
     */
    public function setDI($di)
    {
        $this->di = $di;
        $this->filelock = $di->get('fileLock');
        $config = $di->get('config');
        if (isset($config->cachedir)) {
            $this->cachedir = $config->cachedir;
        }
    }

    /**
     * @param $deps string|array
     * @return string
     */
    public function id($deps)
    {
        if (is_array($deps)) {
            $deps = implode("\0", $deps);
        }
        return sha1($deps);
    }

    /**
     * @param $id string
     * @return string|bool
     */
    public function getOrLock($id)
    {
        $filename = $this->fileById($id);
        if (file_exists($filename)) {
            return file_get_contents($filename);
        } else {
            return $this->filelock->lock($filename);
        }
    }

    /**
     * @param $id string
     * @param $data string
     * @return bool
     */
    public function storeAndUnlock($id, $data)
    {
        $filename = $this->fileById($id);
        if ($this->filelock->isLocked($filename, true)) {
            $tmpfilename = $filename . '.tmp';
            file_put_contents($tmpfilename, $data);
            if (!rename($tmpfilename, $filename)) {
                copy($tmpfilename, $filename);
                unlink($tmpfilename);
            }
            $this->filelock->unlock($filename);
            return true;
        }
        return false;
    }

    /**
     * @param $id string
     * @return bool
     */
    public function delete($id)
    {
        $filename = $this->fileById($id);
        if (!$this->filelock->lock($filename)) {
            return false;
        }
        unlink($filename);
        $this->filelock->unlock($filename);
        return true;
    }

    /**
     * @param $id string
     * @return string
     */
    private function fileById($id)
    {
        return $this->cachedir . '/' . $id;
    }
}

