<!--layout:Portfolio,order:10-->
<?php
/* * --------------------------------------------------------------------
  # Package - JoomlaMan JMNewsPro
  # Version 1.0.1
  # --------------------------------------------------------------------
  # Author - JoomlaMan http://www.joomlaman.com
  # Copyright © 2012 - 2013 JoomlaMan.com. All Rights Reserved.
  # @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
  # Websites: http://www.JoomlaMan.com
  ---------------------------------------------------------------------* */
// no direct access
defined('_JEXEC') or die('Restricted access');
global $jmnewspro_istope_load;
$document = JFactory::getDocument();
global $direction;
if(!$direction){
	$document->addScript(JURI::root(true) . '/modules/mod_jmnewspro/assets/js/jquery.jm-direction.js');
	$direction = 1;
}
if($jmnewspro_show_popup) {
	$document->addScript(JURI::root(true) . '/modules/mod_jmnewspro/assets/js/jquery.colorbox-min.js');
	$document->addStyleSheet(JURI::root(true) . '/modules/mod_jmnewspro/assets/css/colorbox.css');
}
if(empty($jmnewspro_istope_load)){
    $document->addScript(JURI::root(true) . '/modules/mod_jmnewspro/assets/js/jquery.grid.min.js');
    $jmnewspro_istope_load = 1;
}
$slider_source = $params->get('slider_source', 1);
$result = ModJmNewsProHelper::getCategory($slider_source,$params);
if (empty($slides)) {
    print "There are no slide to show, Please make sure you have configured SlideShow correctly.";
    return;
}
$style = null;
if (!$jmnewspro_show_pager):       
    $style .= "#jmnewspro-$module->id .bx-controls{
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
	$style .="#jmnewspro-$module->id .slide-item-wrap{
				position: relative;
			} ";
$document->addStyleDeclaration($style);
?>
<!-- START Responsive Carousel MODULE -->
<ul id="filters" class="clearfix">
	<li class="current"><a data-filter="*">Show all</a></li>
<?php if($result){
	foreach($result as $i=>$cat){?>
		<li><a href="#" data-filter=".category<?php echo $cat->id;?>"><?php echo $cat->title;?></a></li>
<?php } }?>
</ul>
<div class="jmnewspro <?php echo $moduleclass_sfx; ?>  <?php echo ($params->get('jmnewspro_layout', 'default'))?' '.$params->get('jmnewspro_layout', 'default'):''?>" id="jmnewspro-<?php print $module->id; ?>">
    <div class="slider">
        <?php foreach ($slides as $slide): ?>
            <div class="slide-item category<?php echo $slide->category;?>" style="min-height:<?php print $jmnewspro_item_height; ?>px">
                <div class="slide-item-wrap">
                    <div class="slide-item-wrap-item">
                        <?php if($jmnewspro_show_image):?>
                            <div class="slide-item-image clearfix">
                                <?php if($jmnewspro_image_link):?>
                                <a target="<?php print $slide->target; ?>" href="<?php print $slide->link;?>"><img src="<?php echo str_replace(JPATH_SITE,JURI::base(),$slide->getMainImage()); ?>"></a>
                                <?php else:?>
                                <img src="<?php echo str_replace(JPATH_SITE,JURI::base(),$slide->getMainImage()); ?>">
                                <?php endif;?>
                            </div>
                        <?php endif; ?>
                        <?php if ($jmnewspro_show_title || $jmnewspro_show_desc || $jmnewspro_show_readmore): ?>
                            <div class="slide-item-desc-warp<?php print ($jmnewspro_hover) ? ' jmnewsprohover' : ''; ?>">
                                <div class="slide-inner">   <div class="padding">
                                    <?php if($jmnewspro_show_title):?>
                                    <div class="slide-item-title"><?php print $slide->title; ?></div>
                                    <?php endif;?>
                                    <?php if($jmnewspro_show_desc):?>
                                    <div class="slide-item-desc"><?php print $slide->description; ?></div>
                                    <?php endif;?>
                                    <?php if ($jmnewspro_show_readmore): ?>
                                        <span class="slide-item-readmore"><a target="<?php print $slide->target; ?>" href="<?php print $slide->link ?>"><?php echo $jmnewspro_readmore_text;?></a></span>					
                                    <?php endif; ?>
									<span class="slide-item-zoom">
									  <a class="colorbox" href="<?php echo str_replace(JPATH_SITE,JURI::base(),$slide->image); ?>" title="Open images">
									  <?php echo $jmnewspro_popup_text;?></span>
                                </div></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<!-- END Responsive Carousel MODULE -->
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.slider').JMGrid({
			filter:'#filters > li a',
			cols: <?php echo $jmnewspro_maxslide; ?>,
			item: '.slide-item',
			itemWidth: <?php print $jmnewspro_item_width;?>,
			itemHeight: <?php print $jmnewspro_item_height;?>,
			hiddenClass: 'jmhidden',
			margin:<?php print $jmnewspro_slidemargin;?>
		});
		jQuery('#filters a').click(function(){
			jQuery('#filters a').not(this).parent().removeClass('current');
			jQuery(this).parent().addClass('current');
			return false;
		})
		var obj = '#jmnewspro-<?php print $module->id; ?>';
		<?php if($jmnewspro_show_popup) {?>
		jQuery(".slide-item-zoom .colorbox").colorbox({rel:'.colorbox'<?php print $jmnewspro_colorbox_transition; ?>,width:'<?php print $jmnewspro_colorbox_width; ?>',height:'<?php print $jmnewspro_colorbox_height; ?>'});
		<?php }?>
		jQuery('.portfolio .slide-item-wrap').jmDeriction();
	});
</script>