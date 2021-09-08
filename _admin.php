<?php
/**
 * @brief cinecturlink2, a plugin for Dotclear 2
 * 
 * @package Dotclear
 * @subpackage Plugin
 * 
 * @author Jean-Christian Denis and Contributors
 * 
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

require_once dirname(__FILE__) . '/_widgets.php';

$_menu['Plugins']->addItem(
    __('My cinecturlink'),
    $core->adminurl->get('admin.plugin.cinecturlink2'),
    dcPage::getPF('cinecturlink2/icon.png'),
    preg_match(
        '/' . preg_quote($core->adminurl->get('admin.plugin.dcAdvancedCleaner')) . '(&.*)?$/', 
        $_SERVER['REQUEST_URI']
    ),
    $core->auth->check('contentadmin', $core->blog->id)
);

$core->addBehavior(
    'adminDashboardFavorites',
    ['cinecturlink2AdminBehaviors', 'adminDashboardFavorites']
);

class cinecturlink2AdminBehaviors
{
    public static function adminDashboardFavorites($core, $favs)
    {
        $favs->register('cinecturlink2', [
            'title' => __('My cinecturlink'),
            'url' => $core->adminurl->get('admin.plugin.cinecturlink2').'#links',
            'small-icon' => dcPage::getPF('cinecturlink2/icon.png'),
            'large-icon' => dcPage::getPF('cinecturlink2/icon-big.png'),
            'permissions' => $core->auth->check('contentadmin', $core->blog->id),
            'active_cb'    => ['cinecturlink2AdminBehaviors', 'adminDashboardFavoritesActive']
        ]);
    }

    public static function adminDashboardFavoritesActive($request, $params)
    {
        return $request == 'plugin.php' 
            && isset($params['p']) 
            && $params['p'] == 'cinecturlink2';
    }
}