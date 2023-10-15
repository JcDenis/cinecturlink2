<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @brief       cinecturlink2 widgets class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Widgets
{
    public static function init(WidgetsStack $w): void
    {
        $categories_combo = array_merge(
            Combo::categoriesCombo(),
            [__('Uncategorized') => 'null']
        );

        $sortby_combo = [
            __('Update date')     => 'link_upddt',
            __('My rating')       => 'link_note',
            __('Title')           => 'link_title',
            __('Random')          => 'RANDOM',
            __('Number of views') => 'COUNTER',
        ];
        $order_combo = [
            __('Ascending')  => 'asc',
            __('Descending') => 'desc',
        ];

        $w
            ->create(
                'cinecturlink2links',
                __('My cinecturlink'),
                self::parseLinks(...),
                null,
                __('Show selection of cinecturlinks')
            )
            ->addTitle(
                __('My cinecturlink'),
            )
            ->setting(
                'category',
                __('Category:'),
                '',
                'combo',
                $categories_combo
            )
            ->setting(
                'sortby',
                __('Order by:'),
                'link_upddt',
                'combo',
                $sortby_combo
            )
            ->setting(
                'sort',
                __('Sort: (only for date, note and title)'),
                'desc',
                'combo',
                $order_combo
            )
            ->setting(
                'limit',
                __('Limit:'),
                10,
                'text'
            )
            ->setting(
                'withlink',
                __('Enable link'),
                1,
                'check'
            )
            ->setting(
                'showauthor',
                __('Show author'),
                1,
                'check'
            )
            ->setting(
                'shownote',
                __('Show my rating'),
                0,
                'check'
            )
            ->setting(
                'showdesc',
                __('Show description'),
                0,
                'check'
            )
            ->setting(
                'showpagelink',
                __('Show a link to cinecturlink page'),
                0,
                'check'
            )
            ->addHomeOnly()
            ->addContentOnly()
            ->addClass()
            ->addOffline();

        $w
            ->create(
                'cinecturlink2cats',
                __('List of categories of cinecturlink'),
                self::parseCats(...),
                null,
                __('List of categories of cinecturlink')
            )
            ->addTitle(
                __('My cinecturlink by categories')
            )
            ->setting(
                'title',
                __('Title:'),
                __('My cinecturlink by categories'),
                'text'
            )
            ->setting(
                'shownumlink',
                __('Show number of links'),
                0,
                'check'
            )
            ->addHomeOnly()
            ->addContentOnly()
            ->addClass()
            ->addOffline();
    }

    public static function parseLinks(WidgetsElement $w): string
    {
        if (!My::settings()->avtive
            || !$w->checkHomeOnly(App::url()->type)
        ) {
            return '';
        }

        $C2     = new Utils();
        $params = [];

        if ($w->category) {
            if ($w->category == 'null') {
                $params['sql'] = ' AND L.cat_id IS NULL ';
            } elseif (is_numeric($w->category)) {
                $params['cat_id'] = (int) $w->category;
            }
        }

        $limit = abs((int) $w->limit);

        // Tirage aléatoire: Consomme beaucoup de ressources!
        if ($w->sortby == 'RANDOM') {
            $big_rs = $C2->getLinks($params);

            if ($big_rs->isEmpty()) {
                return '';
            }

            $ids = [];
            while ($big_rs->fetch()) {
                $ids[] = $big_rs->link_id;
            }
            shuffle($ids);
            $ids = array_slice($ids, 0, $limit);

            $params['link_id'] = [];
            foreach ($ids as $id) {
                $params['link_id'][] = $id;
            }
        } elseif ($w->sortby == 'COUNTER') {
            $params['order'] = 'link_count asc';
            $params['limit'] = $limit;
        } else {
            $params['order'] = $w->sortby;
            $params['order'] .= $w->sort == 'asc' ? ' asc' : ' desc';
            $params['limit'] = $limit;
        }

        $rs = $C2->getLinks($params);

        if ($rs->isEmpty()) {
            return '';
        }

        $widthmax = (int) My::settings()->widthmax;
        $style    = $widthmax ? ' style="width:' . $widthmax . 'px;"' : '';

        $entries = [];
        while ($rs->fetch()) {
            $url    = $rs->link_url;
            $img    = $rs->link_img;
            $title  = Html::escapeHTML($rs->link_title);
            $author = Html::escapeHTML($rs->link_author);
            $cat    = Html::escapeHTML($rs->cat_title);
            $note   = $w->shownote ? ' <em>(' . $rs->link_note . '/20)</em>' : '';
            $desc   = $w->showdesc ? '<br /><em>' . Html::escapeHTML($rs->link_desc) . '</em>' : '';
            $lang   = $rs->link_lang ? ' hreflang="' . $rs->link_lang . '"' : '';
            $count  = abs((int) $rs->link_count);

            # --BEHAVIOR-- cinecturlink2WidgetLinks
            $bhv = App::behavior()->callBehavior('cinecturlink2WidgetLinks', $rs->link_id);

            $entries[] = '<p style="text-align:center;">' .
            ($w->withlink && !empty($url) ? '<a href="' . $url . '"' . $lang . ' title="' . $cat . '">' : '') .
            '<strong>' . $title . '</strong>' . $note . '<br />' .
            ($w->showauthor ? $author . '<br />' : '') . '<br />' .
            '<img src="' . $img . '" alt="' . $title . ' - ' . $author . '"' . $style . ' />' .
            $desc .
            ($w->withlink && !empty($url) ? '</a>' : '') .
            '</p>' . $bhv;

            try {
                $cur             = App::con()->openCursor($C2->table);
                $cur->link_count = ($count + 1);
                $C2->updLink((int) $rs->link_id, $cur, false);
            } catch (Exception $e) {
            }
        }
        # Tirage aléatoire
        if ($w->sortby    == 'RANDOM'
            || $w->sortby == 'COUNTER'
        ) {
            shuffle($entries);
            if (My::settings()->triggeronrandom) {
                App::blog()->triggerBlog();
            }
        }

        return $w->renderDiv(
            (bool) $w->content_only,
            'cinecturlink2list ' . $w->class,
            '',
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') . implode(' ', $entries) .
            (
                $w->showpagelink && My::settings()->public_active ?
                '<p><a href="' . App::blog()->url() . App::url()->getBase(My::id()) . '" title="' . __('view all links') . '">' . __('More links') . '</a></p>' : ''
            )
        );
    }

    public static function parseCats(WidgetsElement $w): string
    {
        if (!My::settings()->avtive
            || !My::settings()->public_active
            || !$w->checkHomeOnly(App::url()->type)
        ) {
            return '';
        }

        $C2 = new Utils();
        $rs = $C2->getCategories([]);
        if ($rs->isEmpty()) {
            return '';
        }

        $res   = [];
        $res[] = '<li><a href="' .
            App::blog()->url() . App::url()->getBase(My::id()) .
            '" title="' . __('view all links') . '">' . __('all links') .
            '</a>' . ($w->shownumlink ? ' (' . ($C2->getLinks([], true)->f(0)) . ')' : '') .
            '</li>';

        while ($rs->fetch()) {
            $res[] = '<li><a href="' .
                App::blog()->url() . App::url()->getBase('cinecturlink2') . '/' .
                My::settings()->public_caturl . '/' .
                urlencode($rs->cat_title) .
                '" title="' . __('view links of this category') . '">' .
                Html::escapeHTML($rs->cat_title) .
                '</a>' . ($w->shownumlink ? ' (' .
                    ($C2->getLinks(['cat_id' => $rs->cat_id], true)->f(0)) . ')' : '') .
                '</li>';
        }

        return $w->renderDiv(
            (bool) $w->content_only,
            'cinecturlink2cat ' . $w->class,
            '',
            ($w->title ? $w->renderTitle(Html::escapeHTML($w->title)) : '') .
            '<ul>' . implode(' ', $res) . '</ul>'
        );
    }
}
