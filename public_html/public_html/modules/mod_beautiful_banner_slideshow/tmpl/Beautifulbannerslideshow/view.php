<?php
/**
* @title		Beautiful Banner Slideshow
* @website		http://www.joombig.com
* @copyright	Copyright (C) 2013 joombig.com. All rights reserved.
* @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

    // no direct access
    defined('_JEXEC') or die;
?>
<script>
jQuery.noConflict(); 
</script>
<link rel="stylesheet" type="text/css" href="<?php echo $mosConfig_live_site; ?>/modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/css/wt-rotator.css"/>
<style>
	#main_beautiful_banner_slideshow{
		width:<?php echo $width_module;?>;
		margin:0 auto;
	}
	#main_beautiful_banner_slideshow .panel{
		width:100%;
	}
	#main_beautiful_banner_slideshow #timer{
		background-color:<?php echo $timer_color;?>;
	}
	#main_beautiful_banner_slideshow .panel{
		border: solid <?php echo $border_module;?>px <?php echo $border_color;?>;
	}
</style>
<?php if($background_style == 1){?>
	<style>
		#main_beautiful_banner_slideshow .inner-bg{
			background:<?php echo $background_box;?>;
			opacity:<?php echo $opacity_box;?> !important;
			border-radius:5px;
		}
	</style>
<?php } else {?>
	<style>
		#main_beautiful_banner_slideshow .inner-bg{
			background:transparent;
			border-radius:5px;
		}
	</style>
<?php }?>
<?php if($enable_jQuery == 1){?>
	<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/js/jquery-1.9.1.min.js"></script>
<?php }?>

<?php if($resize_img == 1){?>
	<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/js/jquery.wt-rotator-resize.min.js"></script>
<?php } else {?>
	<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/js/jquery.wt-rotator.min.js"></script>
<?php }?>
<script type="text/javascript" src="<?php echo $mosConfig_live_site; ?>/modules/mod_beautiful_banner_slideshow/tmpl/Beautifulbannerslideshow/js/preview.js"></script>   
<div id="main_beautiful_banner_slideshow">
<div class="panel">
<div class="beautifullcontainer">
        <div class="wt-rotator">
            <a href="#"></a>            
            <div class="desc"></div>
            <div class="preloader"></div>
            <div class="c-panel">
                <div class="buttons-beautifull">
                    <div class="prev-btn"></div>
                    <div class="play-btn"></div>    
                    <div class="next-btn"></div>               
                </div>
                <div class="thumbnails">
                    <ul>
					<?php foreach($data as $index=>$value) { ?>
                        <li>
                            <a href="<?php echo JURI::root().$value['image'] ?>"><a href="<?php echo $value['link'] ?>"></a></a>
							<?php if($show_des == 1){?>
								<div style="left:<?php echo $left_box;?>px; top:<?php echo $top_box;?>px; width: <?php echo ($width_box-20);?>px; height: <?php echo ($height_box-20);?>px;"> 
									<span class="cap-title" style="font-size:<?php echo $fontsize_title;?>px;color:<?php echo $color_title;?>;"><?php echo $value['shortDesc']?></span><br/>
									<span style="font-size:<?php echo $fontsize_des;?>px;color:<?php echo $color_des;?>;"><?php echo $value['introtext']?></span><br/>
									<div class="beutiful_readmore">
										<a href="<?php echo $value['link'] ?>"  style="font-size:<?php echo $fontsize_des;?>px;color:<?php echo $color_des;?>;font-style:italic;"><?php echo $value['readmore']?></a>
									</div>
								</div>     
							<?php }?>	
                        </li>   
					<?php } ?>	
                    </ul>
                </div>     
            </div>
        </div>	
  </div>
  </div>
</div>
<script>
	var calWidth, calHeight, cal_shuffle_img, cal_auto_play, cal_delay_time, cal_transition_speed, cal_style_des, cal_show_timer, call_show_thumb, call_show_nav;
	var parentWidth = document.getElementById("main_beautiful_banner_slideshow").clientWidth;
	calWidth = parentWidth;
	calHeight = <?php echo $height_module;?>;
	cal_shuffle_img = <?php echo $shuffle_img;?>;
	cal_auto_play = <?php echo $auto_play;?>;
	cal_delay_time = <?php echo $delay_time;?>;
	cal_transition_speed = <?php echo $transition_speed;?>;
	cal_style_des = <?php echo $style_des;?>;
	cal_show_timer = <?php echo $show_timer;?>;
	call_show_thumb = <?php echo $show_thumb;?>;
	call_show_nav = <?php echo $show_nav;?>;
</script>
