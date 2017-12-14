<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

class Ressio_Filesystem_Native implements IRessio_Filesystem
{
    /**
     * Check file exists
     * @param string $filename
     * @return bool
     */
    public function isFile($filename)
    {
        return is_file($filename);
    }

    /**
     * Check directory exists
     * @param string $path
     * @return bool
     */
    public function isDir($path)
    {
        return is_dir($path);
    }

    /**
     * @param string $filename
     * @return integer|bool
     */
    public function size($filename)
    {
        return filesize($filename);
    }

    /**
     * Load content from file
     * @param string $filename
     * @return string
     */
    public function getContents($filename)
    {
        return file_get_contents($filename);
    }

    /**
     * Save content to file
     * @param string $filename
     * @param string $content
     * @return bool
     */
    public function putContents($filename, $content)
    {
        return file_put_contents($filename, $content);
    }

    /**
     * Make directory
     * @param string $path
     * @param int $chmod
     * @return bool
     */
    public function makeDir($path, $chmod = 0777)
    {
        $parent = dirname($path);
        if ($parent !== $path && !$this->isDir($parent)) {
            $this->makeDir($parent, $chmod);
        }
        return mkdir($path, $chmod);
    }

    /**
     * Get file timestamp
     * @param string $path
     * @return int
     */
    public function getModificationTime($path)
    {
        $time = @filemtime($path);
        if (strtolower(substr(PHP_OS, 0, 3)) !== 'win') {
            return $time;
        }
        // fix mtime on Windows
        $fileDST = (date('I', $time) === '1');
        $systemDST = (date('I') === '1');
        if ($fileDST === false && $systemDST === true) {
            return $time + 3600;
        } elseif ($fileDST === true && $systemDST === false) {
            return $time - 3600;
        }
        return $time;
    }

    /**
     * Update file timestamp
     * @param string $filename
     * @param int $time
     * @return bool
     */
    public function touch($filename, $time = null)
    {
        return touch($filename, $time);
    }

    /**
     * Delete file or empty directory
     * @param string $path
     * @return bool
     */
    public function delete($path)
    {
        return unlink($path);
    }

    /**
     * Copy file
     * @param string $src
     * @param string $target
     * @return bool
     */
    public function copy($src, $target)
    {
        return copy($src, $target);
    }
}