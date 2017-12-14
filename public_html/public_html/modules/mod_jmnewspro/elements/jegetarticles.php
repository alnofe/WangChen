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
jimport('joomla.html.html');
jimport('joomla.form.formfield');
class JFormFieldJEgetArticles extends JFormField {
	protected $type = 'JEgetArticles'; //the form field type
    var $options = array();
    
    protected function getInput() {
        JHtml::_('behavior.modal', 'a.modal');
        $db     = JFactory::getDbo();
        $sql    = "Select c.*,d.title as cat_title from #__content as c inner join #__categories as d on d.id=c.catid where c.state>0 and c.publish_up<='".date('Y-mm-dd H:i:s')."' and (c.publish_down>='".date('Y-mm-dd H:i:s')."' or c.publish_down like '0000-00-00 00:00:00')";
        $db->setQuery($sql);
        $items  = $db->loadObjectList(); 
        //var_dump($items);exit;
        
        
		$doc		= JFactory::getDocument();
        $id = $this->__get('fieldname').'_contentid';
        $doc->addStyleDeclaration("
            #$id {display:none;}
            .control-right,.control-left {width:50%;}
            .control-right {float:right;text-align:right;}
            .control-left {float:left;}
            .rowclear {width:100%;clear:both;float:left;}
        ");
        
        $doc->addScript("http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js");
        $doc->addScriptDeclaration("
            jQuery.noConflict();          
            jQuery(document).ready(function() {
                   
            });
            function article_filter(text) {
                
            }
        ");
        
        $html = '<div id="'.$this->__get('fieldname').'_contentid">';
        $html .= '<div class="rowclear">';
        $html .= '<div class="control-left"><input type="text" class="article-filter" onkeyup="article_filter(this.value);" /></div>';
        $html .= '<div class="control-right"><a href="#">Save</a></div>';
        $html .= '</div>';
		$html .= '<div class="rowclear"><table class="article-list">';
        $html .= '<thead><tr>
            <th><input type="checkbox" name="checkAll"/></th>
            <th>'.JText::_('ID_FIELD_LABEL').'</th>
            <th>'.JText::_('TITLE_FIELD_LABEL').'</th>
            <th>'.JText::_('CREAT_DATE_FIELD_LABEL').'</th>
            <th>'.JText::_('CAT_TITLE_FIELD_LABEL').'</th>
        </tr></thead>';
        $html .= '<tbody id="fbody">';
        foreach($items as $item) {
            $html.='<tr class="article-'.$item->id.'">';
            $html.='<td><input type="checkbox" name="cid[]" value="'.$item->id.'" /></td>';
            $html.="<td><span>$item->id</span></td>";
            $html.="<td><span>$item->title</span></td>";
            $html.="<td><span>$item->created</span></td>";
            $html.="<td><span>$item->cat_title</span></td>";
            $html.='</tr>';
        }
        $html .= '</tbody>';
        $html .= "</table></div>";
        $html .= "</div>";
        $html .= '<input type="text" readonly="readonly" class="'.$this->__get('class').' disable" name="'.$this->__get('name').'" />';
        $html .= '<a class="modal" rel="{handler: \'clone\', size: {x: 800, y: 400}}" href="#'.$this->__get('fieldname').'_contentid">Choose articles</a>';
		return $html;
	}
    
}