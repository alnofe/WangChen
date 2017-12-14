<?php
// no direct access
defined('_JEXEC') or die;
$COM_CONTENT_PUBLISHED_DATE_ON = version_compare(JVERSION, '1.7', '<') ? 'COM_CONTENT_PUBLISHED_DATE' : 'COM_CONTENT_PUBLISHED_DATE_ON';
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');
$params		= $this->item->params;
$images = isset($this->item->images) ? json_decode($this->item->images) : new stdClass;
$urls = isset($this->item->urls) ? json_decode($this->item->urls) : null;
if(!isset($this->item->pagination))
	$this->item->pagination = null;
$canEdit	= $this->item->params->get('access-edit');
$user		= JFactory::getUser();
?>
<div class="item-page<?php echo $this->pageclass_sfx?>">
<?php
if(!empty($this->item->pagination) AND $this->item->pagination && !$this->item->paginationposition && $this->item->paginationrelative)
{
	echo $this->item->pagination;
}
?>
<?php if ($params->get('show_title')) : ?>
<h2>
<?php if ($params->get('link_titles') && !empty($this->item->readmore_link)) : ?>
<a href="<?php echo $this->item->readmore_link; ?>"><?php echo $this->escape($this->item->title); ?></a>
<?php else : ?>
<?php 	echo $this->escape($this->item->title); ?>
<?php endif; ?>
</h2>
<?php endif; ?>
<?php  if (!$params->get('show_intro')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>
<?php echo $this->item->event->beforeDisplayContent; ?>
<?php $useDefList = (($params->get('show_author')) OR ($params->get('show_category')) OR ($params->get('show_parent_category'))
	OR ($params->get('show_create_date')) OR ($params->get('show_modify_date')) OR ($params->get('show_publish_date'))
	OR ($params->get('show_hits'))); ?>
<?php if ($useDefList) : ?>
<div class="article-info">
<?php endif; ?>
<?php if ($params->get('show_parent_category') && $this->item->parent_slug != '1:root') : ?>
<div class="parent-category-name">
<?php $title = $this->escape($this->item->parent_title);
	  $url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->parent_slug)).'">'.$title.'</a>';?>
<?php if ($params->get('link_parent_category') AND $this->item->parent_slug) : ?>
<?php 	echo JText::sprintf('COM_CONTENT_PARENT', $url); ?>
<?php else : ?>
<?php 	echo JText::sprintf('COM_CONTENT_PARENT', $title); ?>
<?php endif; ?>
</div>
<?php endif; ?>
<?php if ($params->get('show_category')) : ?>
<div class="category-name">
<?php $title = $this->escape($this->item->category_title);
	  $url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug)).'">'.$title.'</a>';?>
<?php if ($params->get('link_category') AND $this->item->catslug) : ?>
<?php 	echo JText::sprintf('COM_CONTENT_CATEGORY', $url); ?>
<?php else : ?>
<?php 	echo JText::sprintf('COM_CONTENT_CATEGORY', $title); ?>
<?php endif; ?>
</div>
<?php endif; ?>
<?php if ($params->get('show_create_date')) : ?>
<div class="create"><?php echo JText::sprintf('COM_CONTENT_CREATED_DATE_ON', JHtml::_('date',$this->item->created, JText::_('DATE_FORMAT_LC2'))); ?></div>
<?php endif; ?>
<?php if ($params->get('show_modify_date')) : ?>
<div class="modified"><?php echo JText::sprintf('COM_CONTENT_LAST_UPDATED', JHtml::_('date',$this->item->modified, JText::_('DATE_FORMAT_LC2'))); ?></div>
<?php endif; ?>
<?php if ($params->get('show_publish_date')) : ?>
<div class="published"><?php echo JText::sprintf($COM_CONTENT_PUBLISHED_DATE_ON, JHtml::_('date',$this->item->publish_up, JText::_('DATE_FORMAT_LC2'))); ?></div>
<?php endif; ?>
<?php if ($params->get('show_author') && !empty($this->item->author )) : ?>
<div class="createdby"> 
<?php $author = $this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author; ?>
<?php if (!empty($this->item->contactid) && $params->get('link_author') == true): ?>
<?php
	$needle = 'index.php?option=com_contact&view=contact&id=' . $this->item->contactid;
	$menu = JFactory::getApplication()->getMenu();
	$item = $menu->getItems('link', $needle, true);
	$cntlink = !empty($item) ? $needle . '&Itemid=' . $item->id : $needle;
