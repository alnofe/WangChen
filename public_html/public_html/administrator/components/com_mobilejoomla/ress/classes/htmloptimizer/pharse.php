<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

defined('RESSIO_PATH') or die('RESS: Restricted access');

include_once(RESSIO_LIBS . '/pharse/pharse_parser_html.php');

class Ressio_HtmlOptimizer_Pharse implements IRessio_HtmlOptimizer
{
    /** @var Ressio_DI */
    private $di;
    /** @var Ressio_Config */
    private $config;
    /** @var Ressio_Dispatcher */
    private $dispatcher;
    /** @var Ressio_UrlRewriter $urlRewriter */
    private $urlRewriter;

    private $tags_selfclose = array(
        'area', 'base', 'basefont', 'br', 'col',
        'command', 'embed', 'frame', 'hr', 'img',
        'input', 'ins', 'keygen', 'link', 'meta',
        'param', 'source', 'track', 'wbr'
    );
    private $tags_nospaces = array(
        '~root~', 'html', 'head', 'body',
        'audio', 'canvas', 'embed', 'iframe', 'map',
        'object', 'ol', 'table', 'tbody', 'tfoot',
        'thead', 'tr', 'ul', 'video'
    );
    private $tags_preservespaces = array(
        'code', 'pre', 'textarea'
    );
    private $jsEvents = array(
        'onabort', 'onblur', 'oncancel', 'oncanplay', 'oncanplaythrough',
        'onchange', 'onclick', 'onclose', 'oncontextmenu', 'oncuechange',
        'ondblclick', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave',
        'ondragover', 'ondragstart', 'ondrop', 'ondurationchange', 'onemptied',
        'onended', 'onerror', 'onfocus', 'oninput', 'oninvalid',
        'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onloadeddata',
        'onloadedmetadata', 'onloadstart', 'onmousedown', 'onmousemove', 'onmouseout',
        'onmouseover', 'onmouseup', 'onmousewheel', 'onpause', 'onplay',
        'onplaying', 'onprogress', 'onratechange', 'onreset', 'onscroll',
        'onseeked', 'onseeking', 'onselect', 'onshow', 'onstalled',
        'onsubmit', 'onsuspend', 'ontimeupdate', 'onvolumechange', 'onwaiting'
    );
    private $uriAttrs = array(
        'a' => array('href'),
        'area' => array('href'),
        'audio' => array('src'),
        'embed' => array('src'),
        'form' => array('action'),
        'frame' => array('src'),
        'html' => array('manifest'),
        'iframe' => array('src'),
        'img' => array('src'),
        'input' => array('formaction', 'src'),
        'link' => array('href'),
        'object' => array('data'),
        'script' => array('src'),
        'source' => array('src'),
        'track' => array('src'),
        'video' => array('poster', 'src')
    );
    private $attrFirst = array(
        'a' => array('href'),
        'div' => array('class', 'id'),
        'iframe' => array('src'),
        'img' => array('src'),
        'input' => array('type', 'name'),
        'label' => array('for'),
        'link' => array('type', 'rel', 'href'),
        'option' => array('value'),
        'param' => array('type', 'name'),
        'script' => array('type'),
        'select' => array('name'),
        'span' => array('class', 'id'),
        'style' => array('type'),
        'textarea' => array('cols', 'rows', 'name')
    );
    private $defaultAttrsHtml4 = array(
        'area' => array(
            'shape' => 'rect'
        ),
        'button' => array(
            'type' => 'submit'
        ),
        'form' => array(
            'enctype' => 'application/x-www-form-urlencoded',
            'method' => 'get'
        ),
        'input' => array(
            'type' => 'text'
        )
    );
    private $defaultAttrsHtml5 = array(
        'area' => array(
            'shape' => 'rect'
        ),
        'button' => array(
            'type' => 'submit'
        ),
        'command' => array(
            'type' => 'command'
        ),
        'form' => array(
            'autocomplete' => 'on',
            'enctype' => 'application/x-www-form-urlencoded',
            'method' => 'get'
        ),
        'input' => array(
            'type' => 'text'
        ),
        'marquee' => array(
            'behavior' => 'scroll',
            'direction' => 'left'
        ),
        'ol' => array(
            'type' => 'decimal'
        ),
        'script' => array(
            'type' => 'text/javascript'
        ),
        'style' => array(
            'type' => 'text/css'
        ),
        'td' => array(
            'colspan' => '1',
            'rowspan' => '1'
        ),
        'textarea' => array(
            'wrap' => 'soft'
        ),
        'th' => array(
            'colspan' => '1',
            'rowspan' => '1'
        ),
        'track' => array(
            'kind' => 'subtitles'
        )
    );

