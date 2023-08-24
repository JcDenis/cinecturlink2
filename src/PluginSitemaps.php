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
declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use dcCore;

class PluginSitemaps
{
    public static function sitemapsDefineParts($map_parts)
    {
        $map_parts->offsetSet(My::name(), My::id());
    }

    public static function sitemapsURLsCollect($sitemaps)
    {
        if (dcCore::app()->plugins->moduleExists('cinecturlink2')
            && dcCore::app()->blog->settings->sitemaps->sitemaps_cinecturlink2_url
        ) {
            $freq = $sitemaps->getFrequency(dcCore::app()->blog->settings->sitemaps->sitemaps_cinecturlink2_fq);
            $prio = $sitemaps->getPriority(dcCore::app()->blog->settings->sitemaps->sitemaps_cinecturlink2_pr);
            $base = dcCore::app()->blog->url . dcCore::app()->url->getBase('cinecturlink2');

            $sitemaps->addEntry($base, $prio, $freq);

            $C2   = new Utils();
            $cats = $C2->getCategories();
            while ($cats->fetch()) {
                $sitemaps->addEntry($base . '/' . dcCore::app()->blog->settings->cinecturlink2->public_caturl . '/' . urlencode($cats->cat_title), $prio, $freq);
            }
        }
    }
}