?>
<?php 	echo JText::sprintf('COM_CONTENT_WRITTEN_BY', JHtml::_('link', JRoute::_($cntlink), $author)); ?>
<?php else: ?>
<?php 	echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
<?php endif; ?>
</div>
<?php endif; ?>	
<?php if ($params->get('show_hits')) : ?>
<div class="hits"><?php echo JText::sprintf('COM_CONTENT_ARTICLE_HITS', $this->item->hits); ?></div>
<?php endif; ?>
<?php if ($useDefList) : ?>
</div>
<?php endif; ?>
<?php if (isset ($this->item->toc)) : ?>
<?php 	echo $this->item->toc; ?>
<?php endif; ?>
<?php if (isset($urls) AND ((!empty($urls->urls_position) AND ($urls->urls_position=='0')) OR  ($params->get('urls_position')=='0' AND empty($urls->urls_position) )) OR (empty($urls->urls_position) AND (!$params->get('urls_position')))): ?>
<?php 	echo $this->loadTemplate('links'); ?>
<?php endif; ?>
<?php if ($params->get('access-view')):?>
<?php if (isset($images->image_fulltext) and !empty($images->image_fulltext)) : ?>
<?php 	$imgfloat = (empty($images->float_fulltext)) ? $params->get('float_fulltext') : $images->float_fulltext; ?>
<div class="img-fulltext-<?php echo htmlspecialchars($imgfloat); ?>">
<img <?php if ($images->image_fulltext_caption):
		echo 'class="caption"'.' title="' .htmlspecialchars($images->image_fulltext_caption) .'"';
	endif; ?> src="<?php echo htmlspecialchars($images->image_fulltext); ?>" alt="<?php echo htmlspecialchars($images->image_fulltext_alt); ?>"/>
</div>
<?php endif; ?>
<?php
if (!empty($this->item->pagination) AND $this->item->pagination AND !$this->item->paginationposition AND !$this->item->paginationrelative):
	echo $this->item->pagination;
endif;

echo $this->item->text;

if (!empty($this->item->pagination) AND $this->item->pagination AND $this->item->paginationposition AND!$this->item->paginationrelative):
	echo $this->item->pagination;
endif;

if (isset($urls) AND ((!empty($urls->urls_position) AND ($urls->urls_position=='1')) OR ( $params->get('urls_position')=='1') )):
 	echo $this->loadTemplate('links');
endif; ?>
<?php 	//optional teaser intro text for guests ?>
<?php elseif ($params->get('show_noauth') == true AND  $user->get('guest') ) : ?>
<?php 	echo $this->item->introtext; ?>
<?php 	//Optional link to let them register to see the whole article. ?>
<?php 	if ($params->get('show_readmore') && $this->item->fulltext != null) :
			$link1 = JRoute::_('index.php?option=com_users&view=login');
			$link = new JURI($link1);?>
<p class="readmore">
<a href="<?php echo $link; ?>">
<?php 		$attribs = json_decode($this->item->attribs);  ?> 
<?php 
			if ($attribs->alternative_readmore == null) :
				echo rtrim(JText::_('COM_CONTENT_REGISTER_TO_READ_MORE'), ' .');
			elseif ($readmore = $this->item->alternative_readmore) :
				echo $readmore;
			if ($params->get('show_readmore_title', 0) != 0) :
				echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
			endif;
		else:
			echo rtrim(JText::sprintf('COM_CONTENT_READ_MORE_TITLE'), ' .');
		endif; ?></a>
</p>
<?php 	endif; ?>
<?php endif; ?>
<?php
if(!empty($this->item->pagination) AND $this->item->pagination AND $this->item->paginationposition AND $this->item->paginationrelative):
	 echo $this->item->pagination;
endif; ?>
<?php echo $this->item->event->afterDisplayContent; ?>
</div>