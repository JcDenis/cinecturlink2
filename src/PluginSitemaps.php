<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;

/**
 * @brief       cinecturlink2 sitemaps class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class PluginSitemaps
{
    public static function sitemapsDefineParts($map_parts)
    {
        $map_parts->offsetSet(My::name(), My::id());
    }

    public static function sitemapsURLsCollect($sitemaps)
    {
        if (App::plugins()->moduleExists('cinecturlink2')
            && App::blog()->settings()->get('sitemaps')->get('sitemaps_cinecturlink2_url')
        ) {
            $freq = $sitemaps->getFrequency(App::blog()->settings()->get('sitemaps')->get('sitemaps_cinecturlink2_fq'));
            $prio = $sitemaps->getPriority(App::blog()->settings()->get('sitemaps')->get('sitemaps_cinecturlink2_pr'));
            $base = App::blog()->url() . App::url()->getBase('cinecturlink2');

            $sitemaps->addEntry($base, $prio, $freq);

            $C2   = new Utils();
            $cats = $C2->getCategories();
            while ($cats->fetch()) {
                $sitemaps->addEntry($base . '/' . My::settings()->get('public_caturl') . '/' . urlencode($cats->cat_title), $prio, $freq);
            }
        }
    }
}
