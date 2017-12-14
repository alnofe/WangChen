<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

/**
 * CSS minification using GD
 */
class Ressio_ImgRescale_Gd implements IRessio_ImgRescale
{
    public $supported_exts = array('jpg', 'gif', 'png', 'wbmp');
    //public $thumbdir = 'imgcache';

    /** @var Ressio_DI */
    private $di;

    public function __construct()
    {
        // Support of WebP images in PHP 5.5+
        if (function_exists('imagecreatefromwebp') && function_exists('imagewebp')) {
            $this->supported_exts[] = 'webp';
        }
    }

    /**
     * @param Ressio_DI $di
     */
    public function setDI($di)
    {
        $this->di = $di;
    }

    /**
     * @return array
     */
    public function getSupportedExts()
    {
        return $this->supported_exts;
    }

    /**
     * Rescale Image
     * @param string $src_imagepath
     * @param string $dest_imagepath
     * @param int $dest_width
     * @param int $dest_height
     * @param string|bool $dest_ext
     * @return string
     */
    public function rescale($src_imagepath, $dest_imagepath, $dest_width, $dest_height, $dest_ext = false)
    {
        $jpegquality = $this->di->get('config')->img->jpegquality;
        $src_ext = strtolower(pathinfo($src_imagepath, PATHINFO_EXTENSION));
        if ($src_ext === 'jpeg') {
            $src_ext = 'jpg';
        }

        if (!in_array($src_ext, $this->supported_exts, true)) {
            return $src_imagepath;
        }

        /** @var IRessio_Filesystem $fs */
        $fs = $this->di->get('filesystem');

        $src_mtime = $fs->getModificationTime($src_imagepath);
        if ($fs->isFile($dest_imagepath)) {
            $dest_mtime = $fs->getModificationTime($dest_imagepath);
            if ($src_mtime === $dest_mtime) {
                return $dest_imagepath;
            }
        }

        $dest_imagedir = dirname($dest_imagepath);
        if (!$fs->isDir($dest_imagedir)) {
            $fs->makeDir($dest_imagedir);
            $indexhtml = '<html><body bgcolor="#FFFFFF"></body></html>';
            $fs->putContents($dest_imagedir . '/index.html', $indexhtml);
        }

        if (!$fs->copy($src_imagepath, $dest_imagepath)) {
            return $src_imagepath;
        }

        list($src_width, $src_height) = getimagesize($src_imagepath);

        $src_image = false;
        switch ($src_ext) {
            case 'jpg':
                $src_image = ImageCreateFromJPEG($dest_imagepath);
                break;
            case 'gif':
                $content = $fs->getContents($dest_imagepath);
                if ($this->is_gif_ani($content)) {
                    return $src_imagepath;
                }
                $src_image = ImageCreateFromString($content);
                unset($content);
                break;
            case 'wbmp':
                $src_image = ImageCreateFromWBMP($dest_imagepath);
                break;
            case 'png':
                $src_image = ImageCreateFromPNG($dest_imagepath);
                break;
            case 'webp':
                $src_image = ImageCreateFromWebP($dest_imagepath);
                break;
        }
        $fs->delete($dest_imagepath);

        if ($src_image === false) {
            return $src_imagepath;
        }

        $dest_image = ImageCreateTrueColor($dest_width, $dest_height);

        //Additional operations to preserve transparency on images
        switch ($dest_ext) {
            case 'png':
            case 'gif':
            case 'webp':
                ImageAlphaBlending($dest_image, false);
                $color = ImageColorTransparent($dest_image, ImageColorAllocateAlpha($dest_image, 0, 0, 0, 127));
                ImageFilledRectangle($dest_image, 0, 0, $dest_width, $dest_height, $color);
                ImageSaveAlpha($dest_image, true);
                break;
            default:
                $color = ImageColorAllocate($dest_image, 255, 255, 255);
                ImageFilledRectangle($dest_image, 0, 0, $dest_width, $dest_height, $color);
                break;
        }

        if (function_exists('imagecopyresampled')) {
            $ret = ImageCopyResampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
        } else {
            $ret = ImageCopyResized($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
        }
        if (!$ret) {
            ImageDestroy($src_image);
            ImageDestroy($dest_image);
            return $src_imagepath;
        }
        ImageDestroy($src_image);

        ob_start();
        switch ($dest_ext) {
            case 'jpg':
                ImageJPEG($dest_image, null, $jpegquality);
                $data = ob_get_contents();
                $data = $this->jpeg_clean($data);
                if ($data !== false) {
                    ob_clean();
                    echo $data;
                }
                break;
            case 'gif':
                ImageTrueColorToPalette($dest_image, true, 256);
                ImageGIF($dest_image);
                break;
            case 'wbmp':
                // Floyd-Steinberg dithering
                $black = ImageColorAllocate($dest_image, 0, 0, 0);
                $white = ImageColorAllocate($dest_image, 255, 255, 255);
                $next_err = array_fill(0, $dest_width, 0);
                for ($y = 0; $y < $dest_height; $y++) {
                    $cur_err = $next_err;
                    $next_err = array(-1 => 0, 0 => 0);
                    for ($x = 0, $err = 0; $x < $dest_width; $x++) {
                        $rgb = ImageColorAt($dest_image, $x, $y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        $color = $err + $cur_err[$x] + 0.299 * $r + 0.587 * $g + 0.114 * $b;
                        if ($color >= 128) {
                            ImageSetPixel($dest_image, $x, $y, $white);
                            $err = $color - 255;
                        } else {
                            ImageSetPixel($dest_image, $x, $y, $black);
                            $err = $color;
                        }
                        $next_err[$x - 1] += $err * 3 / 16;
                        $next_err[$x] += $err * 5 / 16;
                        $next_err[$x + 1] = $err / 16;
                        $err *= 7 / 16;
                    }
                }
                ImageWBMP($dest_image);
                break;
            case 'png':
                if (version_compare(PHP_VERSION, '5.1.3', '>=')) {
                    ImagePNG($dest_image, null, 9, PNG_ALL_FILTERS);
                } elseif (version_compare(PHP_VERSION, '5.1.2', '>=')) {
                    ImagePNG($dest_image, null, 9);
                } else {
                    ImagePNG($dest_image);
                }
                break;
            case 'webp':
                ImageWebP($dest_image);
                break;
        }
        $data = ob_get_contents();
        ob_end_clean();
        ImageDestroy($dest_image);
        $fs->putContents($dest_imagepath, $data);
        $fs->touch($dest_imagepath, $src_mtime);

        return $dest_imagepath;
    }

    /**
     * Remove JFIF and Comment headers from GD2-generated jpeg (saves 79 bytes)
     * @param string $jpeg_src
     * @return bool|string
     */
    private function jpeg_clean($jpeg_src)
    {
        $jpeg_clr = "\xFF\xD8";
        if (substr($jpeg_src, 0, 2) !== $jpeg_clr) {
            return false;
        }
        $pos = 2;
        $size = strlen($jpeg_src);
        while ($pos < $size) {
            if ($jpeg_src{$pos} !== "\xFF") {
                return false;
            }
            $b = $jpeg_src{$pos + 1};
            if ($b === "\xDA") {
                return $jpeg_clr . substr($jpeg_src, $pos);
            }
            $len = unpack('n', substr($jpeg_src, $pos + 2, 2));
            $len = array_shift($len);
            if ($b !== "\xE0" && $b !== "\xFE") {
                $jpeg_clr .= substr($jpeg_src, $pos, $len + 2);
            }
            $pos += $len + 2;
        }
        return false;
    }

    /**
     * Count animation frames in gif file, return TRUE if two or more
     * @param string $content
     * @return bool
     */
    private function is_gif_ani($content)
    {
        $count = preg_match_all('#\x00\x21\xF9\x04.{4}\x00(?:\x2C|\x21)#s', $content, $matches);
        return $count > 1;
    }

}