<?php

/**
 * RESS.IO Responsive Server Side Optimizer
 * http://ress.io/
 *
 * @copyright   Copyright (C) 2013-2015 Kuneri, Ltd. All rights reserved.
 * @license     GNU General Public License version 2
 */
class Ressio_CssParser
{
    protected $css = '';

    /**
     * Positional.
     */
    protected $lineno = 1;
    protected $column = 1;

    /**
     * @param $css string
     * @return Ressio_CSS_Stylesheet
     */
    public function parse($css)
    {
        /* 3.3. Preprocessing the input stream */
        $css = str_replace(array("\r\n", "\r", "\x0C"), "\n", $css); // CR
        $css = str_replace("\x00", "\xEF\xBF\xBD", $css); // UTF 0FFFD

        $this->css = $css;
        return new Ressio_CSS_Stylesheet($this->parseRules());
    }

    /**
     * Error `msg`.
     * @param $msg string
     * @throws Exception
     */
    private function error($msg)
    {
        throw new Exception($msg . ' near line ' . $this->lineno . ':' . $this->column);
    }

    /**
     * Update lineno and column based on `str`.
     * @param $str string
     */
    private function updatePosition($str)
    {
        if (strpos($str, "\n") !== false) {
            $this->lineno += substr_count($str, "\n");
            $this->column = strlen($str) - strrpos($str, "\n");
        } else {
            $this->column += strlen($str);
        }
    }

    /**
     * Match `re` and return captures.
     * @param $re string
     * @return string[]|bool
     */
    private function match($re)
    {
        if (!preg_match('/^' . $re . '/s', $this->css, $m)) {
            return false;
        }
        $str = $m[0];

        $this->updatePosition($str);
        $this->css = (string)substr($this->css, strlen($str));
        return $m;
    }

    /**
     * Parse ruleset.
     * @return array
     */
    private function parseRules()
    {
        $rules = array();
        $this->parseWhitespace();
        $this->parseComments($rules);
        while ($this->css !== '' && $this->css{0} !== '}') {
            $node = ($this->css{0} === '@') ? $this->parseAtRule() : $this->parseRule();
            if ($node) {
                $rules[] = $node;
                $this->parseComments($rules);
            } else {
                $this->error('Rule not found');
            }
        }
        return $rules;
    }

    /**
     * Parse whitespace.
     */
    private function parseWhitespace()
    {
        $this->match('\s*');
    }

    /**
     * Opening brace.
     * @return string[]|bool
     */
    private function parseOpenBracket()
    {
        if ($this->css{0} !== '{') {
            return false;
        }
        return $this->match('\{\s*');
    }

    /**
     * Closing brace.
     * @return string[]|bool
     */
    private function parseCloseBracket()
    {
        if ($this->css{0} !== '}') {
            return false;
        }
        $this->updatePosition('}');
        $this->css = (string)substr($this->css, 1);
        return array('}');
    }

    /**
     * Parse comments;
     * @param $rules array
     */
    private function parseComments(&$rules)
    {
        while ($c = $this->parseComment()) {
            $rules[] = $c;
        }
    }

    /**
     * Parse comment.
     * @return Ressio_CSS_Comment|bool
     */
    private function parseComment()
    {
        if ($this->css === '' || $this->css{0} !== '/' || $this->css{1} !== '*') {
            return false;
        }

        $i = strpos($this->css, '*/', 2);
        if ($i === false) {
            $i = strlen($this->css);
        }
        $i += 2;

        if ($this->css{$i - 1} === '') {
            $this->error('End of comment missing');
        }

        $this->column += 2; // '/*'
        $str = substr($this->css, 2, $i - 4);
        $this->updatePosition($str);
        $this->column += 2; // '*/'
        $this->css = (string)substr($this->css, $i);

        $this->parseWhitespace();

        return new Ressio_CSS_Comment($str);
    }

    /**
     * Parse rule.
     * @return Ressio_CSS_Rule
     */
    private function parseRule()
    {
        /** @var array $sel */
        $sel = $this->parseSelector();

        if (!$sel) {
            $this->error('selector missing');
        }

        return new Ressio_CSS_Rule($sel, $this->parseDeclarations());
    }

