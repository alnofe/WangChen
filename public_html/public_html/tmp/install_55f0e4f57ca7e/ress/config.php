<?php

return array(
    "autostart" => false,

    "webrootpath" => "", // /path/to/web/root (without trailing slashes)
    "webrooturi" => "", // /sub/dir/where/files/are/processed/ (with both leading and trailing slashes)
    "staticdir" => "./s", // /uri/of/static/files (./ - relative to ressio directory)

    "fileloader" => "file",
    "fileloaderphppath" => "./", // (./ - relative to ressio directory)
    "filehashsize" => 6,
    "cachepath" => "./cache", // (./ - relative to ressio directory)

    "html" => array(
        "gzlevel" => 5,
        "forcehtml5" => false,
        "mergespace" => true,
        "removecomments" => true,
        "urlminify" => true
    ),

    "css" => array(
        "mergeheadbody" => true,
        "crossfileoptimization" => false,
        "inlinelimit" => 1024,
        "loadurl" => true,
        "checklinkattributes" => true,
        "checkstyleattributes" => true,
        "abovethefoldcss" => ''
    ),

    "js" => array(
        "mergeheadbody" => true,
        "autoasync" => true,
        "crossfileoptimization" => false,
        "inlinelimit" => 1024,
        "loadurl" => true,
        "wraptrycatch" => false,
        "checkattributes" => true
    ),

    "img" => array(
        "rescale" => true,
        "bufferwidth" => 0,
        "hiresimages" => true,
        "hiresjpegquality" => 80,
        "jpegquality" => 90,
        "keeporig" => false,
        "scaletype" => "fit",
        "setdimension" => true,
        "templatewidth" => 960,
        "wideimgclass" => "wideimg",
        "wrapwideimg" => false,
        "lazyload" => true,
        "lazyloaddomtastic" => true
    ),

    "amdd" => array(
        "handler" => "plaintext",
        "cacheSize" => 1000,
        "dbPath" => "./vendor/amdd/devices",
        "dbUser" => "...",
        "dbPassword" => "...",
        "dbHost" => "localhost",
        "dbDatabase" => "...",
        "dbTableName" => "amdd",
        "dbDriver" => "pgsql:host=localhost;port=5432;dbname=...",
        "dbDriverOptions" => array()
    ),

    "rddb" => array(
        "timeout" => 3,
        "proxy" => false,
        "proxy_url" => "tcp://127.0.0.1:3128",
        "proxy_login" => false,
        "proxy_pass" => ""
    ),

    "plugins" => array(
        "Ressio_Plugin_Rescale" => null,
        "Ressio_Plugin_Lazyload" => null,
        "Ressio_Plugin_AboveTheFoldCSS" => null
    ),

    "di" => array(
        "cache" => "Ressio_Cache_File",
        "cssCombiner" => "Ressio_CssCombiner",
        "cssMinify" => "Ressio_CssMinify_Ress",
        "cssOptimizer" => "Ressio_CssOptimizer",
        "deviceDetector" => "Ressio_DeviceDetector_Rddb",
        "dispatcher" => "Ressio_Dispatcher",
        "fileLock" => "Ressio_FileLock_flock",
        "filesystem" => "Ressio_Filesystem_Native",
        "htmlOptimizer" => "Ressio_HtmlOptimizer_Pharse",
        "imgRescaler" => "Ressio_ImgRescale_GD",
        "jsCombiner" => "Ressio_JsCombiner",
        "jsMinify" => "Ressio_JsMinify_Jsmin",
        "urlRewriter" => "Ressio_UrlRewriter"
    )
);