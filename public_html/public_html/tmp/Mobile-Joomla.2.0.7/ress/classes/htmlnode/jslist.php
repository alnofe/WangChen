<?php

/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */
class Ressio_HTMLNode_JSList extends HTML_Node
{
    /** @var Ressio_DI */
    public $di;
    /** @var Ressio_Config */
    public $config;

    public $scriptList = array();

    /**
     * Class constructor
     * @param Ressio_DI $di
     */
    public function __construct($di)
    {
        parent::__construct('script', null);
        $this->di = $di;
        $this->config = $di->get('config');
    }

    /**
     * @return string
     */
    protected function getHash()
    {
        /** @var Ressio_UrlRewriter $urlRewriter */
        $urlRewriter = $this->di->get('urlRewriter');

        /** @var IRessio_Filesystem $fs */
        $fs = $this->di->get('filesystem');

        $hash = array();
        $hash[] = get_class($this->di->get('jsMinify')); // @todo move to combiner
        $hash[] = json_encode($this->scriptList);
        // add file's timestamp to hash
        foreach ($this->scriptList as $item) {
            if ($item['type'] !== 'inline') {
                $filename = $urlRewriter->urlToFilepath($item['src']);
                if ($filename === null) {
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
     * @param string|bool $js
     * @return string
     * @throws Exception
     */
    protected function combinedUrl(&$js)
    {
        $hash = $this->getHash();

        $staticdir = $this->config->webrootpath . $this->config->staticdir;
        $cacheFile = $staticdir . '/' . $hash . '.js';

        /** @var Ressio_UrlRewriter $urlRewriter */
        $urlRewriter = $this->di->get('urlRewriter');

        switch ($this->config->fileloader) {
            case 'php':
                $fileloaderphppath = $this->config->fileloaderphppath;
                $targetUrl = $urlRewriter->filepathToUrl($fileloaderphppath . 'get.php?' . $hash . '.js');
                break;
            default:
                $targetUrl = $urlRewriter->filepathToUrl($cacheFile);
        }

        /** @var IRessio_Filesystem $fs */
        $fs = $this->di->get('filesystem');

        if (!$fs->isFile($cacheFile)) {
            /** @var IRessio_JsCombiner */
            $combiner = $this->di->get('jsCombiner');

            $js = $combiner->combine($this->scriptList);
            $fs->putContents($cacheFile, $js);
            $fs->putContents($cacheFile . '.gz', gzencode($js, 9));
            $jsSize = strlen($js);
        } else {
            $jsSize = $fs->size($cacheFile);
        }

        if ($jsSize <= $this->config->js->inlinelimit) {
            if (!isset($js)) {
                $js = $fs->getContents($cacheFile);
            }
        } else {
            $js = false;
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
        /** @var string|bool $js */
        $targetUrl = $this->combinedUrl($js);

        if ($js === '') {
            return '';
        }

        if (is_string($js)) {
            unset($this->attributes['async'], $this->attributes['defer']);
            $s = '<script' . $this->toString_attributes() . '>'
                . $js
                . '</script>';
        } else {
            /** @var Ressio_UrlRewriter $urlRewriter */
            $urlRewriter = $this->di->get('urlRewriter');

            $this->attributes['src'] = $urlRewriter->minify($targetUrl);
            $s = '<script' . $this->toString_attributes() . '></script>';
        }

        return $s;
    }
}