    /**
     * Parse selector.
     * @return string[]|bool
     */
    private function parseSelector()
    {
        $m = $this->match('(?:[^{\/\\\\]+|\\\\.|\/\*.*?\*\/|\/(?!\*))+');
        if (!$m) {
            return false;
        }
        /* Remove comments from selectors */
        return explode(',', preg_replace('/\/\*.*?\*\/+?/s', '', $m[0]));
    }

    /**
     * Parse declarations.
     * @return Ressio_CSS_Declarations
     */
    private function parseDeclarations()
    {
        if (!$this->parseOpenBracket()) {
            $this->error("missing '{'");
        }

        $declarations = array();
        $this->parseComments($declarations);
        $this->match(';*');

        // declarations
        while ($declaration = $this->parseDeclaration()) {
            $declarations[] = $declaration;
            $this->parseComments($declarations);
            $this->match(';*');
        }

        if (!$this->parseCloseBracket()) {
            $this->error("missing '}'");
        }

        $this->parseWhitespace();

        return new Ressio_CSS_Declarations($declarations);
    }

    /**
     * Parse declaration.
     * @return mixed
     */
    private function parseDeclaration()
    {
        // prop
        $i = strpos($this->css, ':');
        if ($i === false) {
            return false;
        }

        $prop = substr($this->css, 0, $i);
        if (preg_match('/^\*?[-a-z]+\s*$/', $prop)) {
            $this->updatePosition($prop);
            $this->css = substr($this->css, $i);
            $prop = trim($prop);
        } else {
            $prop = $this->match('[-\*]?(?:[_a-z]|[^\\0-\\237]|\\\\[0-9a-f]{1,6}\s?|\\\\[^\n0-9a-f])(?:[_a-z0-9-]|[^\\0-\\237]|\\\\[0-9a-f]{1,6}\s?|\\\\[^\n0-9a-f])*\s*(?=:)');
            if (!$prop) {
                return false;
            }
            $prop = trim($prop[0]);
        }

        // :
        if (!$this->match(':\s*')) {
            //$this->error("property missing ':'");
            $val = $this->match('[^;}]*[;\s]*');
            return new Ressio_CSS_Comment($prop . $val[0]);
        }

        // val
        $val = $this->match('(?:\'(?:\\\\\'|.)*?\'|"(?:\\\\"|.)*?"|\([^\)]*\)|\/\*.*?\*\/|\/(?!\*)|[^};\'"\(\/]+)+');
        if (!$val) {
            //$this->error('property missing value');
            $val = $this->match('[^;}]*[;\s]*');
            return new Ressio_CSS_Comment($prop . ':' . $val[0]);
        }

        // ;
        $this->match('[;\s]*');

        $prop = preg_replace('#/\*[^*]*?\*+(?:[^/*][^*]*?\*+)*?/#', '', $prop);
        $val = preg_replace('#/\*[^*]*?\*+(?:[^/*][^*]*?\*+)*?/#', '', trim($val[0]));

        return new Ressio_CSS_Declaration($prop, $val);
    }

    /**
     * Parse at rule.
     * @return object|bool
     */
    private function parseAtRule()
    {
        if ($ret = $this->parseAtMedia()) {
            return $ret;
        }
        if ($ret = $this->parseAtKeyframes()) {
            return $ret;
        }
        if ($ret = $this->parseAtFontface()) {
            return $ret;
        }
        if ($ret = $this->parseAtViewport()) {
            return $ret;
        }
        if ($ret = $this->parseAtImport()) {
            return $ret;
        }
        if ($ret = $this->parseAtCharset()) {
            return $ret;
        }
        if ($ret = $this->parseAtDocument()) {
            return $ret;
        }
        if ($ret = $this->parseAtPage()) {
            return $ret;
        }
        if ($ret = $this->parseAtSupports()) {
            return $ret;
        }
        if ($ret = $this->parseAtNamespace()) {
            return $ret;
        }
        if ($ret = $this->parseAtHost()) {
            return $ret;
        }
        if ($ret = $this->parseAtRegion()) {
            return $ret;
        }
        if ($ret = $this->parseAtGeneral()) {
            return $ret;
        }

        return false;
    }

