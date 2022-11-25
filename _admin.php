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
    cinecturlink2AdminUrl(),
    cinecturlink2AdminIcon(),
    preg_match('/' . preg_quote(cinecturlink2AdminUrl()) . '(&.*)?$/', $_SERVER['REQUEST_URI']),
     cinecturlink2AdmiPerm(),
);

dcCore::app()->addBehavior('adminColumnsListsV2', function (ArrayObject $cols) {
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
});

dcCore::app()->addBehavior('adminFiltersListsV2', function (ArrayObject $sorts) {
    $sorts['c2link'] = [
        __('Cinecturlink'),
        [
            __('Date')        => 'link_upddt',
            __('Title')       => 'link_title',
            __('Category')    => 'cat_id',
            __('Author')      => 'link_author',
            __('Description') => 'link_desc',
            __('Link')        => 'link_url',
            __('Rating')      => 'link_note',
        ],
        'link_upddt',
        'desc',
        [__('Links per page'), 30],
    ];
});

dcCore::app()->addBehavior('adminDashboardFavoritesV2', function (dcFavorites $favs) {
    $favs->register('cinecturlink2', [
        'title'       => __('My cinecturlink'),
        'url'         => cinecturlink2AdminUrl() . '#links',
        'small-icon'  => cinecturlink2AdminIcon(),
        'large-icon'  => cinecturlink2AdminIcon(),
        'permissions' =>  cinecturlink2AdmiPerm(),
    ]);
});

function cinecturlink2AdminUrl(): string
{
    return dcCore::app()->adminurl->get('admin.plugin.cinecturlink2');
}

function cinecturlink2AdminIcon(): string
{
    return urldecode(dcPage::getPF('cinecturlink2/icon.svg'));
}

function cinecturlink2AdmiPerm(): bool
{
    return dcCore::app()->auth->check(dcAuth::PERMISSION_CONTENT_ADMIN, dcCore::app()->blog->id);
}
