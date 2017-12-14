<?php
/*
#------------------------------------------------------------------------
# Package - JoomlaMan JMSlideShow
# Version 1.0
# -----------------------------------------------------------------------
# Author - JoomlaMan http://www.joomlaman.com
# Copyright © 2012 - 2013 JoomlaMan.com. All Rights Reserved.
# @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
# Websites: http://www.JoomlaMan.com
#------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') || die('Restricted access');
if (!defined('DS'))
  define('DS', '/');
if (!defined('JM_NEWS_PRO_IMAGE_FOLDER')) {
  define('JM_NEWS_PRO_IMAGE_FOLDER', JPATH_SITE . DS . 'media' . DS . 'mod_jmnewspro');
}
if (!defined('JM_NEWS_PRO_IMAGE_PATH')) {
  define('JM_NEWS_PRO_IMAGE_PATH', JURI::root(true) . '/media/mod_jmnewspro');
}
if (!file_exists(JM_NEWS_PRO_IMAGE_FOLDER)) {
  @mkdir(JM_NEWS_PRO_IMAGE_FOLDER, 0755) or die('Please change the permission');
}
if (!class_exists('JMNewsProSlide')) {
  require_once JPATH_SITE . DS . 'modules' . DS . 'mod_jmnewspro' . DS . 'classes' . DS . 'slide.php';
}
// Include the syndicate functions only once
require_once (dirname(__file__) . DS . 'helper.php');
$module_id = $module->id;
$slides = ModJmNewsProHelper::getSlides($params);
//print_r($slides);
$doc = JFactory::getDocument();
$jmnewspro_item_width = $params->get('jmnewspro_item_width', 200);
$jmnewspro_item_height = $params->get('jmnewspro_item_height', 200);
$jmnewspro_minslide = $params->get('jmnewspro_minslide', 1);
$jmnewspro_maxslide = $params->get('jmnewspro_maxslide', 3);
$jmnewspro_moveslide = $params->get('jmnewspro_moveslide', 0);
$jmnewspro_slidemargin = $params->get('jmnewspro_slidemargin', 10);
$moduleclass_sfx = $params->get('moduleclass_sfx');
$jmnewspro_viewall = $params->get('jmnewspro_viewall','');
$jmnewspro_viewalltext = $params->get('jmnewspro_viewalltext','View All');
$jmnewspro_header = $params->get('jmnewspro_header','');
$jmnewspro_show_nav_buttons = $params->get('jmnewspro_show_nav_buttons', 0);
$jmnewspro_show_pager = $params->get('jmnewspro_show_pager', 0);
$jmnewspro_show_title = $params->get('jmnewspro_show_title', 1);
$jmnewspro_show_desc = $params->get('jmnewspro_show_desc', 1);
$jmnewspro_show_image = $params->get('jmnewspro_show_image', 1);
$jmnewspro_show_readmore = $params->get('jmnewspro_show_readmore', 0);
$jmnewspro_readmore_text = $params->get('jmnewspro_readmore_text', 'Read more');
$jmnewspro_hover = $params->get('jmnewspro_hover', 0);
$jmnewspro_pager_position = $params->get('jmnewspro_pager_position', 'bottomright');
$jmnewspro_image_link = $params->get('jmnewspro_image_link', 'bottomright');
$jmnewspro_show_category = $params->get('jmnewspro_show_category', 0);
$jmnewspro_auto = $params->get('jmnewspro_auto', 'true');
$jmnewspro_timeout = $params->get('jmnewspro_timeout', 4000);
$jmnewspro_onhover = $params->get('jmnewspro_onhover', 'true');
$jmnewspro_speed = $params->get('jmnewspro_speed', 500);
$jmnewspro_touch = $params->get('jmnewspro_touch', 0);
$jmnewspro_easing = $params->get('jmnewspro_easing', 'swing');
$jmnewspro_mode = $params->get('jmnewspro_mode', 'horizontal');
$jmnewspro_usecss = $params->get('jmnewspro_usecss', false);
$resize = $params->get('jmnewspro_resize', 0);
$jmnewspro_show_price = $params->get('jmnewspro_show_price', 1);
$jmnewspro_show_popup = $params->get('jmnewspro_show_popup', 1);
$jmnewspro_popup_text = $params->get('jmnewspro_popup_text', '');
$jmnewspro_colorbox_style = $params->get('jmnewspro_colorbox_style', 'colorbox.css');
$jmnewspro_colorbox_transition = $params->get('jmnewspro_colorbox_transition', '');
$jmnewspro_colorbox_width = $params->get('jmnewspro_colorbox_width', 'auto');
$jmnewspro_colorbox_height = $params->get('jmnewspro_colorbox_height', 'auto');
$jmnewspro_text_next = $params->get('jmnewspro_text_next', 'Next');
$jmnewspro_text_prev = $params->get('jmnewspro_text_prev', 'Prev');
$jmnewspro_layout = $params->get('jmnewspro_layout', 'default');
$jmnewspro_include_jquery = $params->get('jmnewspro_include_jquery', 0);
$jmnewspro_next_selector = $params->get('jmnewspro_next_selector', '.jmnewspro-next');
$jmnewspro_prev_selector = $params->get('jmnewspro_prev_selector', '.jmnewspro-prev');
$data_controls = empty($jmnewspro_show_nav_buttons) ? 'false' : 'true';
$jm_params = 'data-resize="'.$resize.'" data-useCSS="'.$jmnewspro_usecss.'" data-mode="'.$jmnewspro_mode.'" data-easing="'.$jmnewspro_easing.'" data-slideSelector="" data-slideWidth="'.$jmnewspro_item_width.'" data-minSlides="'.$jmnewspro_minslide.'" data-maxSlides="'.$jmnewspro_maxslide.'" data-moveSlides="'.$jmnewspro_moveslide.'" data-slideMargin="'.$jmnewspro_slidemargin.'" data-autoHover="'.$jmnewspro_onhover.'" data-speed="'.$jmnewspro_speed.'" data-adaptiveHeight="true" data-infiniteLoop="true" data-auto="'.$jmnewspro_auto.'" data-pause="'.$jmnewspro_timeout.'" data-controls="'.$data_controls.'" data-touchEnabled="'.$jmnewspro_touch.'"';
if(empty($jmnewspro_touch)) $jmnewspro_touch = 'false';
else $jmnewspro_touch = 'true';
//Finding for custom CSS in template
$custom_css = JPATH_SITE . '/templates/' . ModJmNewsProHelper::getTemplate() . '/css/' . $module->module . '_' . $jmnewspro_layout . '.css';
if (file_exists($custom_css)) {
  $doc->addStylesheet(JURI::root(true) . '/templates/' . ModJmNewsProHelper::getTemplate() . '/css/' . $module->module . '_' . $jmnewspro_layout . '.css');
} else if(file_exists(JPATH_SITE . '/modules/mod_jmnewspro/assets/css/mod_jmnewspro_' . $jmnewspro_layout . '.css')){
  $doc->addStylesheet(JURI::root(true) . '/modules/mod_jmnewspro/assets/css/mod_jmnewspro_' . $jmnewspro_layout . '.css');
}
if ($jmnewspro_include_jquery == 1) {
  $doc->addScript(JURI::root(true) . '/modules/mod_jmnewspro/assets/js/jquery-1.8.3.js');
}
global $jm_jquery_autoload;
if($jmnewspro_include_jquery==2 && empty($jm_jquery_autoload)):?>
<script type="text/javascript">
var jQueryScriptOutputted = false;
function JMInitJQuery() {
  if (typeof(jQuery) == 'undefined') {
    if (! jQueryScriptOutputted) {
      jQueryScriptOutputted = true;
      document.write("<scr" + "ipt type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js\"></scr" + "ipt>");
    }
    setTimeout("JMInitJQuery()", 50);
  }
}
JMInitJQuery();
</script>
<?php
$jm_jquery_autoload = 1;
endif;
global $jmnewspro_bxslider_load;
if (empty($jmnewspro_bxslider_load)) {
  $doc->addScript(JURI::root(true) . '/modules/mod_jmnewspro/assets/js/jquery.bxslider.js');
  $jmnewspro_bxslider_load = 1;
}
global $jmnewspro_easing_load;
if (empty($jmnewspro_easing_load)) {
	$doc->addScript(JURI::root(true). '/modules/mod_jmnewspro/assets/js/jquery.easing.1.3.js');
  $jmnewspro_easing_load = 1;
}
global $bxslider_extra;
if($bxslider_extra!=1){
	$doc->addScript(JURI::root(true). '/modules/mod_jmnewspro/assets/js/jquery.jm-bxslider.js');
	$bxslider_extra = 1;
}
require JModuleHelper::getLayoutPath('mod_jmnewspro', $jmnewspro_layout);