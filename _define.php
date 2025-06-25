<?php
/**
 * @file
 * @brief       The plugin cinecturlink2 definition
 * @ingroup     cinecturlink2
 *
 * @defgroup    cinecturlink2 Plugin cinecturlink2.
 *
 * Widgets and pages about books, musics, films, blogs you are interested in
 *
 * Taken from cinecturlink for Dotclear 1
 * By Tigroux and Brol
 * Under GNU GPL 2.0 license
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

$this->registerModule(
    'Cinecturlink 2',
    'Widgets and pages about books, musics, films, blogs you are interested in',
    'Jean-Christian Denis and Contributors',
    '2.3.4',
    [
        'requires'    => [['core', '2.28']],
        'settings'    => ['blog' => '#params.' . $this->id . '_params'],
        'permissions' => 'My',
        'type'        => 'plugin',
        'support'     => 'https://github.com/JcDenis/' . $this->id . '/issues',
        'details'     => 'https://github.com/JcDenis/' . $this->id . '/',
        'repository'  => 'https://raw.githubusercontent.com/JcDenis/' . $this->id . '/master/dcstore.xml',
        'date'        => '2025-06-25T22:12:28+00:00',
    ]
);
