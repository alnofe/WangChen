<!--layout:Masonry,order:11-->
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
if($jmnewspro_show_popup) {
	$document->addScript(JURI::root(true) . '/modules/mod_jmnewspro/assets/js/jquery.colorbox-min.js');
	$document->addStyleSheet(JURI::root(true) . '/modules/mod_jmnewspro/assets/css/colorbox.css');
}
global $js_masonry;
if(!$js_masonry){
	$document->addScript(JURI::root(true) . '/modules/mod_jmnewspro/assets/js/masonry.pkgd.min.js');
	$js_masonry = 1;
}
global $direction;
if(!$direction){
	$document->addScript(JURI::root(true) . '/modules/mod_jmnewspro/assets/js/jquery.jm-direction.js');
	$direction = 1;
}
if (empty($slides)) {
	echo "There are no slide to show, Please make sure you have configured SlideShow correctly.";
	return;
}
$style = "	.slide-item {float:left}
			.slide-item img{width:100%}
			.slide-item.w1 { width: 25%; }
			.slide-item.w2 { width: 50%; }
		";
$document->addStyleDeclaration($style);
?>   
<!-- START Responsive Carousel MODULE -->
<div class="jmnewspro <?php echo $moduleclass_sfx; ?> css3 <?php echo $params->get('jmnewspro_layout', 'default');?>" id="jmnewspro-<?php echo $module->id; ?>">
  <div id="jmnewpro-masonry">
    <?php foreach ($slides as $i => $slide): ?>
			<div class="slide-item <?php echo $slide->content_type;?>">
        <div class="slide-item-wrap">
          <div class="view slide-item-wrap-item">
            <?php if ($jmnewspro_show_image): ?>
              <div class="slide-item-image clearfix">
                <?php if ($jmnewspro_image_link): ?>
                  <a href="<?php echo $slide->link; ?>"><img alt="" src="<?php echo str_replace(JPATH_SITE,JURI::base(),$slide->getMainImage()); ?>"></a>
                <?php else: ?>
                  <img alt="" src="<?php echo str_replace(JPATH_SITE,JURI::base(),$slide->getMainImage()); ?>">
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <?php if ($jmnewspro_show_title || $jmnewspro_show_desc || $jmnewspro_show_category): ?>
              <div class="slide-item-desc-warp<?php echo ($jmnewspro_hover) ? ' jmnewsprohover' : ''; ?>">
                <article class="slide-inner">
					<div class="padding">
						<?php if ($jmnewspro_show_title): ?>
						<h2 class="entry-title slide-item-title"><?php echo $slide->title; ?></h2>
						<?php endif; ?>
						<?php if($jmnewspro_show_category):?>
						<div class="category"><?php echo $slide->category_name;?></div>
						<?php endif;?>
						<?php if ($jmnewspro_show_desc): ?>
						<div class="slide-item-desc"><?php echo $slide->description; ?></div>
						<?php endif; ?>
					</div>
					<?php if ($jmnewspro_show_readmore || $jmnewspro_show_popup): ?>
					<div class="detailButtonWrap">
						<?php if ($jmnewspro_show_readmore): ?>
							<a class="slide-item-readmore" title="<?php echo $jmnewspro_readmore_text; ?>" href="<?php echo $slide->link ?>">
								<?php echo $jmnewspro_readmore_text;?>
							</a>
						<?php endif; ?>
						<?php if ($jmnewspro_show_popup):?>
							<a class="slide-item-readmore slide-item-zoom colorbox" title="<?php print $jmnewspro_popup_text;?>" href="<?php echo str_replace(JPATH_SITE,JURI::base(),$slides[$i]->image); ?>">
								<?php echo $jmnewspro_popup_text;?>
							</a>
						<?php endif;?>
					</div>
					<?php endif;?>
                </article>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
		<div style="clear:both"></div>
  </div>
</div>
<!-- END Responsive Carousel MODULE -->

<script type="text/javascript">
	function jmmasonry(){
		var colwidth = Math.floor(jQuery(window).width()/4);
		if(jQuery(window).width()<768){
			colwidth = jQuery(window).width();
		}
		jQuery('#jmnewpro-masonry .slide-item').each(function(){
			cols = Math.round(jQuery(this).width()/colwidth);
			cols = (cols>2)?2:cols;
			if(jQuery(window).width()<768){
				cols = 1;
			}
			jQuery(this).width(colwidth * cols);
			jQuery(this).find('.padding').css({
				paddingTop: ((jQuery(this).height() - 60)/2) +'px'
			})
		})
		setTimeout(function(){
			jQuery('#jmnewpro-masonry').masonry({
				// options
				columnWidth: colwidth,
				itemSelector: '.slide-item'
			});
		},500);
	}
	jQuery(document).ready(function($){
		jmmasonry();
		jQuery('.layout10 .slide-item-wrap').jmDeriction();
		<?php if($jmnewspro_show_popup):?>
		jQuery("#jmnewspro-<?php echo $module->id; ?> .colorbox").colorbox({rel:'.colorbox','maxWidth':'100%'});
		<?php endif;?>
	}).resize(function(){
		jQuery('#jmnewpro-masonry').masonry('destroy');
		jmmasonry();
	})
</script>