    const DOCTYPE_HTML4 = 1;
    const DOCTYPE_HTML5 = 2;
    const DOCTYPE_XHTML = 3;
    /** @var int */
    public $doctype = self::DOCTYPE_HTML5;
    /** @var string */
    public $origDoctype;

    /** @var HTML_Node */
    public $dom;

    private $baseFound = false;

    /** @var Ressio_HTMLNode_JSList|null */
    private $lastJsNode;
    /** @var Ressio_HTMLNode_JSList|null */
    private $lastAsyncJsNode;
    /** @var Ressio_HTMLNode_CSSList|null */
    private $lastCssNode;

    public $classNodeCssList = 'Ressio_HTMLNode_CSSList';
    public $classNodeJsList = 'Ressio_HTMLNode_JSList';

    /**
     */
    public function __construct()
    {
    }

    /**
     * @param $di Ressio_DI
     */
    public function setDI($di)
    {
        $this->di = $di;
        $this->config = $di->get('config');
        $this->dispatcher = $di->get('dispatcher');
        $this->urlRewriter = $di->get('urlRewriter');
    }

    /**
     * @param $buffer string
     * @return string
     */
    public function run($buffer)
    {
        //@todo Implement caching (for static html pages)
        //@todo (necessary to split parsing and optimization to support browser-specific optimization)

        // parse html
        $page = new HTML_Parser_HTML5($buffer);

        $dom = $page->root;
        $this->dom = $dom;

        $this->lastJsNode = null;
        $this->lastAsyncJsNode = null;
        $this->lastCssNode = null;

        $this->dispatcher->triggerEvent('HtmlIterateBefore', array($this));

        $this->domIterate($dom, $this->config->html->mergespace);

        if ($this->origDoctype === null && $this->config->html->forcehtml5) {
            $offset = 0;
            $dom->addDoctype('html', $offset);
        }

//        if ($this->config->js->autoasync && $this->lastAsyncJsNode) {
//            $this->lastAsyncJsNode->attributes['async'] = 'async';
//        }

//        if ($this->lastCssNode) {
//            // move to the end of body
//            $body = $dom->getChildrenByTag('body');
//            if (count($body) === 1) {
//                $this->lastCssNode->changeParent($body[0]);
//            }
//        }

        $this->dispatcher->triggerEvent('HtmlIterateAfter', array($this));

        $buffer = (string)$dom;

        $this->dom = null;
        $this->lastJsNode = null;
        $this->lastAsyncJsNode = null;
        $this->lastCssNode = null;

        return $buffer;
    }

    /**
     * @param $file string
     * @param $attribs array|null
     */
    public function appendScript($file, $attribs = null)
    {
        // @todo take into account autoasync!!

        $node = $this->dom->addChild('script');

        if ($this->doctype !== self::DOCTYPE_HTML5) {
            $node->attributes['type'] = 'text/javascript';
        }
        $node->attributes['src'] = $file;
        if (is_array($attribs)) {
            $node->attributes = array_merge($node->attributes, $attribs);
        }

        $this->addJs($node, true);
    }

    /**
     * @param $content string
     * @param $attribs array|null
     */
    public function appendScriptDeclaration($content, $attribs = null)
    {
        /** @var HTML_Node $node */
        $node = $this->dom->addChild('script');

        if ($this->doctype !== self::DOCTYPE_HTML5) {
            $node->attributes['type'] = 'text/javascript';
        }
        if (is_array($attribs)) {
            $node->attributes = array_merge($node->attributes, $attribs);
        }

        $node->addText($content);

        $this->addJs($node, true, true);
    }

