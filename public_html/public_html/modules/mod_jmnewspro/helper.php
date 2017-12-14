<?php
/*
#------------------------------------------------------------------------
# Package - JoomlaMan JMNewsPro
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
class ModJmNewsProHelper {
  /**
   * Do something getItems method
   *
   * @param
   * @return
   */
  static function getSlides($params) {
    $slidesource = $params->get('slider_source', 1);
    switch ($slidesource) {
      case 1:
        return ModJmNewsProHelper::getSlidesFromCategories($params);
        break;
      case 2:
        return ModJmNewsProHelper::getSlidesFromArticleIDs($params);
        break;
      case 3:
        return ModJmNewsProHelper::getSlidesFromK2Categories($params);
        break;
      case 4:
        return ModJmNewsProHelper::getSlidesFromK2IDs($params);
        break;
      case 5:
        return ModJmNewsProHelper::getSlidesFromCategoriesProduct($params);
        break;
      case 6:
        return ModJmNewsProHelper::getSlidesFromProductIDs($params);
        break;
      case 7:
        return ModJmNewsProHelper::getSlidesFeatured($params);
        break;
      case 8:
        return ModJmNewsProHelper::getSlidesK2Featured($params);
        break;
      case 10:
        return ModJmNewsProHelper::getSlidesFromFile($params);
        break;
    }
  }
  static function getSlidesFromFile($params){
  	$slides = array();
  	$images = json_decode($params->get('jmnewspro_file_image'));

  	$limit = $params->get('jmnewspro_count', 0);
  	foreach ($images as $i=>$image) {
  		if ($limit > 0 && $i <= ($limit-1)) {
  			$slide = new JMNewsProSlide($params);
  			$slide->loadFileImages($image);
  			$slides[] = $slide;
  		} elseif($limit <= 0 ) {
  			$slide = new JMNewsProSlide($params);
  			$slide->loadFileImages($image);
  			$slides[] = $slide;
  		}
  	}

  	return $slides;
  }
  static function getSlidesFromCategories($params) {
    $limit = $params->get('jmnewspro_count', 0);
    $categories = $params->get('jmnewspro_categories', array());
    $categories = implode(',', $categories);
    if($categories){
	    $db = JFactory::getDbo();
	    $ordering = $params->get('jmnewspro_ordering', 'ASC');
	    $orderby = $params->get('jmnewspro_orderby', 1);
	    if ($orderby == 1) {
	      $field = 'c.title';
	    } elseif ($orderby == 2) {
	      $field = 'c.ordering';
	    } else {
	      $field = 'c.id';
	    }
	    $query = $db->getQuery(true);
	            $query->select("c.id");
	            $query->from("#__content AS c");
	            $query->where("c.catid IN({$categories})");
	            $query->where("c.state > 0");
	            $query->where ("c.publish_up <= NOW()");
	            //$query->where ("c.publish_down >= NOW()");
	            if($ordering != 'RAND()'){
	            	$query->order($field . ' ' . $ordering);
	            } else {
	            	$query->order($ordering);
	            }
	    if ($limit > 0) {
	      $db->setQuery($query, 0, $limit);
	    } else {
	      $db->setQuery($query);
	    }
	    $rows = $db->loadObjectList();
    }
    $slides = array();
    if (empty($rows)) {
      return $slides;
    }
    foreach ($rows as $row) {
      $slide = new JMNewsProSlide($params);
      $slide->loadArticle($row->id);
      $slides[] = $slide;
    }
    return $slides;
  }
  static function getSlidesFromArticleIDs($params) {
    $ids = $params->get('jmnewspro_article_ids', '');
    $ids = str_replace(' ', '', $ids);
    $db = JFactory::getDbo();
    $ordering = $params->get('jmnewspro_ordering', 'ASC');
    $orderby = $params->get('jmnewspro_orderby', 1);
    if ($orderby == 1) {
      $field = 'c.title';
    } elseif ($orderby == 2) {
      $field = 'c.ordering';
    } else {
      $field = 'c.id';
    }
    $query = $db->getQuery(true);
            $query->select("c.id");
            $query->from("#__content AS c");
            $query->where("c.state > 0");
            $query->where("c.id IN ({$ids})");
            $query->where ("c.publish_up <= NOW()");
            if($ordering != 'RAND()'){
            	$query->order($field . ' ' . $ordering);
            } else {
            	$query->order($ordering);
            }
    $db->setQuery($query);
    $rows = $db->loadObjectList();
    if (empty($rows)) {
      return $slides;
    }
    foreach ($rows as $row) {
      $slide = new JMNewsProSlide($params);
      $slide->loadArticle($row->id);
      $slides[] = $slide;
    }
    return $slides;
  }
  static function getSlidesFeatured($params) {
    $limit = $params->get('jmnewspro_count', 0);
    $db = JFactory::getDbo();
    $ordering = $params->get('jmnewspro_ordering', 'ASC');
    $orderby = $params->get('jmnewspro_orderby', 1);
    if ($orderby == 1) {
      $field = 'c.title';
    } elseif ($orderby == 2) {
      $field = 'c.ordering';
    } else {
      $field = 'c.id';
    }
    $query = $db->getQuery(true);
            $query->select("c.id");
            $query->from("#__content AS c");
            $query->where("c.state > 0");
            $query->where("c.featured = 1");
            $query->where ("c.publish_up <= NOW()");
  			if($ordering != 'RAND()'){
            	$query->order($field . ' ' . $ordering);
            } else {
            	$query->order($ordering);
            }
    if ($limit > 0) {
      $db->setQuery($query, 0, $limit);
    } else {
      $db->setQuery($query);
    }
    $rows = $db->loadObjectList();
    $slides = array();
    if (empty($rows)) {
      return $slides;
    }
    foreach ($rows as $row) {
      $slide = new JMNewsProSlide($params);
      $slide->loadArticle($row->id);
      $slides[] = $slide;
    }
    return $slides;
  }
  ///Hikashop
  static function getSlidesFromCategoriesProduct($params) {
    $limit = $params->get('jmnewspro_count', 0);
    $categories = $params->get('jmnewspro_hikashop_categories', array());
    $categories = implode(',', $categories);
    $orderfield=$params->get('jmnewspro_hikashop_orderby', 'product_id');
    $ordering=$params->get('jmnewspro_ordering', 'ASC');
    if($categories){
	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);
	            $query->select("DISTINCT p.product_id");
	            $query->from("#__hikashop_product AS p");
	            $query->leftjoin("#__hikashop_product_category AS c ON p.product_id = c.product_id");
	            $query->where("c.category_id IN({$categories})");
	            $query->where("p.product_published > 0");
	    if($ordering != 'RAND()'){
	    	$query->order('p.'.$orderfield.' '.$ordering);
	   	} else {
	    	$query->order($ordering);
	    }
	    if ($limit > 0) {
	      $db->setQuery($query, 0, $limit);
	    } else {
	      $db->setQuery($query);
	    }
	    $rows = $db->loadObjectList();
    }
    $slides = array();
    if (empty($rows)) {
      return $slides;
    }
    foreach ($rows as $row) {
      $slide = new JMNewsProSlide($params);
      $slide->loadProduct($row->product_id);
      $slides[] = $slide;
    }

    return $slides;
  }
  static function getSlidesFromProductIDs($params) {
    $ids = $params->get('jmnewspro_hikashop_ids', '');
    $ids = str_replace(' ', '', $ids);
    $db = JFactory::getDbo();
    $orderfield=$params->get('jmnewspro_hikashop_orderby', 'product_id');
    $ordering=$params->get('jmnewspro_ordering', 'ASC');
    if (empty($ids))
      return $slides;
    $query = $db->getQuery(true);
            $query->select("p.product_id");
            $query->from("#__hikashop_product AS p");
            $query->where("p.product_published > 0");
            $query->where("p.product_id IN ({$ids})");
  		if($ordering != 'RAND()'){
	    	$query->order('p.'.$orderfield.' '.$ordering);
	   	} else {
	    	$query->order($ordering);
	    }
    $db->setQuery($query);
    $rows = $db->loadObjectList();
    $slides = array();
    if (empty($rows)) {
      return $slides;
    }
    foreach ($rows as $row) {
      $slide = new JMNewsProSlide($params);
      $slide->loadProduct($row->product_id);
      $slides[] = $slide;
    }
    return $slides;
  }
  static function getSlidesFromK2Categories($params) {
    $limit = $params->get('jmnewspro_count', 0);
    $categories = $params->get('jmnewspro_k2_categories', array());
    $categories = implode(',', $categories);
    $ordering = $params->get('jmnewspro_ordering', 'ASC');
    $orderby = $params->get('jmnewspro_orderby', 1);
    $orderfields = array('','k2.title','k2.ordering','k2.created');
    if($categories){
	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);
	            $query->select("k2.id");
	            $query->from("#__k2_items AS k2");
	            $query->where("k2.catid IN({$categories})");
	            $query->where("k2.published = 1");
	            $query->where("k2.trash = 0");
	            $query->where("k2.publish_up <= NOW()");
	            if($ordering != 'RAND()'){
	            	$query->order($orderfields[$orderby] . ' ' . $ordering);
	            } else {
	            	$query->order($ordering);
	            }

	    if ($limit > 0) {
	      $db->setQuery($query, 0, $limit);
	    } else {
	      $db->setQuery($query);
	    }
	    $rows = $db->loadObjectList();
    }
    $slides = array();
    if (empty($rows)) {
      return $slides;
    }
    foreach ($rows as $row) {
      $slide = new JMNewsProSlide($params);
      $slide->loadK2($row->id);
      $slides[] = $slide;
    }
    return $slides;
  }
  static function getSlidesFromK2IDs($params) {
		$slides = array();
    $ids = $params->get('jmnewspro_k2_ids', '');
    $ids = str_replace(' ', '', $ids);
    $db = JFactory::getDbo();
    $ordering = $params->get('jmnewspro_ordering', 'ASC');
    $orderby = $params->get('jmnewspro_orderby', 1);
    $orderfields = array('','k2.title','k2.ordering','k2.created');
    $query = $db->getQuery(true);
            $query->select("k2.id");
            $query->from("#__k2_items AS k2");
            $query->where("k2.id IN ({$ids})");
            $query->where("k2.published = 1");
			$query->where("k2.trash = 0");
			$query->where("k2.publish_up <= NOW()");
  			if($ordering != 'RAND()'){
	           	$query->order($orderfields[$orderby] . ' ' . $ordering);
	        } else {
	           	$query->order($ordering);
	        }
    $db->setQuery($query);
    $rows = $db->loadObjectList();
    if (empty($rows)) {
      return $slides;
    }
    foreach ($rows as $row) {
      $slide = new JMNewsProSlide($params);
      $slide->loadK2($row->id);
      $slides[] = $slide;
    }
    return $slides;
  }
  static function getSlidesK2Featured($params) {
    $limit = $params->get('jmnewspro_count', 0);
    $ordering = $params->get('jmnewspro_ordering', 'ASC');
    $orderby = $params->get('jmnewspro_orderby', 1);
    $orderfields = array('','k2.title','k2.ordering','k2.created');
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
            $query->select("k2.id");
            $query->from("#__k2_items AS k2");
            $query->where("k2.featured = 1");
            $query->where("k2.published = 1");
			$query->where("k2.trash = 0");
			$query->where("k2.publish_up <= NOW()");
  			if($ordering != 'RAND()'){
	            $query->order($orderfields[$orderby] . ' ' . $ordering);
	        } else {
	            $query->order($ordering);
	        }
    if ($limit > 0) {
      $db->setQuery($query, 0, $limit);
    } else {
      $db->setQuery($query);
    }
    $rows = $db->loadObjectList();
    $slides = array();
    if (empty($rows)) {
      return $slides;
    }
    foreach ($rows as $row) {
      $slide = new JMNewsProSlide($params);
      $slide->loadK2($row->id);
      $slides[] = $slide;
    }
    return $slides;
  }
  static function getTemplate() {
	$app = JFactory::getApplication();
	return $app->getTemplate();
  }

  static function getCategory($slider_source,$params) {
		if($slider_source==1){
			$categories = $params->get('jmnewspro_categories');
			$field = "id,title AS title";
			$field1 = "id";
			$table = "#__categories";
		}
		elseif($slider_source==3){
			$categories = $params->get('jmnewspro_k2_categories');
			$field = "id,name AS title";
			$field1 = "id";
			$table = "#__k2_categories";
		}
		else{
			$categories = $params->get('jmnewspro_hikashop_categories');
			$field = "category_id,category_name AS title";
			$field1 = "category_id";
			$table = "#__hikashop_category";
		}
		if($categories){
			$cat = implode(',',$categories);
		}
		$db = JFactory::getDbo();
		$query = "SELECT {$field} FROM {$table} WHERE {$field1} IN({$cat})";
		$db->setQuery($query);
		return $db->loadObjectList();
  }
}
// END ModjmnewsproHelper
?>