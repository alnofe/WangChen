<?php
@ob_start();
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.categories');
require_once JPATH_SITE.'/components/com_content/helpers/route.php';
$k2route = JPATH_SITE.'/components/com_k2/helpers/route.php';
if (!class_exists('JMImage')){
	require_once JPATH_SITE . DS . 'modules' . DS . 'mod_jmnewspro' . DS . 'classes' . DS . 'jmimage.class.php';
}
if (file_exists($k2route)){
	require_once($k2route);
}
class JMNewsProSlide extends stdClass {
	var $db = null;
	var $id = null;
	var $category = null;
	var $category_name = null;
	var $image = null;
	var $title = null;
	var $icon = null;
	var $price = null;
	var $currency_symbol = null;
	var $description = null;
	var $link = null;
	var $target = null;
	var $params = null;
	var $created = null;
	var $content_type = 'text';
    var $display_menu = null;
    var $jmnewspro_display_menu_content = null;
    var $jmnewspro_display_menu_k2 = null;
    var $jmnewspro_display_menu_product = null;
	public function JMNewsProSlide($params) {
		$this->params = $params;
        $this->jmnewspro_display_menu_content = $this->params->get('jmnewspro_display_menu_content', '-1');
        $this->jmnewspro_display_menu_k2 = $this->params->get('jmnewspro_display_menu_k2', '-1');
        $this->jmnewspro_display_menu_product = $this->params->get('jmnewspro_display_menu_product', '-1');
	}
	public function loadArticle($id) {
	    $app     = JFactory::getApplication();
    	$menus   = $app->getMenu('site');
		$article = JTable::getInstance("content");
		$article->load($id);
		$lang = JFactory::getLanguage();
		$langcode = $lang->getTag();
		$this->category = $article->get('catid');
		$this->db = JFactory::getDbo();
		$this->db->setQuery('SELECT cat.title FROM #__categories cat	WHERE cat.id='.$this->category);
		$this->category_name = $this->db->loadResult();
		if (!class_exists('ContentHelperRoute')) {   
			require_once(JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php');
		}
        //require_once(JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php');
		$this->created = strtotime($article->created);
		if ($article) {
			//$this->title = $article->get('title');
			$image_source = $this->params->get('jmnewspro_article_image_source', 1);
			$imageobj = json_decode($article->images);
			//print_r($imageobj);die;
			if ($image_source == 1) {
				//Intro Image
				$this->image = isset($imageobj->image_intro)?(!empty($imageobj->image_intro)?$imageobj->image_intro:''):'';
			} elseif ($image_source == 2) {
				//Full Image
				$this->image = $imageobj->image_fulltext;
			} else {
				$this->image = $this->getFirstImage($article->introtext . $article->fulltext);
			}
			if(!empty($imageobj->image_intro) || !empty($imageobj->image_fulltext)){
				$this->content_type = 'image';
			}
			$allowable_tags = $this->params->get('jmnewspro_desc_html', '');
			$tags = "";
			if ($allowable_tags) {
				$allowable_tags = explode(',', $allowable_tags);
				foreach ($allowable_tags as $tag) {
					$tags .= "<$tag>";
				}
			}
			$maxleght = $this->params->get('jmnewspro_desc_length', 150);
			$this->description = substr(strip_tags($article->introtext . $article->fulltext, $tags), 0, $maxleght);
			if ($maxleght < strlen(strip_tags($article->introtext . $article->fulltext, $tags))) {
				$this->description = preg_replace('/ [^ ]*$/', ' ...', $this->description);
			}
			$titleleght = $this->params->get('jmnewspro_title_length', 50);
			$this->title = substr(strip_tags($article->get('title'), $tags), 0, $titleleght);
			if ($titleleght < strlen(strip_tags($article->get('title'), $tags))) {
				$this->title = preg_replace('/ [^ ]*$/', ' ...', $this->title);
			}
           
            // if menu item id was assigned
            if($this->jmnewspro_display_menu_content!='-1'){ 
                $active = $menus->getActive();
                $default = $menus->getDefault($langcode);
                $abslink=ContentHelperRoute::getArticleRoute($article->id, $article->catid, $langcode);
                
                $itemids=explode('Itemid=',$abslink);
                if($itemids[1]==$active->id || $itemids[1]==$default->id){
                    $link='index.php?option=com_content&view=article&id='.$article->id.'&catid='.$article->catid;
                    $langshorts=$lang->getLocale(); 
                    if ($langcode && $langcode != "*" && JLanguageMultilang::isEnabled())
                    {
                        $link.='&lang='.$langshorts[4];
                    }
        		   $this->link=JRoute::_($link.'&Itemid='.$this->jmnewspro_display_menu_content);
        		} 
                else{
    	           $this->link = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid, $langcode));
                    
                } 
            }else{
                $this->link = JRoute::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid, $langcode));
            }  
            
			$this->id = $id;
			$this->target = $this->params->get('jmnewspro_link_target', '_blank');
			if ($this->params->get('jmnewspro_title_link')) {
				$this->title = '<a target="'.$this->target.'" href="' . $this->link . '">' . $this->title . '</a>';
			}
		} else {
			return null;
		}
	}
	public function loadProduct($id) {
	    $app     = JFactory::getApplication();
    	$menus   = $app->getMenu('site');
        $lang = JFactory::getLanguage();
		$langcode = $lang->getTag();
		$this->db = JFactory::getDbo();
		$query = $this->db->getQuery(true)
						->select("p.*,pc.product_category_id,pc.category_id,hp.price_value,hcu.currency_symbol")
						->select("f.file_path")
						->select("hc.category_name")
						->from("#__hikashop_product AS p")
						->leftjoin("#__hikashop_file AS f ON p.product_id = f.file_ref_id")
						->leftjoin("#__hikashop_product_category AS pc ON pc.product_id = p.product_id")
						->leftjoin("#__hikashop_category AS hc ON hc.category_id = pc.category_id")
						->leftjoin("#__hikashop_price AS hp ON hp.price_product_id = p.product_id")
						->leftjoin("#__hikashop_currency AS hcu ON hcu.currency_id = hp.price_currency_id")
						->where("p.product_id = {$id}")
						->where("f.file_type = 'product' ");
		$product = $this->db->setQuery($query)->loadObject();
		if ($product) {
			$this->title = $product->product_name;
			$titleleght = $this->params->get('jmnewspro_title_length', 50);
			$this->title = substr($product->product_name, 0, $titleleght);
			if ($titleleght < strlen($product->product_name)) {
				$this->title = preg_replace('/ [^ ]*$/', ' ...', $this->title);
			}
			$this->created = $product->product_created;
			$image_source = $this->params->get('jmnewspro_image_source', 0);
			if (empty($image_source)) {
				$this->image = JPATH_SITE . '/media/com_hikashop/upload/'. $product->file_path;
			} else {
				$this->image = $this->getFirstImage($product->product_description);
			}
			$maxleght = $this->params->get('jmnewspro_desc_length', 150);
			$allowable_tags = $this->params->get('jmnewspro_desc_html', '');
			$tags = "";
			if ($allowable_tags) {
				$allowable_tags = explode(',', $allowable_tags);
				foreach ($allowable_tags as $tag) {
					$tags .= "<$tag>";
				}
			}
			$this->description = substr(strip_tags($product->product_description, $tags), 0, $maxleght);
			$this->id = $product->product_id;
			$this->category = $product->category_id;
			$this->price = $product->price_value;
			$this->currency_symbol = $product->currency_symbol;
			$this->category_name = $product->category_name;
			if ($maxleght < strlen(strip_tags($product->product_description, $tags))) {
				$this->description = preg_replace('/ [^ ]*$/', ' ...', $this->description);
			}
            $Itemid = $this->getItemidFromCategory((int)$this->category);
            // if menu item id was assigned
            if($this->jmnewspro_display_menu_product!='-1'){ 
                $active = $menus->getActive();
                $default = $menus->getDefault($langcode);
                if($Itemid==$active->id || $Itemid==$default->id){
                    $Itemid=$this->jmnewspro_display_menu_product;
                    $this->link = JRoute::_("index.php?option=com_hikashop&ctrl=product&task=show&cid={$product->product_id}&name={$product->product_name}&Itemid={$Itemid}");
                } 
                else{
    	           $this->link = JRoute::_("index.php?option=com_hikashop&ctrl=product&task=show&cid={$product->product_id}&name={$product->product_name}&Itemid={$Itemid}");
                } 
            }else{
                $this->link = JRoute::_("index.php?option=com_hikashop&ctrl=product&task=show&cid={$product->product_id}&name={$product->product_name}&Itemid={$Itemid}");
            }  
             
			$this->target = $this->params->get('jmnewspro_link_target', '_blank');
			if ($this->params->get('jmnewspro_title_link')) {
				$this->title = '<a target="'.$this->target.'" href="' . $this->link . '">' . $this->title . '</a>';
			}
		} else {
			return null;
		}
	}

	function getItemidFromCategory($category_id) {
	    $app = JFactory::getApplication();
        $menus   = $app->getMenu('site');
        $lang = JFactory::getLanguage();
		$langcode = $lang->getTag();
		$result = "";
		$itemid = $this->params->get('jmnewspro_follow','');
		if($itemid){
			$result = $itemid;
		}
		$db = JFactory::getDbo();
		if(!$result){
			$query = "SELECT * FROM `#__hikashop_config` WHERE `config_namekey` like 'menu_%'";
			$db->setQuery($query);
			$values = $db->loadObjectList();
			foreach ($values as $key => $value) {
				if($value->config_value){
					$options = unserialize(base64_decode($value->config_value));
					if ((int)$options['selectparentlisting'] == (int)$category_id) {
						$result = str_replace('menu_','',$value->config_namekey);
					}
				}
			}
		}
        
		if($result){
			$query = "SELECT * FROM `#__menu` WHERE `id`=".$result." AND published=1";
            $db->setQuery($query);
            $mobject=$db->loadObject();
            if(!$mobject){
               $default = $menus->getDefault($langcode);
               $active = $menus->getActive();
               if($active && ($active->home == 1 || $active->home == 0)){   
				    $result = $active->id;
               } 
            } 
		}else{
			$homepage = $app->getMenu()->getDefault();
			$menu = $app->getMenu()->getActive();
			if($menu && $menu->home == 0){
				$result = $menu->id;
			}
		}
		return $result;
	}
	/* Custom Source */
	public function loadFileImages($image) {
		$titleleght = $this->params->get('jmnewspro_title_length', 50);
		$maxleght = $this->params->get('jmnewspro_desc_length', 150);
		$this->image = $image->url;
		$this->link = $image->link;
		$this->icon = urldecode($image->icon);
		$this->target = $image->target;
		if ($this->target == "none"){
			$this->target = $this->params->get('jmnewspro_link_target', '_blank');
		}
		$this->title = substr(urldecode($image->title), 0, $titleleght);
		if ($this->params->get('jmnewspro_title_link')) {
			$this->title = '<a target="'.$this->target.'" href="' . $this->link . '">' . $this->title . '</a>';
		}
		$this->description = substr(urldecode($image->desc),0,$maxleght);
	}
	public function loadK2($id) {
		$this->db = JFactory::getDbo();
        require_once JPATH_SITE . DS . 'components' . DS . 'com_k2' . DS . 'helpers' . DS . 'route.php';
		$query = $this->db->getQuery(true)
						->select("k2.*,c.name as categoryname,c.id as categoryid, c.alias as categoryalias, c.params as categoryparams")
						->from("#__k2_items AS k2")
						->innerJoin("#__k2_categories c ON c.id = k2.catid")
						->where("k2.id = {$id}");
		$k2 = $this->db->setQuery($query)->loadObject();
		if ($k2) {
			$this->created = strtotime($k2->created);
			$this->title = $k2->title;
			$titleleght = $this->params->get('jmnewspro_title_length', 50);
			$this->title = substr($k2->title, 0, $titleleght);
			if ($titleleght < strlen($k2->title)) {
				$this->title = preg_replace('/ [^ ]*$/', ' ...', $this->title);
			}
			$image_source = $this->params->get('jmnewspro_image_source', 0);
			if (empty($image_source)) {
				//$size = XS, S, M, L, XL
				$size = $this->params->get('jmnewspro_k2_image_size', 'L');
				$this->image = JPATH_SITE . '/media/k2/items/cache/' . md5("Image" . $k2->id) . '_'.$size.'.jpg';
				$this->content_type = 'image';
			} else {
				$this->image = $this->getFirstImage($k2->introtext . $k2->fulltext);
			}
			if(!empty($k2->video)){
				$this->content_type = 'video';
			}
			$maxleght = $this->params->get('jmnewspro_desc_length', 150);
			$allowable_tags = $this->params->get('jmnewspro_desc_html', '');
			$tags = "";
			if ($allowable_tags) {
				$allowable_tags = explode(',', $allowable_tags);
				foreach ($allowable_tags as $tag) {
					$tags .= "<$tag>";
				}
			}
			$this->description = substr(strip_tags($k2->introtext . $k2->fulltext, $tags), 0, $maxleght);
			$this->id = $k2->id;
			$this->category = $k2->catid;
			$this->db = JFactory::getDbo();
			$this->db->setQuery('SELECT cat.name FROM #__k2_categories cat	WHERE cat.id='.$this->category);
			$this->category_name = $this->db->loadResult();
			if ($maxleght < strlen(strip_tags($k2->introtext . $k2->fulltext, $tags))) {
				$this->description = preg_replace('/ [^ ]*$/', ' ...', $this->description);
			}
            //check existan of menu item id
            $needles = array(
    			'item' => (int)$k2->id,
    			'category' => (int)$k2->catid,
    		);
            // if menu item id was assigned
            if($this->jmnewspro_display_menu_k2!='-1'){
        	    $item = K2HelperRoute::_findItem($needles);
        		if(!$item){
        		   $this->link=JRoute::_('index.php?option=com_k2&view=item&id='.$k2->id.':'.urlencode($k2->alias).'&Itemid='.$this->jmnewspro_display_menu_k2);
        		} 
                else{
    	           $this->link = $this->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($k2->id.':'.urlencode($k2->alias),$k2->catid.':'.urlencode($k2->categoryalias))));
                } 
            }else{
                $this->link = $this->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($k2->id.':'.urlencode($k2->alias),$k2->catid.':'.urlencode($k2->categoryalias))));
            }
			$this->target = $this->params->get('jmnewspro_link_target', '_blank');
			if ($this->params->get('jmnewspro_title_link')) {
				$this->title = '<a target="'.$this->target.'" href="' . $this->link . '">' . $this->title . '</a>';
			}
		} else {
			return null;
		}
	}
	function getFirstImage($str) {
		$str = strip_tags($str, '<img>');
		$matches = null;
		preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $str, $matches);
		if (isset($matches[1][0])) {
			return $image = $matches[1][0];
		}
		return '';
	}
	function getMainImage() {
		if (empty($this->image)) {
			$this->image = JPATH_SITE . '/modules/mod_jmnewspro/images/no-image.jpg';
		} elseif (str_replace(array('http://', 'https://'), '', $this->image) != $this->image) {
			$imageArray = @getimagesize($this->image);
			if (!$imageArray[0]) {
				$this->image = JPATH_SITE . '/modules/mod_jmnewspro/images/no-image.jpg';
			}
		} elseif (!file_exists($this->image)) {
			$this->image = JPATH_SITE . '/modules/mod_jmnewspro/images/no-image.jpg';
		}
		$style = $this->params->get('jmnewspro_image_style', 'fill');
		if($style=='original') return $this->image;
		$width = $this->params->get('jmnewspro_item_width');
		$height = $this->params->get('jmnewspro_item_height');
		$file = pathinfo($this->image);
		$basename = $width . 'x' . $height . '_' . $style . '_' . $file['basename'];
		$safe_name = str_replace(array(' ', '(', ')', '[', ']'), '_', $basename);
		$newfile = JM_NEWS_PRO_IMAGE_FOLDER . '/' . $safe_name;
		$flush = isset($_GET['flush']) ? true : false;
		if (!is_file($newfile) || filemtime($this->image) > filemtime($newfile)) {
			@unlink($newfile);
			$jmimage = new JMImage($this->image);
			switch ($style) {
				case 'fill':
					$jmimage->reFill($width, $height);
					break;
				case 'fit':
					$jmimage->scale($width, $height);
					$jmimage->enlargeCanvas($width, $height, array(0, 0, 0));
					break;
				case 'stretch':
					$jmimage->resample($width, $height, false);
					break;
			}
			$jmimage->save($newfile);
		}
		return JM_NEWS_PRO_IMAGE_PATH . '/' . $safe_name;
	}
	function getThumbnail() {
		$width = $this->params->get('jmnewspro_image_thumbnail_width', 200);
		$height = $this->params->get('jmnewspro_image_thumbnail_height', 100);
		$file = pathinfo($this->image);
		$basename = $width . 'x' . $height . '_' . $file['basename'];
		$safe_name = str_replace(array(' ', '(', ')', '[', ']'), '_', $basename);
		$newfile = JM_NEWS_PRO_IMAGE_FOLDER . '/' . $safe_name;
		if (!file_exists($newfile)) {
			$jmimage = new JMImage($this->image);
			$jmimage->resample($width, $height);
			$jmimage->enlargeCanvas($width, $height, array(255, 255, 255));
			$jmimage->save($newfile);
		}
		return JM_NEWS_PRO_IMAGE_PATH . '/' . $safe_name;
	}
}
@ob_end_clean();