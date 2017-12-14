<?php
/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */

// @todo Gecko and IE supports some webkit-prefixed features,
//       keep them if no corresponding -moz or -ms features presented

class Ressio_CssOptimizer implements IRessio_CssOptimizer
{
    /** @var Ressio_DI */
    private $di;
    /** @var Ressio_Config */
    public $config;

    /** @var string */
    public $srcBase;
    /** @var string */
    public $targetBase;

    /** @var string|bool */
    private $browserVendor;
    /** @var string */
    private $browserVendorWrapped;

    /**
     * @param $di Ressio_DI
     */
    public function setDI($di)
    {
        $this->di = $di;
        $this->config = $di->get('config');

        /** @var IRessio_DeviceDetector $detector */
        $detector = $this->di->get('devicedetector');
        $this->browserVendor = $detector->vendor();
        $this->browserVendorWrapped = '-' . $this->browserVendor . '-';
        if ($this->browserVendor === 'unknown') {
            $this->browserVendor = false;
        }
    }

    /**
     * @param $buffer string
     * @param $srcBase string
     * @param $targetBase string
     * @return string
     */
    public function run($buffer, $srcBase = null, $targetBase = null)
    {
        $this->srcBase = $srcBase;
        $this->targetBase = $targetBase;

        try {
            $buffer = preg_replace('/(^\s*<!--\s*|\s*-->\s*$)/', '', $buffer);
            $parser = new Ressio_CssParser;
            $parsed = $parser->parse($buffer);
            $this->cssIterateRules($parsed->rules, '');
            $buffer = (string)$parsed;
        } catch (Exception $e) {
            error_log('Catched error in Ressio_CssOptimizer::run: ' . $e->getMessage());
        }
        return $buffer;
    }

    /**
     * @param string $url
     * @return string
     */
    private function escapeUrl($url)
    {
        if (!strpbrk($url, ' ()\'"')) {
            return $url;
        }

        /** @var int[] $c */
        $c = count_chars($url);
        $url1_extra = $c[32/* */] + $c[34/*"*/] + $c[39/*'*/] + $c[40/*(*/] + $c[41/*)*/];
        $url2_extra = 2 + $c[39/*'*/];
        $url3_extra = 2 + $c[34/*"*/];

        if ($url1_extra < $url2_extra) {
            if ($url1_extra < $url3_extra) {
                return addcslashes($url, ' "\'()');
            } else {
                return '"' . addcslashes($url, '"') . '"';
            }
        } else {
            if ($url2_extra < $url3_extra) {
                return "'" . addcslashes($url, "'") . "'";
            } else {
                return '"' . addcslashes($url, '"') . '"';
            }
        }
    }

    /**
     * @param $matches string[]
     * @return string
     */
    public function replaceUrlsCallback($matches)
    {
        $relurl = trim($matches[0], ' \'"');
        $relurl = stripslashes($relurl);

        if (0 === strncasecmp('data:', $relurl, 5)) {
            return $this->escapeUrl($relurl);
        }

        /** @var Ressio_UrlRewriter $urlRewriter */
        $urlRewriter = $this->di->get('urlRewriter');

        $relurl = $urlRewriter->getRebasedUrl($relurl, $this->srcBase, $this->targetBase);
        return $this->escapeUrl($relurl);
    }

    /**
     * @param $rules array
     * @param $media string
     * @return array
     */
    private function embedMedia($rules, $media)
    {
        $newRules = array();
        $mediaRules = array();
        foreach ($rules as $rule) {
            if ($rule instanceof Ressio_CSS_AtMedia) {
                if ($rule->media === $media) {
                    foreach ($rule->rules as $mediaRule) {
                        $mediaRules[] = $mediaRule;
                    }
                } else {
                    $newRules[] = new Ressio_CSS_AtMedia($media, $mediaRules);
                    $mediaRules = array();

                    $media_list = explode(',', $rule->media);
                    foreach ($media_list as &$cond) {
                        $cond = $media . ' and ' . trim($cond);
                        // @todo merge "screen and screen"
                        // @todo remove "screen and not screen"
                        // @todo optionally remove "print" rules for mobile devices
                    }
                    unset($cond);
                    $media_list = implode(',', $media_list);
                    $newRules[] = new Ressio_CSS_AtMedia($media_list, $rule->rules);
                }
            } else {
                $mediaRules[] = $rule;
            }
        }
        if (count($mediaRules)) {
            $newRules[] = new Ressio_CSS_AtMedia($media, $mediaRules);
        }
        return $newRules;
    }

