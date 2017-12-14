<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

function sendError404()
{
    sendResponseCode(404, 'Not Found');
    echo '<h1>404 Not Found</h1>';
    exit();
}

function sendResponseCode($code, $message)
{
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
    if (version_compare(PHP_VERSION, '4.3.0', '>=')) {
        header("$protocol $code $message", true, $code);
    } else {
        header("$protocol $code $message");
    }
    header('Status: $code $message');
}

// @todo add support of get.php/hash in csslist/jslist classes
$filename = isset($_SERVER['PATH_INFO']) ? substr($_SERVER['PATH_INFO'], 1) : $_SERVER['QUERY_STRING'];
if (($pos = strpos($filename, '&')) !== false) {
    $filename = substr($filename, 0, $pos);
}

if (ini_get('expose_php')) {
    // override PHP's header
    header('X-Powered-By: RESS.io');
}

if (empty($filename) || strpos($filename, '/') !== false || strpos($filename, '..') !== false) {
    sendError404();
}

if (!defined('RESSIO_STATICDIR')) {
    define('RESSIO_PATH', dirname(__FILE__));
    include_once RESSIO_PATH . '/ressio.php';
    $config = Ressio::loadConfig();
    define('RESSIO_STATICDIR', $config->webrootpath . $config->staticdir);
}

$fullFilename = RESSIO_STATICDIR . '/' . $filename;

if (!is_file($fullFilename)) {
    sendError404();
}

$ext = pathinfo($filename, PATHINFO_EXTENSION);
switch ($ext) {
    case 'js':
        header('Content-Type: text/javascript');
        break;
    case 'css':
        header('Content-Type: text/css');
        break;
    default:
        sendError404();
}

/*
$etag = '"'.$filename.'"';
if(isset($_SERVER['HTTP_IF_NONE_MATCH']) ){
    $client_etag = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
    if($client_etag===$etag)
    {
        sendResponseCode(304, 'Not Modified');
        header('ETag: '.$etag);
        exit();
    }
} else {
    header('ETag: ' . $etag);
}
*/

// @todo move TTL to config
// expiration: +7 days
$cacheTTL = 7 * 24 * 60 * 60;
//header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheTTL));
//header('Pragma: public');
//header('Cache-Control: public, must-revalidate, proxy-revalidate');
header('Cache-Control: public, max-age=' . $cacheTTL);

header('Vary: Accept-Encoding');

$gzSupport = isset($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false);
if ($gzSupport) {
    $gzFile = $fullFilename . '.gz';
    if (is_file($gzFile)) {
        header('Content-Encoding: gzip');
        header('Content-Length: ' . filesize($gzFile));
        readfile($gzFile);
        exit();
    }
}

// @todo support X-Accel-Redirect/X-Sendfile, path is dirname($_SERVER['SCRIPT_NAME']).'/cache/'.$filename

readfile($fullFilename);