    /**
     * @return Ressio_CSS_AtGeneral|bool
     * @throws Exception
     */
    private function parseAtGeneral()
    {
        $m = $this->match('@([^;{]+)');
        if (!$m) {
            return false;
        }

        $name = rtrim($m[1]);

        $rules = null;
        if (!$this->match(';')) {
            if (!$this->parseOpenBracket()) {
                $this->error("general @-rule missing '{'");
            }
            $rules = $this->parseRules();
            if (!$this->parseCloseBracket()) {
                $this->error("general @-rule missing '}'");
            }
        }
        $this->parseWhitespace();

        return new Ressio_CSS_AtGeneral($name, $rules);
    }

    /**
     * Parse keyframes.
     * @return Ressio_CSS_AtKeyframes|bool
     */
    private function parseAtKeyframes()
    {
        $m = $this->match('@([-\w]+?)?keyframes\s+');
        if (!$m) {
            return false;
        }

        $vendor = isset($m[1]) ? $m[1] : '';

        // identifier
        $m = $this->match('([-\w]+)\s*');
        if (!$m) {
            $this->error('@keyframes missing name');
        }
        $name = $m[1];

        if (!$this->parseOpenBracket()) {
            $this->error("@keyframes missing '{'");
        }

        $frames = array();
        $this->parseComments($frames);
        while ($frame = $this->parseKeyframe()) {
            $frames[] = $frame;
            $this->parseComments($frames);
        }

        if (!$this->parseCloseBracket()) {
            $this->error("@keyframes missing '}'");
        }

        $this->parseWhitespace();

        return new Ressio_CSS_AtKeyframes($vendor, $name, $frames);
    }

    /**
     * Parse keyframe.
     * @return Ressio_CSS_Keyframe|bool
     */
    private function parseKeyframe()
    {
        $vals = array();

        while ($m = $this->match('((?:\d+?\.\d+?|\.\d+?|\d+?)%?|[a-z]+?)\s*')) {
            $vals[] = $m[1];
            $this->match(',\s*');
        }

        if (!count($vals)) {
            return false;
        }

        $this->parseWhitespace();

        return new Ressio_CSS_Keyframe($vals, $this->parseDeclarations());
    }

    /**
     * Parse media.
     * @return Ressio_CSS_AtMedia|bool
     */
    private function parseAtMedia()
    {
        $m = $this->match('@media(?=[ (])\s*([^{]+)');
        if (!$m) {
            return false;
        }

        $media = trim($m[1]);

        if (!$this->parseOpenBracket()) {
            $this->error("@media missing '{'");
        }

        $rules = $this->parseRules();

        if (!$this->parseCloseBracket()) {
            $this->error("@media missing '}'");
        }

        $this->parseWhitespace();

        return new Ressio_CSS_AtMedia($media, $rules);
    }

    /**
     * Parse supports.
     * @return Ressio_CSS_AtSupports|bool
     */
    private function parseAtSupports()
    {
        $m = $this->match('@supports\s+([^{]+)');
        if (!$m) {
            return false;
        }

        $supports = trim($m[1]);

        if (!$this->parseOpenBracket()) {
            $this->error("@supports missing '{'");
        }

        $rules = $this->parseRules();

        if (!$this->parseCloseBracket()) {
            $this->error("@supports missing '}'");
        }

        $this->parseWhitespace();

        return new Ressio_CSS_AtSupports($supports, $rules);
    }

    /**
     * Parse import
     * @return Ressio_CSS_AtImport|bool
     */
    private function parseAtImport()
    {
        $m = $this->match('@import\s+([^;\\n]+);');
        if (!$m) {
            return false;
        }

        $import = trim($m[1]);

        preg_match('/^(?:url\(\s*?(?:"([^"]*?)"|\'([^\']*?)\'|([^ )]*?))\)|"([^"]*?)"|\'([^\']*?)\'|([^ )]*?))\s*(.*?)$/', $import, $match);
        $url = $match[1] . $match[2] . $match[3] . $match[4] . $match[5] . $match[6];
        $media = $match[7];

        $this->parseWhitespace();

        return new Ressio_CSS_AtImport($url, $media);
    }

