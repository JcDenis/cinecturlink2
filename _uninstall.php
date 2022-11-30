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

$this->addUserAction(
    /* type */
    'settings',
    /* action */
    'delete_all',
    /* ns */
    'cinecturlink2',
    /* desc */
    __('delete all settings')
);

$this->addUserAction(
    /* type */
    'tables',
    /* action */
    'delete',
    /* ns */
    initCinecturlink2::CINECTURLINK_TABLE_NAME,
    /* desc */
    sprintf(__('delete %s table'), 'cinecturlink2')
);

$this->addUserAction(
    /* type */
    'tables',
    /* action */
    'delete',
    /* ns */
    initCinecturlink2::CATEGORY_TABLE_NAME,
    /* desc */
    sprintf(__('delete %s table'), 'cinecturlink2_cat')
);

$this->addUserAction(
    /* type */
    'versions',
    /* action */
    'delete',
    /* ns */
    'cinecturlink2',
    /* desc */
    __('delete the version number')
);

$this->addUserAction(
    /* type */
    'plugins',
    /* action */
    'delete',
    /* ns */
    'cinecturlink2',
    /* desc */
    __('delete plugin files')
);

$this->addDirectAction(
    /* type */
    'settings',
    /* action */
    'delete_all',
    /* ns */
    'cinecturlink2',
    /* desc */
    sprintf(__('delete all %s settings'), 'cinecturlink2')
);

$this->addDirectAction(
    /* type */
    'tables',
    /* action */
    'delete',
    /* ns */
    initCinecturlink2::CINECTURLINK_TABLE_NAME,
    /* desc */
    sprintf(__('delete %s table'), 'cinecturlink2')
);

$this->addDirectAction(
    /* type */
    'tables',
    /* action */
    'delete',
    /* ns */
    initCinecturlink2::CATEGORY_TABLE_NAME,
    /* desc */
    sprintf(__('delete %s table'), 'cinecturlink2_cat')
);

$this->addDirectAction(
    /* type */
    'versions',
    /* action */
    'delete',
    /* ns */
    'cinecturlink2',
    /* desc */
    sprintf(__('delete %s version number'), 'cinecturlink2')
);

$this->addDirectAction(
    /* type */
    'plugins',
    /* action */
    'delete',
    /* ns */
    'cinecturlink2',
    /* description */
    sprintf(__('delete %s plugin files'), 'cinecturlink2')
);