    /**
     * @param $file string
     * @param $attribs array|null
     */
    public function appendStylesheet($file, $attribs = null)
    {
        /** @var HTML_Node $node */
        $node = $this->dom->addChild('link');

        if ($this->doctype !== self::DOCTYPE_HTML5) {
            $node->attributes['type'] = 'text/css';
        }
        $node->attributes['rel'] = 'stylesheet';
        $node->attributes['href'] = $file;
        if (is_array($attribs)) {
            $node->attributes = array_merge($node->attributes, $attribs);
        }

        $this->addCss($node);
    }

    /**
     * @param $content string
     * @param $attribs array|null
     */
    public function appendStyleDeclaration($content, $attribs = null)
    {
        /** @var HTML_Node $node */
        $node = $this->dom->addChild('style');

        if ($this->doctype !== self::DOCTYPE_HTML5) {
            $node->attributes['type'] = 'text/css';
        }
        if (is_array($attribs)) {
            $node->attributes = array_merge($node->attributes, $attribs);
        }

        $node->addText($content);

        $this->addCss($node);
    }

    /** @var array */
    private $cmpAttrFirst;

    /**
     * Comparison method to sort attributes for better gzip compression
     * @param string $attr1
     * @param string $attr2
     * @return int
     */
    public function attrFirstCmp($attr1, $attr2)
    {
        $value1 = array_search($attr1, $this->cmpAttrFirst, true);
        if ($value1 === false) {
            $value1 = 1000;
        }

        $value2 = array_search($attr2, $this->cmpAttrFirst, true);
        if ($value2 === false) {
            $value2 = 1000;
        }

        return $value1 - $value2;
    }

