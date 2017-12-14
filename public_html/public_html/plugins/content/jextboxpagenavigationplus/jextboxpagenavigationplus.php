<?php

/**
 * @package    "JExtBOX Page Navigation Plus" plugin for Joomla!
 * @copyright  Copyright (c) 2013-2015 Galaa
 * @author     Galaa
 * @link       http://jextbox.com
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

class PlgContentJextboxPageNavigationPlus extends JPlugin
{

	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		$app = JFactory::getApplication();
		$view = $app->input->get('view');
		$print = $app->input->getBool('print');

		if ($print)
		{
			return false;
		}

		if (($context == 'com_content.article') && ($view == 'article') && $params->get('show_item_navigation'))
		{
			$db = JFactory::getDbo();
			$user = JFactory::getUser();
			$lang = JFactory::getLanguage();
			$nullDate = $db->getNullDate();

			$date = JFactory::getDate();
			$now = $date->toSql();

			$uid = $row->id;
			$option = 'com_content';
			$canPublish = $user->authorise('core.edit.state', $option . '.article.' . $row->id);

			// The following is needed as different menu items types utilise a different param to control ordering.
			// For Blogs the `orderby_sec` param is the order controlling param.
			// For Table and List views it is the `orderby` param.
			$params_list = $params->toArray();
			if (array_key_exists('orderby_sec', $params_list))
			{
				$order_method = $params->get('orderby_sec', '');
			}
			else
			{
				$order_method = $params->get('orderby', '');
			}
			// Additional check for invalid sort ordering.
			if ($order_method == 'front')
			{
				$order_method = '';
			}

			// Determine sort order.
			switch ($order_method)
			{
				case 'date' :
					$orderby = 'a.created';
					break;
				case 'rdate' :
					$orderby = 'a.created DESC';
					break;
				case 'alpha' :
					$orderby = 'a.title';
					break;
				case 'ralpha' :
					$orderby = 'a.title DESC';
					break;
				case 'hits' :
					$orderby = 'a.hits';
					break;
				case 'rhits' :
					$orderby = 'a.hits DESC';
					break;
				case 'order' :
					$orderby = 'a.ordering';
					break;
				case 'author' :
					$orderby = 'a.created_by_alias, u.name';
					break;
				case 'rauthor' :
					$orderby = 'a.created_by_alias DESC, u.name DESC';
					break;
				case 'front' :
					$orderby = 'f.ordering';
					break;
				default :
					$orderby = 'a.ordering';
					break;
			}

			$xwhere = ' AND (a.state = 1 OR a.state = -1)' .
				' AND (publish_up = ' . $db->quote($nullDate) . ' OR publish_up <= ' . $db->quote($now) . ')' .
				' AND (publish_down = ' . $db->quote($nullDate) . ' OR publish_down >= ' . $db->quote($now) . ')';

			// Array of articles in same category correctly ordered.
			$query = $db->getQuery(true);

			// Sqlsrv changes
			$case_when = ' CASE WHEN ';
			$case_when .= $query->charLength('a.alias', '!=', '0');
			$case_when .= ' THEN ';
			$a_id = $query->castAsChar('a.id');
			$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
			$case_when .= ' ELSE ';
			$case_when .= $a_id . ' END as slug';

			$case_when1 = ' CASE WHEN ';
			$case_when1 .= $query->charLength('cc.alias', '!=', '0');
			$case_when1 .= ' THEN ';
			$c_id = $query->castAsChar('cc.id');
			$case_when1 .= $query->concatenate(array($c_id, 'cc.alias'), ':');
			$case_when1 .= ' ELSE ';
			$case_when1 .= $c_id . ' END as catslug';
			$query->select('a.id, a.title,' . $case_when . ',' . $case_when1)
				->from('#__content AS a')
				->join('LEFT', '#__categories AS cc ON cc.id = a.catid')
				->where(
					'a.catid = ' . (int) $row->catid . ' AND a.state = ' . (int) $row->state
						. ($canPublish ? '' : ' AND a.access = ' . (int) $row->access) . $xwhere
				);
			$query->order($orderby);
			if ($app->isSite() && $app->getLanguageFilter())
			{
				$query->where('a.language in (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');
			}

			$db->setQuery($query);
			$list = $db->loadObjectList('id');

			// This check needed if incorrect Itemid is given resulting in an incorrect result.
			if (!is_array($list))
			{
				$list = array();
			}

			reset($list);

			// Location of current content item in array list.
			$location = array_search($uid, array_keys($list));

			$rows = array_values($list);

			$row->prev = null;
			$row->next = null;

			$direction = $this->params->get('direction', 1);

			if ($location - 1 >= 0)
			{
				// The previous content item cannot be in the array position -1.
				if($direction):
					$row->prev = $rows[$location - 1];
				else:
					$row->next = $rows[$location - 1];
				endif;
			}

			if (($location + 1) < count($rows))
			{
				// The next content item cannot be in an array position greater than the number of array postions.
				if($direction):
					$row->next = $rows[$location + 1];
				else:
					$row->prev = $rows[$location + 1];
				endif;
			}

			$pnSpace = "";
			if (JText::_('JGLOBAL_LT') || JText::_('JGLOBAL_GT'))
			{
				$pnSpace = " ";
			}

			if ($row->prev)
			{
				$row->prev_title = $row->prev->title;
				$row->prev = JRoute::_(ContentHelperRoute::getArticleRoute($row->prev->slug, $row->prev->catslug));
			}
			else
			{
				$row->prev_title = '';
				$row->prev = '';
			}

			if ($row->next)
			{
				$row->next_title = $row->next->title;
				$row->next = JRoute::_(ContentHelperRoute::getArticleRoute($row->next->slug, $row->next->catslug));
			}
			else
			{
				$row->next_title = '';
				$row->next = '';
			}

			// Output
			if ($row->prev || $row->next)
			{
				if(empty($row->prev) && $this->params->get('show_parent', 1))
				{
					$row->prev = $this->parent_link($row->catid);
					$row->prev_title = $this->parent_title();
				}
				if(empty($row->next) && $this->params->get('show_parent', 1))
				{
					$row->next = $this->parent_link($row->catid);
					$row->next_title = $this->parent_title();
				}
				// Get the path for the layout file
				$path = JPluginHelper::getLayoutPath('content', 'jextboxpagenavigationplus');

				// Render the pagenav
				ob_start();
				include $path;
				$row->pagination = ob_get_clean();

				$row->paginationposition = $this->params->get('position', 1);

				// This will default to the 1.5 and 1.6-1.7 behavior.
				$row->paginationrelative = $this->params->get('relative', 0);
			}
		}

		return;
	}

	private function parent_link($catid)
	{

		return
		(
			$this->params->get('parent_type', 1)
			?
				JURI::root()
			:
				JRoute::_(ContentHelperRoute::getCategoryRoute($catid))
		);

	}

	private function parent_title(){

		if(empty($title = $this->params->get('parent_title', ''))):
			$title =
			(
				$this->params->get('parent_type', 1)
				?
					JText::_('JERROR_LAYOUT_HOME_PAGE')
				:
					JText::_('JCATEGORY')
			);
		endif;
		return $title;

	}

}
