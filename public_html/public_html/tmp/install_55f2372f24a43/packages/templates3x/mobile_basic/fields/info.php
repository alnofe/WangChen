<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version        2.0.4
 * @license        GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright    (C) 2008-2015 Mobile Joomla!
 * @date        September 2015
 */
defined('_JEXEC') or die;

class JFormFieldInfo extends JFormField
{
    protected $type = 'Info';

    protected function getLabel()
    {
        return '';
    }

    protected function getInput()
    {
        $title = (string)$this->element['title'];
        $docurl = (string)$this->element['docurl'];
        $extid = (int)$this->element['extid'];

        $html = array();

        $html[] = '<{jqmstart}/>';
        $html[] = '<p><h2>' . $title . ' 2.0.4</h2></p>';
        $html[] = '<p><b>Expiration</b>: <span class="mjactive"><span id="mjsubscription"></span> days left</span><span class="mjexpired">Expired</span> <a target="_blank" class="mjrenewurl ui-btn ui-mini" href="http://www.mobilejoomla.com/orders.html">Renew</a></p>';
        $html[] = '<p>Template for <a target="_blank" href="http://www.mobilejoomla.com/">Mobile Joomla!</a> extension</p>';
        $html[] = '<p><a target="_blank" href="' . $docurl . '">Documentation</a></p>';
        $html[] = '<p><a target="_blank" href="http://www.mobilejoomla.com/forum/18-premium-support.html">Premium support forum</a></p>';
        $html[] = '<{jqmend}/>';

        $css = ".mjactive,.mjexpired,.mjrenewurl{display:none}";
        $js = "
window.addEvent('domready',function(){
	function updateSubscription(expires){
		if(expires==''){//expired
			$$('.mjexpired').setStyle('display', 'inline');
			$$('.mjrenewurl').setStyle('display', 'inline');
		}else{//active
			var mjsubscription=$('mjsubscription');
			if(mjsubscription!=null){
				if(MooTools.version>='1.2')
					mjsubscription.set('html', expires);
				else
					mjsubscription.setHTML(expires);
			}
			$$('.mjactive').setStyle('display', 'inline');
			if(parseInt(expires)<=30)
				$$('.mjrenewurl').setStyle('display', 'inline');
		}
	}

	function checkSubscription(){
		if(typeof Request == 'function'){
			new Request.HTML( {
				url: 'http://www.mobilejoomla.com/getsubs.php?app=$extid&domain=' + document.domain,
				method: 'get',
				onSuccess : function(tree, elements, response){
					updateSubscription(response);
				}
			}).send();
		} else if(typeof Ajax == 'function'){
			new Ajax( 'http://www.mobilejoomla.com/getsubs.php?app=$extid&domain=' + document.domain, {
				method: 'get',
				onComplete: function(response){
					updateSubscription(response);
				}
			}).request();
		}
	}

	try{
		checkSubscription();
	}catch(e){}
});
";
        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration($css);
        $doc->addScriptDeclaration($js);

        return implode($html);
    }
}
