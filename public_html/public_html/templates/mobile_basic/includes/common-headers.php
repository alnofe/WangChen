<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.10
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
?>
<meta charset="utf-8">
<meta name="HandheldFriendly" content="True">
<meta name="MobileOptimized" content="<?php echo $mjDevice->screenwidth; ?>">
<?php if(!isset($this->params) || !$this->params->get('zoom')) : ?>
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
<?php else: ?>
<meta name="viewport" content="width=device-width">
<?php endif; ?>
<meta http-equiv="cleartype" content="on">
<?php if(!isset($this->params) || !$this->params->get('detectnumber')) : ?>
<meta name="format-detection" content="telephone=no">
<meta http-equiv="x-rim-auto-match" content="none">
<?php endif; ?>
<?php if(!isset($this->params) || !$this->params->get('detectaddress')) : ?>
<meta name="format-detection" content="address=no">
<?php endif; ?>
<script>(function(d){if(navigator.userAgent.match(/IEMobile\/10\.0/)){var s=d.createElement("style");s.appendChild(d.createTextNode("@-ms-viewport{width:auto!important}"));d.getElementsByTagName("head")[0].appendChild(s)}})(document);</script>