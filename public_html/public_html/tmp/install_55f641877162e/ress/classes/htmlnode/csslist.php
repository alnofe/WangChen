<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

class Ressio_HTMLNode_CSSList extends HTML_Node
{
    /** @var Ressio_DI */
    public $di;
    /** @var Ressio_Config */
    public $config;

    public $styleList = array();

    /**
     * Class constructor
     * @param Ressio_DI $di
     */
    public function __construct($di)
    {
        parent::__construct('~stylesheet~', null);
        $this->di = $di;
        $this->config = $di->get('config');
    }

    /**
     * @return string
     */
    protected function getHash()
    {
        /** @var IRessio_DeviceDetector $detector */
        $detector = $this->di->get('devicedetector');

        /** @var Ressio_UrlRewriter $urlRewriter */
        $urlRewriter = $this->di->get('urlRewriter');

        /** @var IRessio_Filesystem $fs */
        $fs = $this->di->get('filesystem');

        $hash = array();
        $hash[] = get_class($this->di->get('cssMinify')); // @todo move to combiner
        $hash[] = $detector->vendor();
        $hash[] = json_encode($this->styleList);
        // add file's timestamp to hash
        foreach ($this->styleList as $item) {
            if ($item['type'] !== 'inline') {
                $filename = $urlRewriter->urlToFilepath($item['src']);
                if($urlRewriter->isAbsoluteURL($filename)){
                    $hash[] = '-';
                } else {
                    $hash[] = $fs->getModificationTime($filename);
                }
            }
        }

        // @todo move Ressio_Helper::hash to DI
        return Ressio_Helper::hash(implode('|', $hash), $this->config->filehashsize);
    }

    /**
     * @param string|bool $css
     * @return string
     * @throws Exception
     */
    protected function combinedUrl(&$css)
    {
        $hash = $this->getHash();

        $staticdir = $this->config->webrootpath . $this->config->staticdir;
        $cacheFile = $staticdir . '/' . $hash . '.css';

        /** @var Ressio_UrlRewriter $urlRewriter */
        $urlRewriter = $this->di->get('urlRewriter');

        switch ($this->config->fileloader) {
            case 'php':
                $fileloaderphppath = $this->config->fileloaderphppath;
                $targetUrl = $urlRewriter->filepathToUrl($fileloaderphppath . 'get.php?' . $hash . '.css');
                break;
            default:
                $targetUrl = $urlRewriter->filepathToUrl($cacheFile);
        }

        /** @var IRessio_Filesystem $fs */
        $fs = $this->di->get('filesystem');

        if (!$fs->isFile($cacheFile)) {
            /** @var IRessio_CssCombiner */
            $combiner = $this->di->get('cssCombiner');

            $css = $combiner->combine($this->styleList, $targetUrl);
            $fs->putContents($cacheFile, $css);
            $fs->putContents($cacheFile . '.gz', gzencode($css, 9));
            $cssSize = strlen($css);
        } else {
            $cssSize = $fs->size($cacheFile);
        }

        if ($cssSize <= $this->config->css->inlinelimit) {
            if (!isset($css)) {
                $css = $fs->getContents($cacheFile);
            }
        } else {
            $css = false;
        }

        return $targetUrl;
    }

    /**
     * Returns the node as string
     * @param bool $attributes Print attributes (of child tags)
     * @param bool|int $recursive How many sublevels of childtags to print. True for all.
     * @param bool $content_only Only print text, false will print tags too.
     * @return string
     */
    public function toString($attributes = true, $recursive = true, $content_only = false)
    {
        /** @var string|bool $css */
        $targetUrl = $this->combinedUrl($css);

        if ($css === '') {
            return '';
        }

        if (is_string($css)) {
            $s = '<style' . $this->toString_attributes() . '>'
                . $css
                . '</style>';
        } else {
            /** @var Ressio_UrlRewriter $urlRewriter */
            $urlRewriter = $this->di->get('urlRewriter');

            $this->attributes['rel'] = 'stylesheet';
            $this->attributes['href'] = $urlRewriter->minify($targetUrl);
            $s = '<link' . $this->toString_attributes() . '>';
        }

        return $s;
    }
}
