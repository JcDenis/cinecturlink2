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
/*
 * Taken from cinecturlink for Dotclear 1
 * By Tigroux and Brol
 * Under GNU GPL 2.0 license
 */

if (!defined('DC_RC_PATH')) {
    return null;
}

$this->registerModule(
    'Cinecturlink 2',
    'Widgets and pages about books, musics, films, blogs you are interested in',
    'Jean-Christian Denis and Contributors',
    '2.0',
    [
        'requires' => [
            ['php', '8.1'],
            ['core', '2.27'],
        ],
        'settings' => [
            'blog' => '#params.' . basename(__DIR__) . '_params',
        ],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]),
        'type'       => 'plugin',
        'support'    => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/issues',
        'details'    => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/src/branch/master/README.md',
        'repository' => 'https://git.dotclear.watch/JcDenis/' . basename(__DIR__) . '/raw/branch/master/dcstore.xml',
    ]
);
