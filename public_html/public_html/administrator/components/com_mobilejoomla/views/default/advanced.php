<?php
/**
 * Mobile Joomla!
 * http://www.mobilejoomla.com
 *
 * @version    2.0.10
 * @license    GNU/GPL v2 - http://www.gnu.org/licenses/gpl-2.0.html
 * @copyright  (C) 2008-2015 Mobile Joomla!
 * @date       September 2015
 */
defined('_JEXEC') or die('Restricted access');

/** @var MjController $this */
/** @var array $params */
/** @var string $controllerName */
/** @var string $viewName */

echo $this->renderView('global/header');

JToolbarHelper::apply();
JToolbarHelper::save();
JToolbarHelper::cancel();

// populate $settings array
include_once JPATH_COMPONENT . '/models/settings.php';
$mjSettings = new MjSettingsModel($this->joomlaWrapper);

$lists = array();

/* // @todo mark some options as disabled
if (!function_exists('apache_get_modules')) {
    $enable_apache = true;
    $enable_apachephp = true;
} else {
    $enable_apache = false;
    $enable_apachephp = false;
    $apache_modules = apache_get_modules();
    if (in_array('mod_rewrite', $apache_modules, true)) {
        $enable_apachephp = true;
        if (in_array('mod_headers', $apache_modules, true)
            && in_array('mod_mime', $apache_modules, true)
            && in_array('mod_expires', $apache_modules, true)
        ) {
            $enable_apache = true;
        }
    }
} */
$lists['distribmode'] = array(
    '' => JText::_('COM_MJ__DISTRIBMODE__DEFAULT'),
    'apache' => JText::_('COM_MJ__DISTRIBMODE__APACHE'),
    'apachephp' => JText::_('COM_MJ__DISTRIBMODE__APACHEPHP'),
    'php' => JText::_('COM_MJ__DISTRIBMODE__PHP')
);