    /**
     * @param $rules array
     * @param $media string
     */
    private function cssIterateRules(&$rules, $media)
    {
        /** @var Ressio_UrlRewriter $urlRewriter */
        $urlRewriter = $this->di->get('urlRewriter');
        /** @var IRessio_Filesystem $fs */
        $fs = $this->di->get('filesystem');

        $newRules = array();
        foreach ($rules as $rule) {
            $className = get_class($rule);
            switch ($className) {
                case 'Ressio_CSS_Comment':
                    /** @var Ressio_CSS_Comment $rule */
                    if ($rule->comment === '' || $rule->comment{0} !== '!') {
                        $rule = null;
                        break;
                    }
                    break;
                case 'Ressio_CSS_Rule':
                    /** @var Ressio_CSS_Rule $rule */
                    // check declarations
                    $this->cssIterateDeclarations($rule->declarations);
                    if (count($rule->declarations->declarations) === 0) {
                        $rule = null;
                        break;
                    }
                    foreach ($rule->selector as $i => &$selector) {
                        // remove vendor-specific selectors
                        if ($this->browserVendor && preg_match('/:-(\w+)-/', $selector, $m) && $m[1] !== $this->browserVendor) {
                            unset($rule->selector[$i]);
                            continue;
                        }
                        // optimize selectors
                        $selector = str_replace("\n", ' ', trim($selector));
                        // @todo keep double space in quotes (like it does for values)
                        $selector = preg_replace('/(?<=\s)\s+/', '', $selector);
                        $selector = preg_replace('/\s?(>|\+)\s?/', '\1', $selector);
                    }
                    unset($selector);
                    if (count($rule->selector) === 0) {
                        $rule = null;
                        break;
                    }
                    break;
                case 'Ressio_CSS_AtViewport':
                    /** @var Ressio_CSS_AtViewport $rule */
                    if ($this->browserVendor && !empty($rule->vendor) && $rule->vendor !== $this->browserVendorWrapped) {
                        $rule = null;
                        break;
                    }
                    $this->cssIterateDeclarations($rule->declarations);
                    if (count($rule->declarations->declarations) === 0) {
                        $rule = null;
                        break;
                    }
                    break;
                case 'Ressio_CSS_AtKeyframes':
                    /** @var Ressio_CSS_AtKeyframes $rule */
                    if ($this->browserVendor && !empty($rule->vendor) && $rule->vendor !== $this->browserVendorWrapped) {
                        $rule = null;
                        break;
                    }
                    $this->cssIterateRules($rule->keyrules, '');
                    break;
                case 'Ressio_CSS_AtDocument':
                    /** @var Ressio_CSS_AtDocument $rule */
                    if ($this->browserVendor && !empty($rule->vendor) && $rule->vendor !== $this->browserVendorWrapped) {
                        $rule = null;
                        break;
                    }
                    $this->cssIterateRules($rule->rules, '');
                    break;
                case 'Ressio_CSS_Keyframe':
                case 'Ressio_CSS_AtFontface':
                    /** @var Ressio_CSS_AtFontface $rule */
                    $this->cssIterateDeclarations($rule->declarations);
                    if (count($rule->declarations->declarations) === 0) {
                        $rule = null;
                        break;
                    }
                    break;
                case 'Ressio_CSS_AtCharset':
                    /** @var Ressio_CSS_AtCharset $rule */
                    // remove charset
                    $rule = null;
                    break;
                case 'Ressio_CSS_AtImport':
                    /** @var Ressio_CSS_AtImport $rule */
                    // load & embed
                    $relurl = $rule->url;
                    $base = $urlRewriter->getBase();
                    $relurl = $urlRewriter->getRebasedUrl($relurl, $this->srcBase, $base);
                    if (strpos($relurl, '://') === false) {
                        $url = rtrim($base, '/') . $relurl;
                        $path = $urlRewriter->urlToFilepath($url);
                        if ($fs->isFile($path)) {
                            $content = $fs->getContents($path);
                            // @todo check $content===false
                            if (!empty($rule->media)) {
                                $content = '@media ' . $rule->media . '{' . $content . '}';
                            }
                            $saveSrcBase = $this->srcBase;
                            $this->srcBase = dirname($url);
                            if($this->srcBase==='.'){
                                $this->srcBase='';
                            }
                            try {
                                $parser = new Ressio_CssParser();
                                $parsed = $parser->parse($content);
                                unset($parser);
                                $this->cssIterateRules($parsed->rules, $media);
                                $rule = $parsed->rules;
                            } catch (Exception $e) {
                                error_log('Catched error in Ressio_CssOptimizer::cssIterateRules: ' . $e->getMessage());
                            }
                            $this->srcBase = $saveSrcBase;
                        }
                    }
                    break;
                case 'Ressio_CSS_AtMedia':
                    /** @var Ressio_CSS_AtMedia $rule */
                    // @todo: compact "screen and acreen" to "screen"
                    $newMediaRules = $this->embedMedia($rule->rules, $rule->media);
                    foreach ($newMediaRules as $mediaRule) {
                        $this->cssIterateRules($mediaRule->rules, $rule->media);
                    }
                    if (end($newRules) instanceof Ressio_CSS_AtMedia) {
                        array_unshift($newMediaRules, array_pop($newRules));
                    }
                    // merge identical @media rules
                    $lastMedia = false;
                    $mediaRules = array();
                    /** @var Ressio_CSS_AtMedia $mediaRule */
                    foreach ($newMediaRules as $mediaRule) {
                        if ($mediaRule->media === $lastMedia) {
                            $lastMediaRules = array_pop($mediaRules);
                            $mediaRules[] = new Ressio_CSS_AtMedia($lastMedia, array_merge($lastMediaRules->rules, $mediaRule->rules));
                        } else {
                            $mediaRules[] = $mediaRule;
                            $lastMedia = $mediaRule->media;
                        }
                    }
                    $rule = $mediaRules;
                    break;
                case 'Ressio_CSS_AtSupports': //@todo: remove vendor-prefixed features
                case 'Ressio_CSS_AtHost':
                case 'Ressio_CSS_AtRegion':
                case 'Ressio_CSS_AtPage':
                case 'Ressio_CSS_AtGeneral':
                    /** @var Ressio_CSS_AtGeneral $rule */
                    $this->cssIterateRules($rule->rules, '');
                    break;
                case 'Ressio_CSS_AtNamespace':
                    /** @var Ressio_CSS_AtNamespace $rule */
                    break;
            }
            if (!empty($rule)) {
                if (is_array($rule)) {
                    foreach ($rule as $item) {
                        $newRules[] = $item;
                    }
                } else {
                    $newRules[] = $rule;
                }
            }
        }
        $rules = $newRules;
    }

