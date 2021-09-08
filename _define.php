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
    '0.8',
    [
        'requires' => [['core', '2.19']],
        'permissions' => 'contentadmin',
        'type' => 'plugin',
        'support' => 'https://github.com/JcDenis/cinecturlink2',
        'details' => 'https://plugins.dotaddict.org/dc2/details/cinecturlink2',
        'repository' => 'https://raw.githubusercontent.com/JcDenis/cinecturlink2/master/dcstore.xml'
    ]
);