<?php
/**
 * slideshare 1.4.0 for Joomla! 2.5 and 3.3 - 21 June 2014
 * @license http://www.gnu.org/licenses/gpl.html GNU/GPL.
 * @by ot2sen
 * @Copyright (C) 2008-2014 http://www.ot2sen.dk
  */
// no direct access
defined( '_JEXEC' ) or die;

class  plgContentSlideshare25 extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	 
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function onContentPrepare($context, &$article, &$params)
	{		
		$regex = '#{slideshare}(.*?){/slideshare}#s';
					
		preg_match_all($regex, $article->text, $matches);
		if ($matches) {
		$i=0;
			foreach ($matches[0] as $match) {
			

//Define the parameters
$slidewidth = $this->params->def('width', '427');
$slideheight = $this->params->def('height', '356');
$alignslide = $this->params->def('alignslide', 'center');
$related = $this->params->def('related', '0');

//Input embed code that needs to be customized
$original=$matches[1][$i];

//116 Extra replace to ensure embed urls transformed to valid xhtml (&amp;) in WISIWYG editor will be handled correctly
$tidiedoriginal = str_replace("&amp;","&", $original);

//Explode embedded code into parts to be able to identify id of various lengths
$embedParts = explode("&", $tidiedoriginal);

//Make sure trailing ] is to be removed from new type embed url without &w=425, and still keep backward compatibility of older embeds
if(isset($embedParts[1])){
$embedtidyup = explode("]", $embedParts[1]);
}
//Isolate unique id by removing [slideshare id= and store in $cleaned_id
$cleaned_id = explode("=", $embedParts[0]);

//Unique id is same as isolated cleaned
if(isset($cleaned_id[1])){
$cleaned_id2 = $cleaned_id[1];
}
else {
$cleaned_id2 = $cleaned_id;
}

$res = '<div align="'.$alignslide.'" id="__ss_'. $cleaned_id2.'">
<iframe  style="border:1px solid #CCC;border-width:1px 1px 0;margin-bottom:5px" src="http://www.slideshare.net/slideshow/embed_code/'. $cleaned_id2.'?rel='.$related.'" width="'.$slidewidth.'" height="'.$slideheight.'"  frameborder="0" marginwidth="0" marginheight="0" scrolling="no"> </iframe>
</div>';
$article->text = str_replace($match, $res, $article->text);
			$i++;
			}
		}	
	}
}