    /**
     * @param $declarations Ressio_CSS_Declarations
     */
    private function cssIterateDeclarations(&$declarations)
    {
        $newRules = array();
        /** @var Ressio_CSS_Declaration|Ressio_CSS_Comment $rule */
        foreach ($declarations->declarations as $rule) {
            $className = get_class($rule);
            switch ($className) {
                case 'Ressio_CSS_Comment':
                    if ($rule->comment === '' || $rule->comment{0} !== '!') {
                        $rule = null;
                    }
                    break;
                case 'Ressio_CSS_Declaration':
                    if ($this->browserVendor) {
                        if ($rule->prop{0} === '-') {
                            list($nop, $vendor, $property) = explode('-', $rule->prop, 3);
                            if ($vendor !== $this->browserVendor) {
                                $rule = null;
                                break;
                            }
                        }
                        if ($rule->value{0} === '-' && preg_match('/^-(\w+)-/', $rule->value, $m) && $m[1] !== $this->browserVendor) {
                            $rule = null;
                            break;
                        }
                    }
                    $rule->value = str_replace("\n", ' ', trim($rule->value));
                    if (preg_match('/\s\s/', $rule->value)) {
                        $rule->value = preg_replace('/(\'.*?\'|".*?"|[^\'"\s]+)+\s+/', '\1 ', $rule->value);
                    }
                    if ((strpos($rule->value, 'url(') !== false) && ($this->srcBase !== $this->targetBase)) {
                        $rule->value = preg_replace_callback(
                            '#(?<=url\\() *(([\'"])(?:\\\\.|[^\\\\ ]+)*?\2|[^\'") ](?:\\\\.|[^\'")\\\\]+)*?|) *(?=\))#',
                            array($this, 'replaceUrlsCallback'),
                            $rule->value
                        );
                    }
                    break;
            }
            if (!empty($rule)) {
                $newRules[] = $rule;
            }
        }
        $declarations->declarations = $newRules;
    }
}
