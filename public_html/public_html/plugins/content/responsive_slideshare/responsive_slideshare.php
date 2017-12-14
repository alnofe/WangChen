<?php
/**
* @package   Responsive Slideshare Plugin
* @author    ColorPack Creations Co., Ltd.  http://www.colorpack.co.th
* @copyright Copyright (C) ColorPack Creations Co., Ltd. 
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// Assert file included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
* Responsive Slideshare Content Plugin
*
*/
class plgContentResponsive_Slideshare extends JPlugin
{

	/**
	*
	* @param object $subject The object to observe
	* @param object $params The object that holds the plugin parameters
	*/
	function PluginResponsive_Slideshare( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}

	/**
	* Example prepare content method
	*
	* Method is called by the view
	*
	* @param object The article object. Note $article->text is also available
	* @param object The article params
	* @param int The 'page' number
	*/
	function onContentPrepare( $context, &$article, &$params, $page = 0)
		{
		global $mainframe;
		$document = & JFactory::getDocument();
    	$document->addStyleSheet(JURI::base(). "plugins/content/responsive_slideshare/responsive_slideshare.css");


		if ( JString::strpos( $article->text, '{slideshare}' ) === false ) {
		return true;
		}
		
		$article->text = preg_replace('|{slideshare}(.*){\/slideshare}|e', '$this->embedVideo("\1")', $article->text);
		
			

		return true;
	
	}

	function embedVideo($vCode)
	{

	 	$params = $this->params;

		$width = $params->get('width', 640);
		$height = $params->get('height', 360);
	
			return '<div style="max-width:'.$width.'px;max-height:'.$height.'px" class="responsive-embed"><iframe src="http://www.slideshare.net/slideshow/embed_code/'.$vCode.'" frameborder="0" allowfullscreen></iframe></div>';
	}

}
