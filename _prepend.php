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

$d = dirname(__FILE__) . '/inc/';

$__autoload['cinecturlink2']                        = $d . 'class.cinecturlink2.php';
$__autoload['cinecturlink2Context']                 = $d . 'lib.cinecturlink2.context.php';
$__autoload['adminlistCinecturlink2']               = $d . 'lib.cinecturlink2.list.php';
$__autoload['sitemapsCinecturlink2']                = $d . 'lib.sitemaps.cinecturlink2.php';
$__autoload['cinecturlink2ActivityReportBehaviors'] = $d . 'lib.cinecturlink2.activityreport.php';

$core->url->register(
    'cinecturlink2',
    'cinecturlink',
    '^cinecturlink(?:/(.+))?$',
    ['urlCinecturlink2', 'c2Page']
);

$core->addBehavior(
    'sitemapsDefineParts',
    ['sitemapsCinecturlink2', 'sitemapsDefineParts']
);
$core->addBehavior(
    'sitemapsURLsCollect',
    ['sitemapsCinecturlink2', 'sitemapsURLsCollect']
);

if (defined('ACTIVITY_REPORT')) {
    cinecturlink2ActivityReportBehaviors::add($core);
}
