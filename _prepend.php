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
if (!defined('DC_RC_PATH')) {
    return null;
}

Clearbricks::lib()->autoload(['cinecturlink2' => __DIR__ . '/inc/class.cinecturlink2.php']);
Clearbricks::lib()->autoload(['cinecturlink2Context' => __DIR__ . '/inc/lib.cinecturlink2.context.php']);
Clearbricks::lib()->autoload(['adminlistCinecturlink2' => __DIR__ . '/inc/lib.cinecturlink2.list.php']);
Clearbricks::lib()->autoload(['sitemapsCinecturlink2' => __DIR__ . '/inc/lib.sitemaps.cinecturlink2.php']);
Clearbricks::lib()->autoload(['cinecturlink2ActivityReportBehaviors' => __DIR__ . '/inc/lib.cinecturlink2.activityreport.php']);

dcCore::app()->url->register(
    'cinecturlink2',
    'cinecturlink',
    '^cinecturlink(?:/(.+))?$',
    ['urlCinecturlink2', 'c2Page']
);

dcCore::app()->addBehavior(
    'sitemapsDefineParts',
    ['sitemapsCinecturlink2', 'sitemapsDefineParts']
);
dcCore::app()->addBehavior(
    'sitemapsURLsCollect',
    ['sitemapsCinecturlink2', 'sitemapsURLsCollect']
);

if (defined('ACTIVITY_REPORT_V2')) {
    cinecturlink2ActivityReportBehaviors::add();
}