$form = array(
    //left
    array(
        'COM_MJ__PERFORMANCE' => array(
            array(
                'label' => MjHtml::label('caching', 'COM_MJ__CACHING', 'COM_MJ__CACHING_DESC'),
                'input' => MjHtml::onoff('caching', $mjSettings->get('caching'))
            ),
            array(
                'label' => MjHtml::label('httpcaching', 'COM_MJ__BROWSER_CACHING', 'COM_MJ__BROWSER_CACHING_DESC'),
                'input' => MjHtml::onoff('httpcaching', $mjSettings->get('httpcaching'))
            ),
            array(
                'label' => MjHtml::label('.gzip', 'COM_MJ__GZIP_COMPRESSION', 'COM_MJ__GZIP_COMPRESSION_DESC'),
                'input' => MjHtml::onoff('.gzip', $mjSettings->get('.gzip'))
            ),
            array(
                'label' => MjHtml::label('distribmode', 'COM_MJ__DISTRIBUTE_USING', 'COM_MJ__DISTRIBUTE_USING_DESC'),
                'input' => MjHtml::select('distribmode', $mjSettings->get('distribmode'), $lists['distribmode'])
            )
        ),
        'COM_MJ__REDIRECT_TO_DOMAIN' => array(),
        'COM_MJ__COMPATIBILITY' => array(
            array(
                'label' => MjHtml::label('.removetags', 'COM_MJ__REMOVE_UNSUPPORTED_TAGS', 'COM_MJ__REMOVE_UNSUPPORTED_TAGS_DESC'),
                'input' => MjHtml::onoff('.removetags', $mjSettings->get('.removetags'))
            ),
            array(
                'label' => MjHtml::label('nomjitems', 'COM_MJ__NOMJITEMS', 'COM_MJ__NOMJITEMS_DESC'),
                'input' => MjHtml::menulist('nomjitems', $mjSettings->get('nomjitems'), true)
            )
        )
    ),
    //right
    array(
        'COM_MJ__RESS' => array(
            array(
                'label' => MjHtml::label('ress_optimize', 'COM_MJ__RESS_OPTIMIZE_ENABLED', 'COM_MJ__RESS_OPTIMIZE_ENABLED_DESC'),
                'input' => MjHtml::onoff('ress_optimize', $mjSettings->get('ress_optimize'))
            ),
        ),
        'COM_MJ__HTML' => array(
            array(
                'label' => MjHtml::label('.html_removecomments', 'COM_MJ__HTML_REMOVE_COMMENTS', 'COM_MJ__HTML_REMOVE_COMMENTS_DESC'),
                'input' => MjHtml::onoff('.html_removecomments', $mjSettings->get('.html_removecomments'))
            ),
            array(
                'label' => MjHtml::label('.html_mergespace', 'COM_MJ__HTML_MERGE_SPACES', 'COM_MJ__HTML_MERGE_SPACES_DESC'),
                'input' => MjHtml::onoff('.html_mergespace', $mjSettings->get('.html_mergespace'))
            ),
            array(
                'label' => MjHtml::label('.html_minifyurl', 'COM_MJ__HTML_MINIFY_URL', 'COM_MJ__HTML_MINIFY_URL_DESC'),
                'input' => MjHtml::onoff('.html_minifyurl', $mjSettings->get('.html_minifyurl'))
            )
        ),
        'COM_MJ__IMAGE' => array(
            array(
                'label' => MjHtml::label('.img_lazyload', 'COM_MJ__IMAGE_LAZYLOAD', 'COM_MJ__IMAGE_LAZYLOAD_DESC'),
                'input' => MjHtml::onoff('.img_lazyload', $mjSettings->get('.img_lazyload'))
            ),
            array(
                'label' => MjHtml::label('.img_wrapwide', 'COM_MJ__IMAGE_WRAP_WIDE', 'COM_MJ__IMAGE_WRAP_WIDE_DESC'),
                'input' => MjHtml::onoff('.img_wrapwide', $mjSettings->get('.img_wrapwide'))
            ),
            array(
                'label' => MjHtml::label('.img_addstyles', 'COM_MJ__STYLE_IMAGE_SIZE', 'COM_MJ__STYLE_IMAGE_SIZE_DESC'),
                'input' => MjHtml::onoff('.img_addstyles', $mjSettings->get('.img_addstyles'))
            )
        ),
        'COM_MJ__CSS' => array(
            array(
                'label' => MjHtml::label('.css_merge', 'COM_MJ__CSS_MERGE', 'COM_MJ__CSS_MERGE_DESC'),
                'input' => MjHtml::onoff('.css_merge', $mjSettings->get('.css_merge'))
            ),
            array(
                'label' => MjHtml::label('.css_loadurl', 'COM_MJ__CSS_LOADURL', 'COM_MJ__CSS_LOADURL_DESC'),
                'input' => MjHtml::onoff('.css_loadurl', $mjSettings->get('.css_loadurl'))
            ),
            array(
                'label' => MjHtml::label('.css_optimize', 'COM_MJ__CSS_OPTIMIZE', 'COM_MJ__CSS_OPTIMIZE_DESC'),
                'input' => MjHtml::onoff('.css_optimize', $mjSettings->get('.css_optimize'))
            ),
            array(
                'label' => MjHtml::label('.css_inlinelimit', 'COM_MJ__CSS_INLINE_LIMIT', 'COM_MJ__CSS_INLINE_LIMIT_DESC'),
                'input' => '<div class="input-append">' .
                    MjHtml::numberinput('.css_inlinelimit', (int)$mjSettings->get('.css_inlinelimit'), array('class' => 'text-right')) .
                    '<span class="add-on">bytes</span>' .
                    '</div>'
            )
        ),
        'COM_MJ__JS' => array(
            array(
                'label' => MjHtml::label('.js_merge', 'COM_MJ__JS_MERGE', 'COM_MJ__JS_MERGE_DESC'),
                'input' => MjHtml::onoff('.js_merge', $mjSettings->get('.js_merge'))
            ),
            array(
                'label' => MjHtml::label('.js_loadurl', 'COM_MJ__JS_LOADURL', 'COM_MJ__JS_LOADURL_DESC'),
                'input' => MjHtml::onoff('.js_loadurl', $mjSettings->get('.js_loadurl'))
            ),
            array(
                'label' => MjHtml::label('.js_optimize', 'COM_MJ__JS_OPTIMIZE', 'COM_MJ__JS_OPTIMIZE_DESC'),
                'input' => MjHtml::onoff('.js_optimize', $mjSettings->get('.js_optimize'))
            ),
            array(
                'label' => MjHtml::label('.js_wraptrycatch', 'COM_MJ__JS_WRAPTRYCATCH', 'COM_MJ__JS_WRAPTRYCATCH_DESC'),
                'input' => MjHtml::onoff('.js_wraptrycatch', $mjSettings->get('.js_wraptrycatch'))
            ),
            array(
                'label' => MjHtml::label('.js_autosync', 'COM_MJ__JS_AUTOASYNC', 'COM_MJ__JS_AUTOASYNC_DESC'),
                'input' => MjHtml::onoff('.js_autosync', $mjSettings->get('.js_autosync'))
            ),
            array(
                'label' => MjHtml::label('.js_inlinelimit', 'COM_MJ__JS_INLINE_LIMIT', 'COM_MJ__JS_INLINE_LIMIT_DESC'),
                'input' => '<div class="input-append">' .
                    MjHtml::numberinput('.js_inlinelimit', (int)$mjSettings->get('.js_inlinelimit'), array('class' => 'text-right')) .
                    '<span class="add-on">bytes</span>' .
                    '</div>'
            )
        )
    )
);

include_once JPATH_COMPONENT . '/classes/mjhelper.php';

$modes = MjHelper::getDeviceList();

foreach ($modes as $device => $title) {
    if ($device === 'desktop') {
        continue;
    }

    $paramName = $device . '.domain';
    $form[0]['COM_MJ__REDIRECT_TO_DOMAIN'][] = array(
        'label' => MjHtml::label($paramName, $title, JText::sprintf('COM_MJ__DOMAIN_NAME_DESC', $title)),
        'input' => MjHtml::textinput($paramName, $mjSettings->get($paramName))
    );
}

echo $this->renderView('global/form', array(
    'form' => $form,
    'controllerName' => $controllerName,
    'viewName' => $viewName,
    'settings' => $mjSettings
));
