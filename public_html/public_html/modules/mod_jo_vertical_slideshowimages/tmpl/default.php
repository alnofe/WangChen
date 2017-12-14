<?php
 /*------------------------------------------------------------------------
# Mod_jo_vertical_slideshowimages - JO Vertical Slideshow images for Joomla 1.6, 1.7, 2.5 Plugin
# -----------------------------------------------------------------------
# author: http://www.joomcore.com
# copyright Copyright (C) 2011 Joomcore.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomcore.com
# Technical Support:  Forum - http://www.joomcore.com/Support
-------------------------------------------------------------------------*/
//No direct access!
defined('_JEXEC') or die('Restricted access');
include("modules/mod_jo_vertical_slideshowimages/tmpl/resize-class.php");
?>
<script type="text/javascript">
	var jo_jquery = jQuery.noConflict();
	jo_jquery(document).ready( function($){	
		var buttons = { 
			previous:$('#jo_vertical_img .button-previous') ,
			next:$('#jo_vertical_img .button-next') 
		};		
		$('#jo_vertical_img').lofJSidernews( { 
			interval:<?php echo $params->get("interval")?>,
		 	easing:'<?php echo $params->get("easing_type")?>',
			direction:'opacity',
			duration:1200,
			auto:<?php echo $params->get("auto_start")?>,
			navigatorHeight		: <?php echo $params->get("thumbnailheight")?>+15,
			navigatorWidth		: <?php echo $params->get("thumbnailwidth")?>+15,
			maxItemDisplay:<?php echo $params->get("maxitem")?>,
			buttons:buttons} );						
	});
</script>
<div id="jo_vertical_scrollingimages <?php echo $params->get('moduleclass_sfx')?>">
	<div id="jo_vertical_img" class="jo-vertical-slideimages" style="width:<?php echo $params->get('width')?>px; height:<?php echo $params->get('height')?>px;">
		<div class="preload"><div></div></div>            
	        <div  class="button-previous"></div>
	    	<div class="jo-vertical-main-slider-images" style="width:<?php echo $params->get('width')-$params->get('thumbnailwidth') - 15?>px; height:<?php echo $params->get('height')?>px;">
	                <ul class="jo-vertical-sliders-wrap-inner">
			<?php 
				$url 	= explode ( "\n", trim ( $params->get ( 'urls' ) ) );
				$i = 0;
				foreach($images as $image) {
			?>		
				<li>
					<?php 
					$image->folder	= str_replace( '\\', '/', $image->folder );
					$img = JHTML::_('image', $image->folder.'/'.$image->name, $image->name);
					if($params->get('add_url_image') == 1){
						echo '<a href="'.$url[$i].'">'.$img.'</a>';
					}else{
						echo $img;
					}
				?>
				</li>                
			<?php 
				$i = $i+1;
			}?> 
	      		</ul>  	
	        </div>
		 <!-- END MAIN CONTENT --> 
		<!-- NAVIGATOR -->
	           	<div class="jo-vertical-navigator-images">
				<div class="jo-vertical-navigator-wrapper">
					<ul class="jo-vertical-navigator-wrap-inner">
					<?php 
						$thumb_width = $params->get('thumbnailwidth');
						$thumb_height = $params->get('thumbnailheight');			
					//var_dump($url);
						foreach($images as $image) {
							$image->folder	= str_replace( '\\', '/', $image->folder );
							$image_url = $image->folder.'/'.$image->name;
							$image_thumb = $image->folder.'/thumb/thumb_'.$image->name;
							$resizeObj = new resize($image_url);
							$resizeObj -> resizeImage($thumb_width, $thumb_height, 'crop');
							$resizeObj -> saveImage($image_thumb, 100);
						?>	
						<li>
							<div>
								<img class="jo-vertical-thumbnail" src="<?php echo $image->folder.'/thumb/thumb_'.$image->name?>" alt="" title=""/>
							</div>    
						</li>
						<?php }?>			
					</ul>
				</div>	   
	            	</div> 
	   	 <div class="button-next"></div>
	</div> 
</div>

