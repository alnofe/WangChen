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
//-- No direct access
defined('_JEXEC') or die('Restricted access');
class JFormFieldDisplaymenu extends JFormField {
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'Displaymenu';
    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput() {
        // Initialize variables.
        $html = array();
        $attr = '';
                
        // Initialize some field attributes.
        $attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
        // To avoid user's confusion, readonly="true" should imply disabled="true".
        if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
            $attr .= ' disabled="disabled"';
        }
        $attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
        $attr .= $this->multiple ? ' multiple="multiple"' : '';
        // Initialize JavaScript field attributes.
        $attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
        // Get the field options.
        $options = (array) $this->getOptions();
        // Create a read-only list (no name) with a hidden input to store the value.
        if ((string) $this->element['readonly'] == 'true') {
            $html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
            $html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
        }
        // Create a regular list.
        else {
            $html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
        }
        return implode($html);
    }
    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   11.1
     */
     public static function treerecurse( $id, $indent, $list, &$children, $maxlevel=9999, $level=0, $type=1 )
     {   
             if (@$children[$id] && $level <= $maxlevel)
             { 
                     foreach ($children[$id] as $v)
                     { 
                             $id = $v->id;

                             if ( $type ) {
                                     $pre    = '<sup>|_</sup>&nbsp;';
                                     $spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                             } else {
                                     $pre    = '- ';
                                     $spacer = '&nbsp;&nbsp;';
                             }

                             if ( $v->parent_id == 0 ) {
                                     $txt    = $v->title;
                             } else {
                                     $txt    = $pre . $v->title;
                             }
                             $pt = $v->parent_id;
                             $list[$id] = $v;
                            $list[$id]->treename = "$indent$txt";
                            $list[$id]->children = count( @$children[$id] );
                             $list = JFormFieldDisplaymenu::treerecurse( $id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type );
                    }
             }
             return $list;
    }
    protected function getOptions() {
        // Initialize variables.
        $selecttype = $this->element['selecttype'];        
        $options = array();
        $db = JFactory::getDbo();
        $where=''; 
        if($selecttype=='content'){
            $where.="AND m.link LIKE '%option=com_content%'";
        }else if($selecttype=='k2'){
            $where.="AND m.link LIKE '%option=com_k2%'";
        }else if($selecttype=='product'){
            $where.="AND m.link LIKE '%option=com_hikashop%'";
        }                
        $query = 'SELECT m.id'
                 . ' FROM #__menu AS m'
                 . ' WHERE m.published = 1 '.$where.' AND id <>1 AND published=1'
                 . ' ORDER BY m.menutype, m.parent_id'
                 ;
        $db->setQuery( $query );
        $citems = $db->loadObjectList();
        $chitems=array();
        foreach($citems as $k=>$v){
            $chitems[$k]=$v->id;
        }
              
        $query = 'SELECT m.id, m.parent_id, m.title, m.menutype'
                 . ' FROM #__menu AS m'
                 . ' WHERE m.published = 1 AND id <>1'
                 . ' ORDER BY m.menutype, m.parent_id'
                 ;
         
                 $db->setQuery( $query );
                 $mitems = $db->loadObjectList();
                 $mitems_temp = $mitems;

                 // establish the hierarchy of the menu
                 $children = array();
                 // first pass - collect children
                 foreach ( $mitems as $v )
                 {
                         $id = $v->id;
                         $pt = $v->parent_id;
                         $list = @$children[$pt] ? $children[$pt] : array();
                         array_push( $list, $v );
                         $children[$pt] = $list;
                 }
               
        $list = JFormFieldDisplaymenu::treerecurse( intval( $mitems[0]->parent_id ), '', array(), $children, 9999, 0, 0 );
         
         // Code that adds menu name to Display of Page(s)
                 $mitems_spacer  = $mitems_temp[0]->menutype;
 
                 $mitems = array();
                 $mitems[] = JHTML::_('select.option',  -1, JText::_( 'Unassigned' ) );   
                 $lastMenuType   = null;
                 $tmpMenuType    = null;
                 foreach ($list as $list_a)
                 {
                         if ($list_a->menutype != $lastMenuType)
                         {
                                 if ($tmpMenuType) {
                                         $mitems[] = JHTML::_('select.option',  '</OPTGROUP>' );
                                 }
                                 $mitems[] = JHTML::_('select.option',  '<OPTGROUP>', $list_a->menutype );
                                 $lastMenuType = $list_a->menutype;
                                 $tmpMenuType  = $list_a->menutype;
                         }
                        if(in_array($list_a->id,$chitems))
                         $mitems[] = JHTML::_('select.option',  $list_a->id, $list_a->treename );
                 }
                 if ($lastMenuType !== null) {
                         $mitems[] = JHTML::_('select.option',  '</OPTGROUP>' );
                 }
 
        reset($mitems);
        return $mitems;
    }
}