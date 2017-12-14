
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

	function jmnewspro99(){
			jQuery('#jmnewspro-99 .slide-item').hover(function(){
			var $sliderHeight = jQuery(this).height();
			var $sliderWidth = jQuery(this).width();
			jQuery(this).find('.jmnewsprohover').width($sliderWidth).stop(true,false).animate({
			  height: $sliderHeight+'px', opacity: 1
			},400);
		},function(){
			jQuery(this).find('.jmnewsprohover').stop(true,false).animate({
			  height: 0+'px', opacity: 0
			},400);
		})
				jQuery(".slide-item-zoom .colorbox").colorbox({rel:'.colorbox',width:'auto',height:'auto'});
		}