    /**
     * @param HTML_Node $node
     * @param bool $mergeSpace
     */
    protected function domIterate(&$node, $mergeSpace)
    {
        // skip xmp and asp tags
        if ($node instanceof $node->childClass_XML ||
            $node instanceof $node->childClass_ASP
        ) {
            return;
        }

        // doctype
        if ($node instanceof $node->childClass_Doctype) {
            /** @var HTML_Node_DOCTYPE $node */
            $this->origDoctype = $node->dtd;
            if ($this->config->html->forcehtml5) {
                $node->dtd = 'html';
            } elseif (strpos($node->dtd, 'DTD HTML')) {
                $this->doctype = self::DOCTYPE_HTML4;
            } elseif (strpos($node->dtd, 'DTD XHTML')) {
                $this->doctype = self::DOCTYPE_XHTML;
            } else {
                $this->doctype = self::DOCTYPE_HTML5;
            }
            return;
        }

        // CDATA is text in xhtml and comment in html
        if ($node instanceof $node->childClass_Text ||
            ($this->doctype === self::DOCTYPE_XHTML && $node instanceof $node->childClass_CDATA)
        ) {
            /** @var HTML_Node_TEXT $node */
            if ($mergeSpace) {
                $node->text = preg_replace('/\s+/m', ' ', $node->text);
                if ($node->text === ' ' && in_array($node->parent->tag, $this->tags_nospaces, true)) {
                    $node->detach();
                }
            }
            return;
        }

        // remove comments
        if ($node instanceof $node->childClass_Comment ||
            ($this->doctype !== self::DOCTYPE_XHTML && $node instanceof $node->childClass_CDATA)
        ) {
            /** @var HTML_Node_COMMENT $node */
            if ($this->config->html->removecomments && $node->text !== '' && $node->text[0] !== '!') {
                $node->detach();
            }
            return;
        }

        // check comments (keep IE ones on IE, [if, <![ : <!--[if IE]>, <!--<![endif]--> )
        // stop css/style combining in IE cond block
        if ($node instanceof $node->childClass_Conditional) {
            /** @var HTML_Node_CONDITIONAL $node */
            /** @var IRessio_DeviceDetector $detector */
            $detector = $this->di->get('devicedetector');
            $vendor = $detector->vendor();
            if ($vendor !== 'ms' && $vendor !== 'unknown') { // if not IE browser
                $node->detach();
            } else { //IE
                // todo: parse as html and compress internals
                $this->breakCss();
                $this->breakJs();
                $inner = $node->children[0]->text;
                $inner = preg_replace('#\s+<!--$#', '<!--', ltrim($inner));
                $node->children[0]->text = $inner;
            }
            return;
        }

        // disable optimizing of nodes with ress-safe attribute
        if (isset($node->attributes['ress-safe'])) {
            unset($node->attributes['ress-safe']);
            return;
        }

        // @todo: remove first and last spaces in block elements
        // @todo: remove space after open/close tag if there is space before the tag

        /** @var HTML_Node $node */

        // check and parse ress-media attribute
        if (isset($node->attributes['ress-media'])) {
            if (!$this->matchRessMedia($node->attributes['ress-media'])) {
                $node->detach();
                return;
            }
            unset($node->attributes['ress-media']);
        }

        $iterateChildren = true;

        $this->dispatcher->triggerEvent('HtmlIterateTag' . $node->tag . 'Before', array($this, $node));
        if ($node->parent === null && $node->tag !== '~root~') {
            return;
        }

        switch ($node->tag) {
            case 'a':
            case 'area':
                if (isset($node->attributes['href'])) {
                    $uri = $node->attributes['href'];
                    if (strpos($uri, 'javascript:') === 0) {
                        $node->attributes['href'] = 'javascript:' . $this->jsMinifyInline(substr($uri, 11));
                    }
                }
                break;
            case 'base':
                // save base href (use first tag only)
                if (!$this->baseFound && isset($node->attributes['href'])) {
                    $base = $node->attributes['href'];
                    if (substr($base, -1) !== '/') {
                        $base = dirname($base);
                        if ($base === '.') {
                            $base = '';
                        }
                        $base .= '/';
                    }
                    $this->urlRewriter->setBase($base);
                    $node->attributes['href'] = $this->urlRewriter->getBase();
                    $this->baseFound = true;
                }
                break;
            case 'body':
                // set css break point to preserve css files order after dynamically adding styles to head using js
                if (!$this->config->css->mergeheadbody) {
                    $this->breakCss();
                }
                if (!$this->config->js->mergeheadbody) {
                    $this->breakJs(true);
                }
                break;
            case 'img':
                // @todo Auto set alt="" if not exists
                $iterateChildren = false; // fix for lazyload plugin
                break;
            case 'picture':
                // parse <picture> elements
                break;
            case 'script':
                if (isset($node->attributes['ress-noasync'])) {
                    unset($node->attributes['ress-noasync']);
                    $autoasync = false;
                } else {
                    $autoasync = $this->config->js->autoasync;
                }

                if (isset($node->attributes['ress-nomerge'])) {
                    unset($node->attributes['ress-nomerge']);
                    $merge = false;
                } else {
                    $merge = $this->config->js->merge;
                }

                // break if there attributes other than type=text/javascript, defer, async
                if (count($node->attributes)) {
                    $attributes = $node->attributes;
                    if ($this->config->js->checkattributes) {
                        if (isset($attributes['type']) && $attributes['type'] === 'text/javascript') {
                            unset($attributes['type'], $node->attributes['type']);
                        }
                        unset($attributes['defer'], $attributes['async'], $attributes['src']);
                        if (count($attributes) > 0) {
                            $this->breakJs(true);
                            break;
                        }
                    } else {
                        if (isset($attributes['type']) && $attributes['type'] !== 'text/javascript') {
                            $this->breakJs(true);
                            break;
                        }
                    }
                }

                // set type=text/javascript in html4 and remove in html5
                if ($this->doctype !== self::DOCTYPE_HTML5 && !isset($node->attributes['type'])) {
                    $node->attributes['type'] = 'text/javascript';
                }

                if (!isset($node->attributes['src'])) { // inline
                    $scriptBlob = $node->children[0]->text;
                    // @todo: refactor clear comments
                    $scriptBlob = preg_replace(array('#^\s*<!--.*?[\r\n]+#', '#//\s*<!--.*$#m', '#//\s*-->.*$#m', '#\s*-->\s*$#'), '', $scriptBlob);
                    // @todo remove CDATA wrapping
                    if ($autoasync && (strpos($scriptBlob, '.write') === false || !preg_match('#\.write(?!\(\))#', $scriptBlob))) {
                        $node->attributes['async'] = 'async';
                    }
                    $node->children[0]->text = $scriptBlob;

                    if ($merge) {
                        $this->addJs($node, false, true);
                    } else {
                        $this->breakJs(true);
                    }
                } else { // external
                    if ($merge) {
                        $srcFile = $this->urlRewriter->urlToFilepath($node->attributes['src']);
                        $ext = pathinfo($srcFile, PATHINFO_EXTENSION);
                        $merge = ($ext === 'js') && ($this->config->js->loadurl || ($srcFile !== null));
                    }

                    if ($merge && $autoasync && !isset($node->attributes['async'])) {
                        $node->attributes['async'] = 'async';
                    }

                    if ($merge) {
                        $this->addJs($node);
                    } else {
                        $this->breakJs($this->config->js->autoasync);
                    }
                }
                break;

            case 'link':
                // break if there attributes other than type=text/css, rel=stylesheet, href
                if (!isset($node->attributes['rel'], $node->attributes['href']) || $node->attributes['rel'] !== 'stylesheet') {
                    break;
                }

                $attributes = $node->attributes;
                if ($this->config->css->checklinkattributes) {
                    if (isset($attributes['type']) && $attributes['type'] === 'text/css') {
                        unset($attributes['type']);
                    }
                    unset($attributes['rel'], $attributes['media'], $attributes['href']);
                    if (count($attributes) > 0) {
                        $this->breakCss();
                        break;
                    }
                } else {
                    if (isset($attributes['type']) && $attributes['type'] !== 'text/css') {
                        break;
                    }
                }

                // set type=text/css in html4 and remove in html5
                if ($this->doctype !== self::DOCTYPE_HTML5 && !isset($node->attributes['type'])) {
                    $node->attributes['type'] = 'text/css';
                }

                if (isset($node->attributes['ress-nomerge'])) {
                    unset($node->attributes['ress-nomerge']);
                    $merge = false;
                } else {
                    // minify css file (for external: breakpoint/load/@import)
                    $merge = $this->config->css->merge;
                    if ($merge) {
                        $srcFile = $this->urlRewriter->urlToFilepath($node->attributes['href']);
                        $ext = pathinfo($srcFile, PATHINFO_EXTENSION);
                        $merge = ($ext === 'css') && ($this->config->css->loadurl || ($srcFile !== null));
                    }
                }

                if ($merge) {
                    $this->addCss($node);
                } else {
                    $this->breakCss();
                }

                break;

            case 'style':
                $attributes = $node->attributes;
                if ($this->config->css->checkstyleattributes) {
                    // break if there attributes other than type=text/css
                    if (isset($attributes['type']) && $attributes['type'] === 'text/css') {
                        unset($attributes['type']);
                    }
                    unset($attributes['media']);
                    if (count($attributes) > 0) {
                        $this->breakCss();
                        break;
                    }
                } else {
                    if (isset($attributes['type']) && $attributes['type'] !== 'text/css') {
                        break;
                    }
                }

                // set type=text/css in html4 and remove in html5
                if ($this->doctype !== self::DOCTYPE_HTML5 && !isset($node->attributes['type'])) {
                    $node->attributes['type'] = 'text/css';
                }
                // remove media attribute if it is empty or "all"
                if (isset($node->attributes['media'])) {
                    $media = $node->attributes['media'];
                    // @todo: parse media
//                    $media = $this->filterMedia($media);
                    if ($media === '' || $media === 'all') {
                        unset($node->attributes['media']);
                    }
                }
                // css break point if scoped=... attribute
                if (isset($node->attributes['scoped'])) {
                    $this->breakCss();
                }

                // @todo: check type

                if (isset($node->attributes['ress-nomerge'])) {
                    unset($node->attributes['ress-nomerge']);
                    $merge = false;
                } else {
                    $merge = true;
                }

                if ($merge) {
                    $this->addCss($node, true);
                } else {
                    $this->breakCss();
                }

                break;
            case 'noscript':
                // remove if js is enabled
                break;
        }

        $this->dispatcher->triggerEvent('HtmlIterateTag' . $node->tag, array($this, $node));
        if ($node->parent === null && $node->tag !== '~root~') {
            return;
        }

        if (!in_array($node->tag, array('script', '~javascript~'), true)) {
            $this->breakJs();
        }

        // minimal form of self-close tags
        if ($node->self_close) {
            $node->self_close_str =
                ($this->doctype !== self::DOCTYPE_XHTML && in_array($node->tag, $this->tags_selfclose, true)) ? '' : '/';
        }

        // minify uri in attributes
        if ($this->config->html->urlminify && isset($this->uriAttrs[$node->tag])) {
            foreach ($this->uriAttrs[$node->tag] as $attrName) {
                if (isset($node->attributes[$attrName])) {
                    $uri = $node->attributes[$attrName];
                    if (strpos($uri, 'data:') !== 0) {
                        $node->attributes[$attrName] = $this->urlRewriter->minify($uri);
                    }
                }
            }
        }

        //minify style attribute (css)
        if (isset($node->attributes['style'])) {
            $node->attributes['style'] = $this->cssMinifyInline($node->attributes['style'], $this->urlRewriter->getBase(), $this->urlRewriter->getBase());
        }

        //minify on* handlers (js)
        foreach ($node->attributes as $name => &$value) {
            if (isset($this->jsEvents[$name])) {
                $value = $this->jsMinifyInline($value);
            }
        }
        unset($value);

        //compress class attribute
        if (isset($node->attributes['class'])) {
            $node->attributes['class'] = preg_replace('#\s+#', ' ', $node->attributes['class']);
        }

        //remove default attributes with default values (type=text for input etc)
        switch ($this->doctype) {
            case self::DOCTYPE_HTML5:
                $defaultAttrs = $this->defaultAttrsHtml5;
                break;
            case self::DOCTYPE_HTML4:
                $defaultAttrs = $this->defaultAttrsHtml4;
                break;
            default:
                $defaultAttrs = array();
        }
        if (isset($defaultAttrs[$node->tag])) {
            foreach ($defaultAttrs[$node->tag] as $attrName => $attrValue) {
                if (isset($node->attributes[$attrName]) && $node->attributes[$attrName] === $attrValue) {
                    unset($node->attributes[$attrName]);
                }
            }
        }

        // rearrange attributes to improve gzip compression
        // (e.g. always use <input type=" or <option value=", etc.)
        if (count($node->attributes) >= 2 && isset($this->attrFirst[$node->tag])) {
            $this->cmpAttrFirst = $this->attrFirst[$node->tag];
            uksort($node->attributes, array($this, 'attrFirstCmp'));
        }

        $this->dispatcher->triggerEvent('HtmlIterateTag' . $node->tag . 'After', array($this, $node));
        if ($node->parent === null && $node->tag !== '~root~') {
            return;
        }

        if ($iterateChildren) {
            $children = $node->children;
            $mergeSpace = $mergeSpace && !in_array($node->tag, $this->tags_preservespaces, true);
            foreach ($children as $child) {
                $this->dispatcher->triggerEvent('HtmlIterateNodeBefore', array($this, $child));
                if ($child->parent === null) {
                    unset($child);
                    continue;
                }
                $this->domIterate($child, $mergeSpace);
                $this->dispatcher->triggerEvent('HtmlIterateNodeAfter', array($this, $child));
                if ($child->parent === null) {
                    unset($child);
                    continue;
                }
            }
        }

        //@todo: remove closing tags for </li> etc (modify HTML_Node::toString)
        //@todo: remove quotes in attribute values (modify HTML_Node::toString_attributes)

        //@todo: collect external domains and add [link rel=prefetch] tags to the head -> plugin
    }

