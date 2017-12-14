<!--layout:Layout8,order:9-->
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
defined('_JEXEC') or die('Restricted access');
$document = JFactory::getDocument();
global $style_common;
if(empty($style_common)){
	$document->addStyleSheet(JURI::root(true) . '/modules/mod_jmnewspro/assets/css/style_common.css');
	$style_common = 1;
}
if($jmnewspro_show_popup) {
	$document->addScript(JURI::root(true) . '/modules/mod_jmnewspro/assets/js/jquery.colorbox-min.js');
	$document->addStyleSheet(JURI::root(true) . '/modules/mod_jmnewspro/assets/css/colorbox.css');
}
if (empty($slides)) {
  echo "There are no slide to show, Please make sure you have configured SlideShow correctly.";
  return;
}
$style = null;
if (!$jmnewspro_show_pager):       
    $style .= "#jmnewspro-$module->id .bx-pager{  
      display: none;
    }";
endif;
if (!$jmnewspro_show_nav_buttons):   
    $style .= "#jmnewspro-$module->id .bx-controls-direction{
      display: none;
    }";
endif;
if ($jmnewspro_pager_position == 'topleft'):
    $style .= "#jmnewspro-$module->id .bx-controls{
      left: 0; position: absolute; top: -15px;
    }";
elseif ($jmnewspro_pager_position == 'topright'):
    $style .= "#jmnewspro-$module->id .bx-controls{
      right: 0; position: absolute; top: -15px;
    }";
elseif ($jmnewspro_pager_position == 'bottomleft'):
    $style .= "#jmnewspro-$module->id .bx-controls{
      left: 0; position: absolute; bottom: -15px;
    }";
elseif ($jmnewspro_pager_position == 'bottomright'): 
    $style .= "#jmnewspro-$module->id .bx-controls{
      right: 0; position: absolute; bottom: -15px;
    }";
endif;
$document->addStyleDeclaration($style);
?>    
<!-- START Responsive Carousel MODULE -->
<div class="jmnewspro css3 <?php echo $moduleclass_sfx; ?> <?php echo $jmnewspro_layout; ?>" id="jmnewspro-<?php print $module->id; ?>">
  <div class="slider jm-bxslider" <?php echo $jm_params;?> data-onSliderLoad="jmnewspro<?php print $module->id?>()" data-nextSelector="#jmnewspro-<?php print $module->id; ?> <?php print $jmnewspro_next_selector;?>" data-prevSelector="#jmnewspro-<?php print $module->id; ?> <?php print $jmnewspro_prev_selector;?>" data-nextText="<?php print $jmnewspro_text_next;?>" data-prevText="<?php print $jmnewspro_text_prev;?>"> 
    <?php foreach ($slides as $i => $slide): ?>
      <div class="slide-item <?php echo $slide->content_type;?>" style="min-height:<?php print $jmnewspro_item_height; ?>px">
        <div class="slide-item-wrap">
          <div class="view slide-item-wrap-item">
            <?php if ($jmnewspro_show_image): ?>
              <div class="slide-item-image clearfix">
                <?php if ($jmnewspro_image_link): ?>
                  <a target="<?php print $slide->target; ?>" href="<?php print $slide->link; ?>"><img src="<?php echo str_replace(JPATH_SITE,JURI::base(),$slide->getMainImage()); ?>" alt="<?php print strip_tags($slide->title); ?>"></a>
                <?php else: ?>
                  <img src="<?php echo str_replace(JPATH_SITE,JURI::base(),$slide->getMainImage()); ?>" alt="<?php print strip_tags($slide->title); ?>">  
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <?php if ($jmnewspro_show_title || $jmnewspro_show_desc || $jmnewspro_show_category): ?>
              <div class="slide-item-desc-warp<?php print ($jmnewspro_hover) ? ' jmnewsprohover' : ''; ?>">
                <div class="slide-inner">
                  <div class="padding">
                    <?php if ($jmnewspro_show_title): ?>
                      <div class="slide-item-title"><?php print $slide->title; ?></div>
                    <?php endif; ?>
                    <?php if($jmnewspro_show_category):?>
                      <div class="category"><?php print $slide->category_name;?></div>
                    <?php endif;?>
                    <?php if ($jmnewspro_show_desc): ?>
                      <div class="slide-item-desc"><?php print $slide->description; ?></div>
                    <?php endif; ?>
					<?php if ($jmnewspro_show_price):?>
					<div class="slide-item-price">
					<?php print $slide->currency_symbol; ?><?php print $slide->price; ?></div>
					<?php endif; ?>
                    <?php if ($jmnewspro_show_readmore): ?>
                      <span class="slide-item-readmore"><a target="<?php print $slide->target; ?>" href="<?php print $slide->link ?>"><?php print $jmnewspro_readmore_text; ?></a></span>
                    <?php endif; ?>
					<?php if ($jmnewspro_show_popup):?>
						<span class="slide-item-zoom">				
								<a class="colorbox" href="<?php echo str_replace(JPATH_SITE,JURI::base(),$slides[$i]->image); ?>"> 	
									<?php print $jmnewspro_popup_text;?> 	
								</a>					
						</span>
					<?php endif;?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if (($jmnewspro_show_nav_buttons)&&($jmnewspro_next_selector)): ?>
	<div class="NavButtons BottomRight clearfix">
		<div class="Inner">
			<span class="jmnewspro-prev"></span>
			<span class="jmnewspro-next"></span>
		</div>
  </div>
  <?php endif; ?>
</div>
<!-- END Responsive Carousel MODULE -->

<script type="text/javascript">
	function jmnewspro<?php print $module->id?>(){
		<?php if($jmnewspro_show_popup):?>
		jQuery(".slide-item-zoom .colorbox").colorbox({rel:'.colorbox'<?php print $jmnewspro_colorbox_transition; ?>,width:'<?php print $jmnewspro_colorbox_width; ?>',height:'<?php print $jmnewspro_colorbox_height; ?>'});
		<?php endif;?>
	}
</script>