<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

/**
 * Stub class Ressio_Config for IDE autocomplete
 */

class Ressio_Config
{
    /** @var bool */
    public $autostart;

    /** @var string */
    public $webrootpath;
    /** @var string */
    public $webrooturi;
    /** @var string */
    public $staticdir;
    /** @var string */
    public $cachepath;

    /** @var string ('file'|'php') */
    public $fileloader;
    /** @var string */
    public $fileloaderphppath;
    /** @var int */
    public $filehashsize;

    /** @var Ressio_ConfigHtml */
    public $html;
    /** @var Ressio_ConfigImg */
    public $img;
    /** @var Ressio_ConfigJs */
    public $js;
    /** @var Ressio_ConfigCss */
    public $css;

    /** @var Ressio_ConfigAmdd */
    public $amdd;
    /** @var Ressio_ConfigRddb */
    public $rddb;

    /** @var array */
    public $plugins;
    /** @var array */
    public $di;
}

class Ressio_ConfigHtml
{
    /** @var bool */
    public $forcehtml5;
    /** @var bool */
    public $mergespace;
    /** @var bool */
    public $removecomments;
    /** @var int */
    public $gzlevel;
    /** @var bool */
    public $urlminify;
}

class Ressio_ConfigImg
{
    /** @var bool */
    public $setdimension;
    /** @var bool */
    public $keeporig;
    /** @var bool */
    public $wrapwideimg;
    /** @var string */
    public $wideimgclass;
    /** @var bool */
    public $hiresimages;
    /** @var int */
    public $jpegquality;
    /** @var int */
    public $hiresjpegquality;
    /** @var bool */
    public $rescale;
    /** @var string */
    public $scaletype;
    /** @var int */
    public $bufferwidth;
    /** @var int */
    public $templatewidth;
    /** @var bool */
    public $lazyload;
    /** @var bool */
    public $lazyloaddomtastic;
}

class Ressio_ConfigJs
{
    /** @var bool */
    public $mergeheadbody;
    /** @var bool */
    public $loadurl;
    /** @var int */
    public $inlinelimit;
    /** @var bool */
    public $crossfileoptimization;
    /** @var bool */
    public $wraptrycatch;
    /** @var bool */
    public $autoasync;
    /** @var bool */
    public $checkattributes;
}

class Ressio_ConfigCss
{
    /** @var bool */
    public $mergeheadbody;
    /** @var bool */
    public $loadurl;
    /** @var int */
    public $inlinelimit;
    /** @var bool */
    public $crossfileoptimization;
    /** @var bool */
    public $checklinkattributes;
    /** @var bool */
    public $checkstyleattributes;
    /** @var string */
    public $abovethefoldcss;
}

class Ressio_ConfigAmdd
{
    /** @var string */
    public $handler;
    /** @var string */
    public $dbPath;
}

class Ressio_ConfigRddb
{
    /** @var string */
    public $apiurl;
    /** @var int */
    public $timeout;
    /** @var bool */
    public $proxy;
    /** @var string */
    public $proxy_url;
    /** @var string|false */
    public $proxy_login;
    /** @var string */
    public $proxy_pass;
}