    /**
     * @param $node HTML_Node
     * @param $append bool
     * @param $inline bool
     */
    private function addJs(&$node, $append = false, $inline = false)
    {
        $src = $inline ? $node->children[0]->text : $node->attributes['src'];
        $async = isset($node->attributes['async']);
        $defer = isset($node->attributes['defer']);

        // @todo: take into account difference between async and defer

        if ($this->lastJsNode !== null ||
            (($append || $async || $defer) && $this->lastAsyncJsNode !== null)
        ) {
            $node->detach();
        } else {
            $newNode = new $this->classNodeJsList($this->di);
            $index = $node->index();
            $node->parent->addChild($newNode, $index);
            $node->detach();
            /** @var Ressio_HTMLNode_JSList $node */
            $node = $newNode;

            $this->lastJsNode = $node;
            $this->lastAsyncJsNode = $node;
        }

        $jsNode = ($this->lastJsNode !== null) ? $this->lastJsNode : $this->lastAsyncJsNode;
        $jsNode->scriptList[] = $inline
            ? array(
                'type' => 'inline',
                'script' => $src,
                'async' => $async,
                'defer' => $defer
            ) : array(
                'type' => 'ref',
                'src' => $src,
                'async' => $async,
                'defer' => $defer
            );
    }

    private function breakJs($full = false)
    {
        $this->lastJsNode = null;
        if ($full) {
            $this->lastAsyncJsNode = null;
        }
    }