    /**
     * Parse charset
     * @return Ressio_CSS_AtCharset|bool
     */
    private function parseAtCharset()
    {
        $m = $this->match('@charset\s+([^;\\n]+);');
        if (!$m) {
            return false;
        }

        $this->parseWhitespace();

        return new Ressio_CSS_AtCharset(trim($m[1]));
    }

    /**
     * Parse namespace
     * @return Ressio_CSS_AtNamespace|bool
     */
    private function parseAtNamespace()
    {
        $m = $this->match('@namespace\s+([^;\\n]+);');
        if (!$m) {
            return false;
        }

        $this->parseWhitespace();

        return new Ressio_CSS_AtNamespace(trim($m[1]));
    }

    /**
     * Parse document.
     * @return Ressio_CSS_AtDocument|bool
     */
    private function parseAtDocument()
    {
        $m = $this->match('@([-\w]+?)?document\s+([^{]+)');
        if (!$m) {
            return false;
        }

        $vendor = trim($m[1]);
        $doc = trim($m[2]);

        if (!$this->parseOpenBracket()) {
            $this->error("@document missing '{'");
        }

        $rules = $this->parseRules();

        if (!$this->parseCloseBracket()) {
            $this->error("@document missing '}'");
        }

        $this->parseWhitespace();

        return new Ressio_CSS_AtDocument($vendor, $doc, $rules);
    }

    /**
     * Parse paged media.
     * @return Ressio_CSS_AtPage|bool
     */
    private function parseAtPage()
    {
        $m = $this->match('@page\s+');
        if (!$m) {
            return false;
        }

        $sel = $this->parseSelector();
        if (!$sel) {
            $sel = array();
        }

        if (!$this->parseOpenBracket()) {
            $this->error("@page missing '{'");
        }

        $declarations = array();
        $this->parseComments($declarations);
        // declarations
        while ($declaration = $this->parseDeclaration()) {
            $declarations[] = $declaration;
            $this->parseComments($declarations);
        }

        if (!$this->parseCloseBracket()) {
            $this->error("@page missing '}'");
        }

        $this->parseWhitespace();

        return new Ressio_CSS_AtPage($sel, $declarations);
    }

    /**
     * Parse host.
     * @return Ressio_CSS_AtHost|bool
     */
    private function parseAtHost()
    {
        $m = $this->match('@host\s*');
        if (!$m) {
            return false;
        }

        if (!$this->parseOpenBracket()) {
            $this->error("@host missing '{'");
        }

        $rules = $this->parseRules();

        if (!$this->parseCloseBracket()) {
            $this->error("@host missing '}'");
        }

        $this->parseWhitespace();

        return new Ressio_CSS_AtHost($rules);
    }

    /**
     * Parse font-face.
     * @return Ressio_CSS_AtFontface|bool
     */
    private function parseAtFontface()
    {
        $m = $this->match('@font-face\s*');
        if (!$m) {
            return false;
        }

        return new Ressio_CSS_AtFontface($this->parseDeclarations());
    }

    /**
     * Parse viewport.
     * @return Ressio_CSS_AtViewport|bool
     */
    private function parseAtViewport()
    {
        $m = $this->match('@([-\w]+?)?viewport\s*');
        if (!$m) {
            return false;
        }

        $vendor = isset($m[1]) ? $m[1] : '';

        return new Ressio_CSS_AtViewport($vendor, $this->parseDeclarations());
    }

    /**
     * Parse media.
     * @return Ressio_CSS_AtRegion|bool
     */
    private function parseAtRegion()
    {
        $m = $this->match('@region\s+([^{]+)');
        if (!$m) {
            return false;
        }

        $region = trim($m[1]);

        if (!$this->parseOpenBracket()) {
            $this->error("@region missing '{'");
        }

        $rules = $this->parseRules();

        if (!$this->parseCloseBracket()) {
            $this->error("@region missing '}'");
        }

        $this->parseWhitespace();

        return new Ressio_CSS_AtRegion($region, $rules);
    }

}

