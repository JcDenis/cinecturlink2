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
        '/' . preg_quote($core->adminurl->get('admin.plugin.cinecturlink2')) . '(&.*)?$/', 
        $_SERVER['REQUEST_URI']
    ),
    $core->auth->check('contentadmin', $core->blog->id)
);

$core->addBehavior(
    'adminColumnsLists', 
    ['cinecturlink2AdminBehaviors', 'adminColumnsLists']
);

$core->addBehavior(
    'adminSortsLists', 
    ['cinecturlink2AdminBehaviors', 'adminSortsLists']
);
$core->addBehavior(
    'adminDashboardFavorites',
    ['cinecturlink2AdminBehaviors', 'adminDashboardFavorites']
);

class cinecturlink2AdminBehaviors
{
    public static function adminColumnsLists($core, $cols)
    {
        $cols['c2link'] = [
            __('Cinecturlink'),
            [
                'date'   => [true, __('Date')],
                'cat'    => [true, __('Category')],
                'author' => [true, __('Author')],
                'desc'   => [false, __('Description')],
                'link'   => [true, __('Liens')],
                'note'   => [true, __('Rating')],
            ]
        ];
    }

    public static function adminSortsLists($core, $sorts)
    {
        $sorts['c2link'] = [
            __('Cinecturlink'),
            [
                __('Date')        => 'link_upddt',
                __('Title')       => 'link_title',
                __('Category')    => 'cat_id',
                __('Author')      => 'link_author',
                __('Description') => 'link_desc',
                __('Link')       => 'link_url',
                __('Rating')      => 'link_note'
            ],
            'link_upddt',
            'desc',
            null
        ];
    }

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