    /**
     * @param $node HTML_Node
     * @param $inline bool
     */
    private function addCss(&$node, $inline = false)
    {
        $src = $inline ? $node->children[0]->text : $node->attributes['href'];

        $media = isset($node->attributes['media']) ? $node->attributes['media'] : 'all';

        if ($this->lastCssNode !== null) {
            $node->detach();
        } else {
            /** @var Ressio_HTMLNode_CSSList $newNode */
            $newNode = new $this->classNodeCssList($this->di);
            $index = $node->index();
            $node->parent->addChild($newNode, $index);
            $node->detach();
            /** @var Ressio_HTMLNode_CSSList $node */
            $node = $newNode;

            $this->lastCssNode = $node;
        }

        $this->lastCssNode->styleList[] = $inline
            ? array(
                'type' => 'inline',
                'style' => $src,
                'media' => $media
            )
            : array(
                'type' => 'ref',
                'src' => $src,
                'media' => $media
            );
    }

    private function breakCss()
    {
        $this->lastCssNode = null;
    }

    /**
     * Minify CSS
     * @param string $str
     * @param string $srcBase
     * @param string $targetBase
     * @return string
     */
    public function cssMinifyInline($str, $srcBase = null, $targetBase = null)
    {
        /** @var IRessio_CssMinify $minifyCss */
        $minifyCss = $this->di->get('cssMinify');
        return $minifyCss->minifyInline($str, $srcBase);
    }