class Ressio_CSS_Stylesheet
{
    /** @var array */
    public $rules;

    /**
     * @param $rules array
     */
    public function __construct($rules)
    {
        $this->rules = $rules;
    }

    /**
     * @param $stylesheet Ressio_CSS_Stylesheet
     */
    public function append($stylesheet)
    {
        $this->rules = array_merge($this->rules, $stylesheet->rules);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode($this->rules);
    }
}

class Ressio_CSS_Comment
{
    /** @var string */
    public $comment;

    /**
     * @param $comment string
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (empty($this->comment)) {
            return '';
        }
        return '/*' . $this->comment . '*/';
    }
}

class Ressio_CSS_Rule
{
    /** @var array */
    public $selector;
    /** @var Ressio_CSS_Declarations */
    public $declarations;

    /**
     * @param $selector array
     * @param $declarations Ressio_CSS_Declarations
     */
    public function __construct($selector, $declarations)
    {
        $this->selector = $selector;
        $this->declarations = $declarations;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->selector) || !count($this->selector)) {
            return '';
        }
        $declarations = (string)$this->declarations;
        if ($declarations === '') {
            return '';
        }
        return implode(',', $this->selector) . $declarations;
    }
}

class Ressio_CSS_Declarations
{
    /** @var array */
    public $declarations;

    /**
     * @param $declarations array
     */
    public function __construct($declarations)
    {
        $this->declarations = $declarations;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->declarations) || !count($this->declarations)) {
            return '';
        }

        return '{' . implode(';', $this->declarations) . '}';
    }
}

class Ressio_CSS_Declaration
{
    /** @var string */
    public $prop;
    /** @var string */
    public $value;

    /**
     * @param $prop string
     * @param $value string
     */
    public function __construct($prop, $value)
    {
        $this->prop = $prop;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        // @todo: check escape
        return $this->prop . ':' . $this->value;
    }
}

class Ressio_CSS_AtGeneral
{
    /** @var string */
    public $name;
    /** @var array */
    public $rules;

    /**
     * @param $name string
     * @param $rules array
     */
    public function __construct($name, $rules = null)
    {
        $this->name = $name;
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->rules)) {
            return '@' . $this->name . ';';
        }
        return '@' . $this->name . '{' . implode($this->rules) . '}';
    }
}

class Ressio_CSS_AtMedia
{
    /** @var string */
    public $media;
    /** @var array */
    public $rules;

    /**
     * @param $media string
     * @param $rules array
     */
    public function __construct($media, $rules)
    {
        $this->media = $media;
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->rules) || !count($this->rules)) {
            return '';
        }
        return '@media ' . $this->media . '{' . implode($this->rules) . '}';
    }
}

class Ressio_CSS_AtPage
{
    /** @var string */
    public $selector;
    /** @var array */
    public $rules;

    /**
     * @param $selector string
     * @param $rules array
     */
    public function __construct($selector, $rules)
    {
        $this->selector = $selector;
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->rules) || !count($this->rules)) {
            return '';
        }
        if (!is_array($this->selector) || !count($this->selector)) {
            return '';
        }
        return '@page ' . implode(',', $this->selector) . '{' . implode($this->rules) . '}';
    }
}

class Ressio_CSS_AtDocument
{
    /** @var string */
    public $vendor;
    /** @var string */
    public $doc;
    /** @var array */
    public $rules;

    /**
     * @param $vendor string
     * @param $doc string
     * @param $rules array
     */
    public function __construct($vendor, $doc, $rules)
    {
        $this->vendor = $vendor;
        $this->doc = $doc;
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->rules) || !count($this->rules)) {
            return '';
        }
        return '@' . $this->vendor . 'document ' . $this->doc . '{' . implode($this->rules) . '}';
    }
}

class Ressio_CSS_AtKeyframes
{
    /** @var string */
    public $vendor;
    /** @var string */
    public $name;
    /** @var array */
    public $keyrules;

