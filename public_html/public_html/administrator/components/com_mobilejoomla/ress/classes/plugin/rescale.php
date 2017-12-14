<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

class Ressio_Plugin_Rescale extends Ressio_Plugin
{
    /**
     * @param $event Ressio_Event
     * @param $optimizer IRessio_HtmlOptimizer
     * @param $node HTML_Node
     */
    public function onHtmlIterateTagImg($event, $optimizer, $node)
    {
        if ($node->parent === null) {
            return;
        }

        // @todo: parse srcset attribute
        if (isset($node->attributes['src']) && $this->config->img->rescale) {
            if ($this->config->img->scaletype === "remove") {
                $node->detach();
            } else {
                // @todo move all urlRewriter-related code here
                $this->imageRescale($node);
            }
        }
    }

    /**
     * @param $node HTML_Node
     */
    private function imageRescale(&$node)
    {
        // todo: rescaled img width should be multiple of 16
        //       (to avoid possible incorrect sizes in device database)

        /** @var IRessio_ImgRescale $rescaler */
        $rescaler = $this->di->get('imgRescaler');
        /** @var IRessio_DeviceDetector $device */
        $device = $this->di->get('deviceDetector');

        if ($rescaler && $device) {
            $forced_width = 0;
            $forced_height = 0;
            if (isset($node->attributes['style'])) {
                $style = $node->attributes['style'];
                if (preg_match('#(?:^|\s|;|\{)width\s*:\s*(\d+)px#', $style, $matches)) {
                    $forced_width = (int) $matches[1];
                }
                if (preg_match('#(?:^|\s|;|\{)height\s*:\s*(\d+)px#', $style, $matches)) {
                    $forced_height = (int) $matches[1];
                }
            }
            if (isset($node->attributes['width'])) {
                $forced_width = (int) $node->attributes['width'];
            }
            if (isset($node->attributes['height'])) {
                $forced_height = (int) $node->attributes['height'];
            }

            $scaledimage_width = $forced_width;
            $scaledimage_height = $forced_height;

            $src_url = $node->attributes['src'];
            $imageurl = $src_url;

            $src_ext = strtolower(pathinfo($src_url, PATHINFO_EXTENSION));
            if ($src_ext === 'jpeg') {
                $src_ext = 'jpg';
            }

            $dest_imageuri = $src_url;

            if (in_array($src_ext, $rescaler->getSupportedExts(), true)) {
                /** @var Ressio_UrlRewriter $urlRewriter */
                $urlRewriter = $this->di->get('urlRewriter');
                /** @var IRessio_Filesystem $fs */
                $fs = $this->di->get('filesystem');

                $src_imagepath = $urlRewriter->urlToFilepath($imageurl);
                if ($fs->isFile($src_imagepath)) {
                    list($src_width, $src_height) = getimagesize($src_imagepath);
                    if ($src_width > 0 && $src_height > 0) {
                        //$dev_width = $device->screen_width();
                        //$dev_height = $device->_screen_height();
                        // optimize for both portrait & landscape orientation [@todo move to options]
                        $dev_width = $dev_height = max($device->screen_width(), $device->screen_height());

                        $formats = $device->browser_imgformats();
                        if (is_array($formats) && count($formats) > 0 && !empty($formats[0])) {
                            $templateBuffer = $this->config->img->bufferwidth;
                            if (isset($node->attributes['ress-fullwidth'])) {
                                $templateBuffer = 0;
                                unset($node->attributes['ress-fullwidth']);
                            }
                            $dev_width -= $templateBuffer;
                            if ($dev_width < 16) {
                                $dev_width = 16;
                            }

                            if ($forced_width === 0) {
                                if ($forced_height === 0) {
                                    $forced_width = $src_width;
                                    $forced_height = $src_height;
                                } else {
                                    $forced_width = round($src_width * $forced_height / $src_height);
                                    if ($forced_width === 0) {
                                        $forced_width = 1;
                                    }
                                }
                            } elseif ($forced_height === 0) {
                                $forced_height = round($src_height * $forced_width / $src_width);
                                if ($forced_height === 0) {
                                    $forced_height = 1;
                                }
                            }

                            if ($this->config->img->scaletype === "prop") {
                                $scalewidth = $this->config->img->templatewidth;
                                $defscale = $dev_width / $scalewidth;
                            } else {
                                $defscale = 1;
                            }

                            $maxscalex = $dev_width / $forced_width;
                            $maxscaley = $dev_height / $forced_height;
                            $scale = min($defscale, $maxscalex, $maxscaley);
                            $allowedFormat = in_array($src_ext, $formats, true);
                            if ($scale >= 1 && $allowedFormat &&
                                $forced_width === $src_width && $forced_height === $src_height
                            ) {
                                $scaledimage_width = $src_width;
                                $scaledimage_height = $src_height;
                            } else {
                                $dest_width = $scaledimage_width = round($forced_width * $scale);
                                $dest_height = $scaledimage_height = round($forced_height * $scale);
                                if ($dest_width === 0) {
                                    $dest_width = 1;
                                }
                                if ($dest_height === 0) {
                                    $dest_height = 1;
                                }

                                $dpr = $device->screen_dpr();
                                if ($this->config->img->hiresimages && $dpr > 1) {
                                    $dest_width *= $dpr;
                                    $dest_height *= $dpr;
                                    $this->config->img->jpegquality = $this->config->img->hiresjpegquality;
                                }

                                if ($allowedFormat) {
                                    $dest_ext = $src_ext;
                                } else {
                                    $dest_ext = $formats[0];
                                }

                                if (defined('PATHINFO_FILENAME')) {
                                    $src_imagename = pathinfo($imageurl, PATHINFO_FILENAME);
                                } else {
                                    $base = basename($imageurl);
                                    $src_imagename = substr($base, 0, strrpos($base, '.'));
                                }

                                // @todo: move cache dir to settings
                                $dest_imagedir = dirname($src_imagepath) . '/imgcache';
                                $dest_imagepath = $dest_imagedir . '/' . $src_imagename . '_' . $dest_width . 'x' . $dest_height . '.' . $dest_ext;

                                $dest_imagepath = $rescaler->rescale($src_imagepath, $dest_imagepath, $dest_width, $dest_height, $dest_ext);

                                $dest_imageuri = $urlRewriter->filepathToUrl($dest_imagepath);
                            }
                        }
                    }
                }
            }

            if ($this->config->img->setdimension && $scaledimage_width && $scaledimage_height) {
                $node->attributes['width'] = $scaledimage_width;
                $node->attributes['height'] = $scaledimage_height;
            }

            if ($src_url !== $dest_imageuri) {
                if ($this->config->img->keeporig) {
                    $node->attributes['data-orig'] = $src_url;
                }
                $node->attributes['src'] = $dest_imageuri;
            }

            if ($this->config->img->wrapwideimg && !isset($node->attributes['ress-nowrap'])) {
                $screenWidth = $device->screen_width();
                if ($screenWidth > 0 && $scaledimage_width > $screenWidth / 2) {
                    /** @var HTML_Node $node */
                    $node = $node->wrap('span');
                    $node->addClass($this->config->img->wideimgclass);
                }
            } else {
                unset($node->attributes['ress-nowrap']);
            }
        }

    }

}