    /**
     * Minify JS
     * @param string $str
     * @return string
     */
    public function jsMinifyInline($str)
    {
        /** @var IRessio_JsMinify $minifyJs */
        $minifyJs = $this->di->get('jsMinify');
        return $minifyJs->minifyInline($str);
    }

    /**
     * @param string $ressMedia
     * @return bool
     * @throws Exception
     */
    public function matchRessMedia($ressMedia)
    {
        //Example: ress-media="mobile and (vendor: webkit) and not (os: android)"

        /** @var IRessio_DeviceDetector $device */
        $device = $this->di->get('deviceDetector');

        $ressMedia = trim($ressMedia);
        $size = strlen($ressMedia);
        $i = 0;

        while ($i < $size) {
            // parse "not"
            $invertRule = false;
            if ($i + 4 < $size
                && $ressMedia[$i] === 'n' && $ressMedia[$i + 1] === 'o' && $ressMedia[$i + 2] === 't'
                && ($ressMedia[$i + 3] <= ' ' || $ressMedia[$i + 3] === '(')
            ) {
                $invertRule = true;
                $i += 3;
                while ($i < $size && $ressMedia[$i] <= ' ') {
                    $i++;
                }
                if ($i === $size) {
                    error_log('Wrong ress-media query: ' . $ressMedia);
                    return false;
                }
            }

            if ($ressMedia[$i] === '(') {
                // parse prop:value
                $j = strpos($ressMedia, ')', $i + 1);
                if ($j === false) {
                    error_log('Wrong ress-media query: ' . $ressMedia);
                    return false;
                }
                /** @var string $prop */
                /** @var string $value */
                list($prop, $value) = explode(':', substr($ressMedia, $i + 1, $j - $i - 1), 2);
                if ($value === null) {
                    error_log('Wrong ress-media query: ' . $ressMedia);
                    return false;
                }
                $prop = trim($prop);
                $value = trim($value);
                $compare = '=';
                if (strlen($prop) > 4 && $prop{0} === 'm' && $prop{3} === '-') {
                    if ($prop[1] === 'i' && $prop[2] === 'n') {
                        // min-
                        $compare = '>=';
                        $prop = substr($prop, 4);
                    } elseif ($prop[1] === 'a' && $prop[2] === 'x') {
                        // max-
                        $compare = '<=';
                        $prop = substr($prop, 4);
                    }
                }

                // @todo support px/em in values
                // 1em = 16px = 12pt
                // Aem => (16*A)px
                // Apt => (4/3*A)px
                $result = false;
                switch ($prop) {
                    case 'vendor':
                        $result = strcasecmp($device->vendor(), $value) === 0;
                        break;
                    case 'vendor-version':
                        $result = version_compare($device->vendor_version(), $value, $compare);
                        break;
                    case 'os':
                        $result = strcasecmp($device->os(), $value) === 0;
                        break;
                    case 'os-version':
                        $result = version_compare($device->os_version(), $value, $compare);
                        break;
                    case 'browser':
                        $result = strcasecmp($device->browser(), $value) === 0;
                        break;
                    case 'browser-version':
                        $result = version_compare($device->browser_version(), $value, $compare);
                        break;
                    case 'device-pixel-ratio':
                        if (strpos($value, '/') !== false) {
                            list($x, $y) = explode('/', $value, 2);
                            $value = (int)$x / (int)$y;
                        }
                        $value = (float)$value;
                        $dpr = (float)$device->screen_dpr();
                        switch ($compare) {
                            case '=':
                                $result = ($dpr === $value);
                                break;
                            case '<=':
                                $result = ($dpr <= $value);
                                break;
                            case '>=':
                                $result = ($dpr >= $value);
                                break;
                        }
                        break;
                    case 'device-width':
                        $width = (int)$device->screen_width();
                        if ($width === 0) {
                            $result = true;
                            break;
                        }
                        $value = (int)$value;
                        switch ($compare) {
                            case '=':
                                $result = ($width === $value);
                                break;
                            case '<=':
                                $result = ($width <= $value);
                                break;
                            case '>=':
                                $result = ($width >= $value);
                                break;
                        }
                        break;
                    case 'device-height':
                        $height = (int)$device->screen_height();
                        if ($height === 0) {
                            $result = true;
                            break;
                        }
                        $value = (int)$value;
                        switch ($compare) {
                            case '=':
                                $result = ($height === $value);
                                break;
                            case '<=':
                                $result = ($height <= $value);
                                break;
                            case '>=':
                                $result = ($height >= $value);
                                break;
                        }
                        break;
                    case 'device-size':
                        $value = (int)$value;
                        switch ($compare) {
                            case '=':
                                error_log('Unknown property (' . $prop . ') in ress-media query: ' . $ressMedia);
                                return false;
                                break;
                            case '<=':
                                $deviceSize = (int)max($device->screen_height(), $device->screen_width());
                                $result = ($deviceSize === 0) || ($deviceSize <= $value);
                                break;
                            case '>=':
                                $deviceSize = (int)min($device->screen_height(), $device->screen_width());
                                $result = ($deviceSize === 0) || ($deviceSize >= $value);
                                break;
                        }
                        break;
                    default:
                        error_log('Unknown property (' . $prop . ') in ress-media query: ' . $ressMedia);
                        return false;
                }

                if ($result === $invertRule) {
                    // "(false)" or "not(true)"
                    return false;
                }

                $i = $j + 1;
                while ($i < $size && $ressMedia[$i] <= ' ') {
                    $i++;
                }

            } else {
                // parse device category name
                $j = strpos($ressMedia, ' ', $i + 1);
                if ($j === false) {
                    $value = substr($ressMedia, $i);
                    $i = $size;
                } else {
                    $value = substr($ressMedia, $i, $j - $i);
                    $i = $j + 1;
                    while ($i < $size && $ressMedia[$i] <= ' ') {
                        $i++;
                    }
                }
                switch ($value) {
                    case 'mobile':
                        $result = $device->isMobile();
                        break;
                    case 'desktop':
                        $result = $device->isDesktop();
                        break;
                    default:
                        $result = ($device->category() === $value);
                }

                if ($result === $invertRule) {
                    // "false" or "not true"
                    return false;
                }
            }

            if ($i >= $size) {
                break;
            }

            // parse "and"
            if ($i + 4 < $size
                && $ressMedia[$i] === 'a' && $ressMedia[$i + 1] === 'n' && $ressMedia[$i + 2] === 'd'
                && ($ressMedia[$i + 3] <= ' ' || $ressMedia[$i + 3] === '(')
            ) {
                $i += 3;
                while ($i < $size && $ressMedia[$i] <= ' ') {
                    $i++;
                }
            } else {
                error_log('Wrong ress-media query: ' . $ressMedia);
                return false;
            }
        }
        return true;
    }
}
