<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version		2.0.10
 * @license		GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright	(C) 2008-2015 Mobile Joomla!
 * @date		September 2015
 */
defined('_JEXEC') or die;

class JFormFieldJqmText extends JFormField
{
	protected $type = 'jqmText';
	protected function getLabel()
	{
		return '<{jqmstart}/><div class="ui-field-contain">'.parent::getLabel().'<{jqmend}/>';
	}
	protected function getInput()
	{
		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';

		$html = array();
		$html[] = '<{jqmstart}/>';
		$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"'
			. ' value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"'
			. ' placeholder="' . htmlspecialchars((string)$this->element['default'], ENT_COMPAT, 'UTF-8') . '"'
			.' data-mini="true"' . $size . $maxLength . '/>';
		$html[] = '</div><{jqmend}/>';

		return implode($html);
	}
}
