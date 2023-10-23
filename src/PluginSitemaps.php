<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use ArrayObject;
use Dotclear\App;
use Dotclear\Plugin\sitemaps\Sitemap;

/**
 * @brief       cinecturlink2 sitemaps class.
 * @ingroup     cinecturlink2
 *
 * Add Cinecturlink public main page and categories pages to plugin sitemap.
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class PluginSitemaps
{
    /**
     * @param   ArrayObject<string, string>     $map_parts
     */
    public static function sitemapsDefineParts(ArrayObject $map_parts): void
    {
        $map_parts->offsetSet(My::name(), My::id());
    }

    public static function sitemapsURLsCollect(Sitemap $sitemaps): void
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
                $sitemaps->addEntry($base . '/' . My::settings()->get('public_caturl') . '/' . urlencode((string) $cats->field('cat_title')), $prio, $freq);
            }
        }
    }
}
