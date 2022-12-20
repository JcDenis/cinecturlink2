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
    '1.1.1',
    [
        'requires'    => [['core', '2.24']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]),
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . basename(__DIR__),
        'details'     => 'https://plugins.dotaddict.org/dc2/details/' . basename(__DIR__),
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . basename(__DIR__) . '/master/dcstore.xml',
    ]
);
