<?php
/**
* @title		Beautiful Banner Slideshow
* @website		http://www.joombig.com
* @copyright	Copyright (C) 2013 joombig.com. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
    // no direct access
    defined('_JEXEC') or die('Restricted access');
	$mosConfig_absolute_path = JPATH_SITE;
	$mosConfig_live_site = JURI :: base();
	if(substr($mosConfig_live_site, -1)=="/") { $mosConfig_live_site = substr($mosConfig_live_site, 0, -1); }

    $module_name             = basename(dirname(__FILE__));
    $module_dir              = dirname(__FILE__);
    $module_id               = $module->id;
    $document                = JFactory::getDocument();
    $style                   = $params->get('sp_style');

    if( empty($style) )
    {
        JFactory::getApplication()->enqueueMessage( 'Slider style no declared. Check Beautiful Banner Slideshow configuration and save again from admin panel' , 'error');
        return;
    }

    $layoutoverwritepath     = JURI::base(true) . '/templates/'.$document->template.'/html/'. $module_name. '/tmpl/'.$style;
    $document                = JFactory::getDocument();
    require_once $module_dir.'/helper.php';
    $helper = new mod_beautifulbannerslideshow($params, $module_id);
    $data = (array) $helper->display();
	$enable_jQuery 				= $params->get('enable_jQuery', 1);
	$width_module				= $params->get('width_module', "100%");
	$height_module 				= $params->get('height_module', "300");
	$border_module 				= $params->get('border_module', "20");
	$border_color 				= $params->get('border_color', "#333333");
	$resize_img 				= $params->get('resize_img', 1);
	$shuffle_img 				= $params->get('shuffle_img', 0);
	$auto_play 					= $params->get('auto_play', "1");
	$delay_time 				= $params->get('delay_time', "6000");
	$transition_speed 			= $params->get('transition_speed', "800");
	$show_timer 				= $params->get('show_timer', 1);
	$timer_color 				= $params->get('timer_color', "#FFF");
	$show_des 					= $params->get('show_des', "1");
	$style_des 					= $params->get('style_des', 1);
	$width_box					= $params->get('width_box', "auto");
	$height_box 				= $params->get('height_box', "auto");
	$top_box					= $params->get('top_box', "50");
	$left_box 					= $params->get('left_box', "15");
	$background_style 			= $params->get('background_style', 1);
	$background_box 			= $params->get('background_box', "#000011");
	
	$fontsize_title 			= $params->get('fontsize_title', "16");
	$color_title 				= $params->get('color_title', "#FFF");
	$fontsize_des 				= $params->get('fontsize_des', "14");
	$color_des 					= $params->get('color_des', "#FFF");
	
	$opacity_box 				= $params->get('opacity_box', "0.75");
	
	$show_thumb 			= $params->get('show_thumb', 1);
	$show_nav 				= $params->get('show_nav', 1);

    //$option = (array) $params->get('animation')->$style;
    if(  is_array( $helper->error() )  )
    {
        JFactory::getApplication()->enqueueMessage( implode('<br /><br />', $helper->error()) , 'error');
    } else {
        if( file_exists($layoutoverwritepath.'/view.php') )
        {
            require(JModuleHelper::getLayoutPath($module_name, $layoutoverwritepath.'/view.php') );   
        } else {
            require(JModuleHelper::getLayoutPath($module_name, $style.'/view') );   
        }

        $helper->setAssets($document, $style);
}