<?php
/*------------------------------------------------------------------------
# mod_jo_vertical_slideshowimages - JO Vertical Slideshow images for Joomla 1.6, 1.7, 2.5 Plugin
# -----------------------------------------------------------------------
# author: http://www.joomcore.com
# copyright Copyright (C) 2011 Joomcore.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomcore.com
# Technical Support:  Forum - http://www.joomcore.com/Support
-------------------------------------------------------------------------*/
 
//No direct access!
defined( "_JEXEC" ) or die( "Restricted access" );
jimport( 'joomla.filesystem.folder' );
require_once dirname(__FILE__).'/helper.php';

/* Get module parameters */
$doc = &Jfactory::getDocument();

if($params->get('leftright')=='left'){
	$doc->addStyleSheet(JURI::root().'modules/mod_jo_vertical_slideshowimages/css/left.css');
}else{	
	$doc->addStyleSheet(JURI::root().'modules/mod_jo_vertical_slideshowimages/css/right.css');
}	

$doc->addScript(JURI::root().'/modules/mod_jo_vertical_slideshowimages/js/jquery.js');
$doc->addScript(JURI::root().'/modules/mod_jo_vertical_slideshowimages/js/jquery.easing.js');
$doc->addScript(JURI::root().'/modules/mod_jo_vertical_slideshowimages/js/script.js');
$type = explode(',',trim($params->get('type')));
$folder	= JoVerticalSlideshowImages::getFolder($params);
$images	= JoVerticalSlideshowImages::getImages($params, $folder, $type);
$replace_folder	= str_replace( '\\', '/', $folder ).'thumb';
$res = JFolder::create($replace_folder, 0755);
$sliderWidth    = 	$params->get('width');
$sliderHeight   = 	$params->get('height');
$autoPlay   = 	$params->get('autoPlay');

if (!count($images)) {
	echo JText::_( 'No images ');
	return;
}

require(JModuleHelper::getLayoutPath('mod_jo_vertical_slideshowimages'));
?>