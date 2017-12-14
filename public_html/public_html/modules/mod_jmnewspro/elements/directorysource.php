<?php
/*
#------------------------------------------------------------------------
# Package - JoomlaMan JMSlideShow
# Version 1.0
# -----------------------------------------------------------------------
# Author - JoomlaMan http://www.joomlaman.com
# Copyright Â© 2012 - 2013 JoomlaMan.com. All Rights Reserved.
# @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
# Websites: http://www.JoomlaMan.com
#------------------------------------------------------------------------
*/
//-- No direct access
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.html.html' );
class JFormFieldDirectorySource extends JFormField {
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'DirectorySource';
    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput() {
    	$jsons = json_decode($this->value);
        $items = $this->processjson($jsons);
        $doc = JURI::root();
        $num_items = count($jsons);
        $html='';
        // Initialize variables.
        $html .="<script src='{$doc}/modules/mod_jmnewspro/admin/assets/js/jquery.ui.min.js'></script>
				  <script>
				  jQuery('#{$this->id}_url').addClass('s9')
				  jQuery(function() {
				    jQuery( '#image_items' ).sortable();
				    jQuery( '#image_items' ).disableSelection();
				  });
				 </script>";
        $html .="<div class='s9'>";
        $html .="<div>
	        		<input id='addnew' class='btn btn-primary' name='addnew' type='button' value='ADD' onclick='addclick()'/>
	        		<input class='btn btn-danger' name='deleteall' type='button' value='DELETE ALL' onclick='deleteallclick()' />
	        		<input id='clear' class='btn btn-warning' name='clear' type='button' value='CLEAR' onclick='clearclick()'/>
	        		<input id='{$this->id}' type='hidden' name='{$this->name}' value='{$this->value}'/>
	        		<input id='juri' name='juri' type='hidden' value='{$doc}'/>
        		<div class='jm_clear'></div>
        		</div>
                <div id='image_items' class='clearfix'>
                   {$items}
                </div>";
        $html .="<script>
        		   var i = {$num_items};
        		   var json= '';
        		   var item_focus = '';
        		   var id_selected = '';
        		   var jm_juri = jQuery('#juri');
        		   var jm_title = jQuery('#{$this->id}_title');
        		   var jm_icon = jQuery('#{$this->id}_icon');
        		   var jm_link = jQuery('#{$this->id}_title_link');
        		   var jm_link_target = jQuery('#{$this->id}_title_link_target');
				   var jm_desc = jQuery('#{$this->id}_desc');
				   var jm_url = jQuery('#{$this->id}_url');
				   var jm_bt_add = jQuery('#addnew');
				   var jm_bt_clear = jQuery('#clear');
				   var jm_value = jQuery('#{$this->id}');
        		   clearclick();
        		   function clearclick()
        		   {
        		      if (jm_bt_clear.val() == 'clear')
        		      {
	        		      jm_title.val('');
	        		      jm_icon.val('');
						  jm_desc.val('');
						  jm_url.val('');
						  jm_link.val('');
					  }
					  else
					  {
					     jQuery('#item_'+id_selected+'').css('opacity','1');
					     jm_bt_clear.val('CLEAR');
					     jm_bt_add.val('ADD');
					     jm_title.val('');
					     jm_icon.val('');
						 jm_desc.val('');
						 jm_url.val('');
						 jm_link.val('');
    				  }
    			   }
    			   function addclick()
        		   {
        		      if ( jm_bt_add.val() == 'ADD'){
						  if (jm_url.val() != '')
						  {
						     var _json = '{\"url\":\"'+jm_url.val()+'\",\"title\":\"'+encodeURI(jm_title.val())+'\",\"link\":\"'+encodeURI(jm_link.val())+'\",\"target\":\"'+jm_link_target.val()+'\",\"desc\":\"'+encodeURI(jm_desc.val())+'\",\"icon\":\"'+encodeURI(jm_icon.val())+'\"}';
						     var _toolstip = 'Title : '+jm_title.val()+'&#10;Link : '+jm_link.val()+'&#10;Desc : '+jm_desc.val()+'';
						     jQuery('#image_items').append('<div id=item_'+i+' class=jm_items ><img class=jm_img src='+jm_juri.val()+jm_url.val()+' onmousemove=processjson() title=\"'+_toolstip+'\" height=60><button class=jm_button type=button onclick=removeclick('+i+') >remove</button><button id=edit_'+i+' class=jm_button type=button onclick=edititem('+i+') >edit</button><input id=value_'+i+' type=hidden value='+_json+' /><div class=jm_clear></div></div>');
	   					     processjson();
	   					     addEffect('item_'+i+'');
	   					     i++;
	    				  }
	   					  else
	   					  {
	   					     alert('Select one image');
	                      }
	                   }
	                   else {
	                      item_focus.focus();
	                      var _json = '{\"url\":\"'+jm_url.val()+'\",\"title\":\"'+encodeURI(jm_title.val())+'\",\"link\":\"'+encodeURI(jm_link.val())+'\",\"target\":\"'+jm_link_target.val()+'\",\"desc\":\"'+encodeURI(jm_desc.val())+'\",\"icon\":\"'+encodeURI(jm_icon.val())+'\"}';
	                      var _toolstip = 'Title:'+jm_title.val()+'&#10;Link:'+jm_link.val()+'&#10;Desc:'+jm_desc.val()+'';
	                      jQuery('#item_'+id_selected+'').html('<img class=jm_img src='+jm_juri.val()+jm_url.val()+' onmousemove=processjson() title=\"'+_toolstip+'\" height=60><button class=jm_button type=button onclick=removeclick('+id_selected+') >remove</button><button id=edit_'+id_selected+' class=jm_button type=button onclick=edititem('+id_selected+') >edit</button><input id=value_'+id_selected+' type=hidden value='+_json+' /><div class=jm_clear></div>');
	   					  processjson();
	   					  runEffect('item_'+id_selected);
	   					  id_selected = '';
	                   }
	                   clearclick();
	                   jm_bt_add.val('ADD');
	                   jm_bt_clear.val('CLEAR');
    			   }
    			   function removeclick(id)
    			   {
                      jQuery('#item_'+id+'').addClass('bounceOut animatedbounceOut');
                      setTimeout(function() {
						      jQuery('#item_'+id+'').remove();
						      processjson();
					  }, 1000);
    			   }
    			   function deleteallclick()
        		   {
        		      var elem = document.getElementById('image_items');
					  json = ''; jm_value.val('');
        		      while(elem.firstChild)
        		      {
						elem.removeChild(elem.firstChild);
					  }
    			   }
    			   function processjson()
    			   {
    			      json = '';
    			      var s = ',';
	    			      jQuery('#image_items input').each(function() {
	                   	  var type = jQuery(this).attr('type');
	                   	  if (type == 'hidden'){
	                   	     	json += jQuery(this).val()+s;
	                   	     }
	                  	  });
    			      _chars = '},]';
    			      json = '['+json+']';
    			      jm_value.val(json.replace(_chars,'}]'));
                   }
    			   function edititem(id)
    			   {
    			       item_focus = jQuery('#edit_'+id+'');
    			       if (id_selected != ''){
    			       	   jQuery('#item_'+id_selected+'').css('opacity','1');
    			       }
    			       id_selected = id;
    			       var json_obj = JSON.parse(jQuery('#value_'+id+'').val());
	    			       jm_bt_add.val('SAVE');
	    			       jm_bt_clear.val('CANCEL');
		                   jm_title.val(decodeURI(json_obj.title));
		                   jm_icon.val(decodeURI(json_obj.icon));
		                   jm_desc.val(decodeURI(json_obj.desc));
		                   jm_url.val(json_obj.url);
		                   jm_link.val(decodeURI(json_obj.link));
		                   jm_link_target.val(json_obj.target);
	    			       jm_url.focus();
	    			       jQuery('#item_'+id_selected+'').css('opacity','0.4');
                   }
                   function runEffect(item)
                   {
                      jQuery('#'+item+'').css('opacity','1');
                      jQuery('#'+item+'').addClass('flipInY animated');
                      setTimeout(function() {
						      jQuery('#'+item+'').removeClass('flipInY animated');
					  }, 1000);
    			   }
    			   function addEffect(item)
                   {
                      jQuery('#'+item+'').addClass('bounceInDown animatedbounceInDown');
                      setTimeout(function() {
						      jQuery('#'+item+'').removeClass('bounceInDown animatedbounceInDown');
					  }, 1000);
    			   }
				    </script>";
        $html .="</div>";
        return $html;
    }
    private function processjson($jsons)
    {
    	$html ='';
        if(count($jsons)>0){
        foreach ($jsons as $i => $item)
        {
        	$json = json_encode($item);
        	$_toolstip = 'Title : '.urldecode($item->title).'&#10;Link : '.urldecode($item->link).'&#10;Desc : '.urldecode($item->desc);
        	$_url = JURI::root().$item->url;
        	$html .= "<div id=item_{$i} class='jm_items' ><img class='jm_img' src='{$_url}' alt='' title='{$_toolstip}' onmousemove=processjson() height=60><button class=jm_button type=button onclick=removeclick('{$i}') >remove</button><button id=edit_{$i} class=jm_button type=button onclick=edititem('{$i}') >edit</button><input id=value_{$i} type=hidden value={$json} /><div class='jm_clear'></div></div>";
        }
        }
    	return $html;
    }
}