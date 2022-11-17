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

require_once __DIR__ . '/_widgets.php';

dcCore::app()->menu[dcAdmin::MENU_PLUGINS]->addItem(
    __('My cinecturlink'),
    dcCore::app()->adminurl->get('admin.plugin.cinecturlink2'),
    dcPage::getPF('cinecturlink2/icon.png'),
    preg_match(
        '/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.cinecturlink2')) . '(&.*)?$/',
        $_SERVER['REQUEST_URI']
    ),
    dcCore::app()->auth->check(dcAuth::PERMISSION_CONTENT_ADMIN, dcCore::app()->blog->id)
);

dcCore::app()->addBehavior(
    'adminColumnsListsV2',
    ['cinecturlink2AdminBehaviors', 'adminColumnsLists']
);

dcCore::app()->addBehavior(
    'adminFiltersListsV2',
    ['cinecturlink2AdminBehaviors', 'adminFiltersLists']
);
dcCore::app()->addBehavior(
    'adminDashboardFavoritesV2',
    ['cinecturlink2AdminBehaviors', 'adminDashboardFavorites']
);

class cinecturlink2AdminBehaviors
{
    public static function adminSortbyCombo()
    {
        return [
            __('Date')        => 'link_upddt',
            __('Title')       => 'link_title',
            __('Category')    => 'cat_id',
            __('Author')      => 'link_author',
            __('Description') => 'link_desc',
            __('Link')        => 'link_url',
            __('Rating')      => 'link_note',
        ];
    }

    public static function adminColumnsLists($cols)
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
            ],
        ];
    }

    public static function adminFiltersLists($sorts)
    {
        $sorts['c2link'] = [
            __('Cinecturlink'),
            self::adminSortbyCombo(),
            'link_upddt',
            'desc',
            [__('Links per page'), 30],
        ];
    }

    public static function adminDashboardFavorites($favs)
    {
        $favs->register('cinecturlink2', [
            'title'       => __('My cinecturlink'),
            'url'         => dcCore::app()->adminurl->get('admin.plugin.cinecturlink2') . '#links',
            'small-icon'  => dcPage::getPF('cinecturlink2/icon.png'),
            'large-icon'  => dcPage::getPF('cinecturlink2/icon-big.png'),
            'permissions' => dcCore::app()->auth->check('contentadmin', dcCore::app()->blog->id),
            'active_cb'   => ['cinecturlink2AdminBehaviors', 'adminDashboardFavoritesActive'],
        ]);
    }

    public static function adminDashboardFavoritesActive($request, $params)
    {
        return $request == 'plugin.php'
            && isset($params['p'])
            && $params['p'] == 'cinecturlink2';
    }
}
