<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

/**
 * No CSS minification
 */
class Ressio_CssMinify_None implements IRessio_CssMinify
{
    /** @var  Ressio_DI */
    public $di;

    public $srcBase;
    public $targetBase;

    public function setDI($di)
    {
        $this->di = $di;
    }

    /**
     * Minify CSS
     * @param string $str
     * @param string $srcBase
     * @param string $targetBase
     * @return string
     */
    public function minify($str, $srcBase = null, $targetBase = null)
    {
        if ($srcBase !== $targetBase) {
            $this->srcBase = $srcBase;
            $this->targetBase = $targetBase;

            $str = preg_replace_callback(
                '#url\(\s*([\'"]?)([^)]+?)\1\s*\)#i',
                array($this, 'replaceUrlsCallback'),
                $str
            );
        }
        return $str;
    }

    /**
     * Minify CSS in style=""
     * @param string $str
     * @param string $srcBase
     * @return string
     */
    public function minifyInline($str, $srcBase = null)
    {
        return $str;
    }

    public function replaceUrlsCallback($url)
    {
        if (0 === strncasecmp('data:', $url[2], 5)) {
            return $url[0];
        }

        /** @var Ressio_UrlRewriter $urlRewriter */
        $urlRewriter = $this->di->get('urlRewriter');

        $relurl = $url[2];
        if (strpos($relurl, '://') === false) {
            $relurl = $urlRewriter->getRebasedUrl($relurl, $this->srcBase, $this->targetBase);
        }
        if ($relurl{0} === '/') {
            // prior to PHP 5.3.3: E_WARNING is emitted when URL parsing failed.
            $parsed_url = @parse_url($this->srcBase);
            $absUrl = '';
            $absUrl .= isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
            $absUrl .= isset($parsed_url['host']) ? $parsed_url['host'] : '';
            $absUrl .= isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
            $relurl = $absUrl . $relurl;
        }
        return 'url(' . $relurl . ')';
    }

}