    /**
     * @param $vendor string
     * @param $name string
     * @param $keyrules array
     */
    public function __construct($vendor, $name, $keyrules)
    {
        $this->vendor = $vendor;
        $this->name = $name;
        $this->keyrules = $keyrules;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->keyrules) || !count($this->keyrules)) {
            return '';
        }
        return '@' . $this->vendor . 'keyframes ' . $this->name . '{' . implode($this->keyrules) . '}';
    }
}

class Ressio_CSS_Keyframe
{
    /** @var array */
    public $values;
    /** @var Ressio_CSS_Declarations */
    public $declarations;

    /**
     * @param $values array
     * @param $declarations Ressio_CSS_Declarations
     */
    public function __construct($values, $declarations)
    {
        $this->values = $values;
        $this->declarations = $declarations;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->values) || !count($this->values)) {
            return '';
        }
        $declarations = (string)$this->declarations;
        if ($declarations === '') {
            return '';
        }
        return implode(',', $this->values) . $declarations;
    }
}

class Ressio_CSS_AtSupports
{
    /** @var string */
    public $feature;
    /** @var array */
    public $rules;

    /**
     * @param $feature string
     * @param $rules array
     */
    public function __construct($feature, $rules)
    {
        $this->feature = $feature;
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->rules) || !count($this->rules)) {
            return '';
        }
        return '@supports ' . $this->feature . '{' . implode($this->rules) . '}';
    }
}

class Ressio_CSS_AtHost
{
    /** @var array */
    public $rules;

    /**
     * @param $rules array
     */
    public function __construct($rules)
    {
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->rules) || !count($this->rules)) {
            return '';
        }
        return '@host{' . implode($this->rules) . '}';
    }
}

class Ressio_CSS_AtImport
{
    /** @var string */
    public $url;
    /** @var string */
    public $media;

    /**
     * @param $url string
     * @param $media string
     */
    public function __construct($url, $media)
    {
        $this->url = $url;
        $this->media = $media;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $css = '@import ';
        $quote = (strpos($this->url, '"') === false) ? '"' : "'";
        $css .= $quote . $this->url . $quote;
        if (!empty($this->media)) {
            $css .= ' ' . $this->media;
        }
        $css .= ';';
        return $css;
    }
}

class Ressio_CSS_AtCharset
{
    /** @var string */
    public $charset;

    /**
     * @param $charset string
     */
    public function __construct($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '@charset ' . $this->charset . ';';
    }
}

class Ressio_CSS_AtNamespace
{
    /** @var string */
    public $namespace;

    /**
     * @param $namespace string
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '@namespace ' . $this->namespace . ';';
    }
}

class Ressio_CSS_AtFontface
{
    /** @var Ressio_CSS_Declarations */
    public $declarations;

    /**
     * @param $declarations Ressio_CSS_Declarations
     */
    public function __construct($declarations)
    {
        $this->declarations = $declarations;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $declarations = (string)$this->declarations;
        if ($declarations === '') {
            return '';
        }
        return '@font-face' . $declarations;
    }
}

class Ressio_CSS_AtViewport
{
    /** @var string */
    public $vendor;
    /** @var Ressio_CSS_Declarations */
    public $declarations;

    /**
     * @param $vendor string
     * @param $declarations Ressio_CSS_Declarations
     */
    public function __construct($vendor, $declarations)
    {
        $this->vendor = $vendor;
        $this->declarations = $declarations;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $declarations = (string)$this->declarations;
        if ($declarations === '') {
            return '';
        }
        return '@' . $this->vendor . 'viewport' . $declarations;
    }
}

class Ressio_CSS_AtRegion
{
    /** @var string */
    public $region;
    /** @var array */
    public $rules;

    /**
     * @param $region string
     * @param $rules array
     */
    public function __construct($region, $rules)
    {
        $this->region = $region;
        $this->rules = $rules;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!is_array($this->rules) || !count($this->rules)) {
            return '';
        }
        return '@region ' . $this->region . '{' . implode($this->rules) . '}';
    }
}
