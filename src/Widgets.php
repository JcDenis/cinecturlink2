<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Helper\Html\Html;
use Dotclear\Plugin\widgets\WidgetsStack;
use Dotclear\Plugin\widgets\WidgetsElement;
use Exception;

/**
 * @brief       cinecturlink2 widgets class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Widgets
{
    /**
     * Widget cinecturlink links ID.
     *
     * @var     string  WIDGET_ID_LINKS
     */
    private const WIDGET_ID_LINKS = 'cinecturlink2links';

    /**
     * Widget cinecturlink categories ID.
     *
     * @var     string  WIDGET_ID_CATS
     */
    private const WIDGET_ID_CATS = 'cinecturlink2cats';

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
                self::WIDGET_ID_LINKS,
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
                self::WIDGET_ID_CATS,
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

    public static function parseLinks(WidgetsElement $widget): string
    {
        if (!My::settings()->get('active')
            || !$widget->checkHomeOnly(App::url()->type)
        ) {
            return '';
        }

        $wdesc  = new WidgetLinksDescriptor($widget);
        $utils  = new Utils();
        $params = [];

        if ($wdesc->category) {
            if ($wdesc->category == 'null') {
                $params['sql'] = ' AND L.cat_id IS NULL ';
            } elseif (is_numeric($wdesc->category)) {
                $params['cat_id'] = (int) $wdesc->category;
            }
        }

        // Tirage aléatoire: Consomme beaucoup de ressources!
        if ($wdesc->sortby == 'RANDOM') {
            $big_rs = $utils->getLinks($params);

            if ($big_rs->isEmpty()) {
                return '';
            }

            $ids = [];
            while ($big_rs->fetch()) {
                $ids[] = $big_rs->link_id;
            }
            shuffle($ids);
            $ids = array_slice($ids, 0, $wdesc->limit);

            $params['link_id'] = [];
            foreach ($ids as $id) {
                $params['link_id'][] = $id;
            }
        } elseif ($wdesc->sortby == 'COUNTER') {
            $params['order'] = 'link_count asc';
            $params['limit'] = $wdesc->limit;
        } else {
            $params['order'] = $wdesc->sortby . ' ' . $wdesc->sort;
            $params['limit'] = $wdesc->limit;
        }

        $rs = $utils->getLinks($params);

        if ($rs->isEmpty()) {
            return '';
        }

        $widthmax = (int) My::settings()->get('widthmax');
        $style    = $widthmax ? ' style="width:' . $widthmax . 'px;"' : '';

        $entries = [];
        while ($rs->fetch()) {
            $row = new RecordLinksRow($rs);

            # --BEHAVIOR-- cinecturlink2WidgetLinks
            $bhv = App::behavior()->callBehavior('cinecturlink2WidgetLinks', $row->link_id);

            $tmp = '';
            if ($wdesc->withlink && !empty($row->link_url)) {
                $tmp .= '<a href="' . $row->link_url . '"' . ($row->link_lang ? ' hreflang="' . $row->link_lang . '"' : '') . ' title="' . Html::escapeHTML($row->cat_title) . '">';
            }
            $tmp .= '<strong>' . Html::escapeHTML($row->link_title) . '</strong>' . ($wdesc->shownote ? ' <em>(' . $row->link_note . '/20)</em>' : '') . '<br />';
            if ($wdesc->showauthor) {
                $tmp .= Html::escapeHTML($row->link_author) . '<br />';
            }
            $tmp .= '<br /><img src="' . $row->link_img . '" alt="' . Html::escapeHTML($row->link_title) . ' - ' . Html::escapeHTML($row->link_author) . '"' . $style . ' />';
            if ($wdesc->showdesc) {
                $tmp .= '<br /><em>' . Html::escapeHTML($row->link_desc) . '</em>';
            }
            if ($wdesc->withlink && !empty($row->link_url)) {
                $tmp .= '</a>';
            }

            $entries[] = '<p style="text-align:center;">' . $tmp . '</p>' . $bhv;

            try {
                $cur = App::con()->openCursor($utils->table);
                $cur->setField('link_count', ($row->link_count + 1));
                $utils->updLink($row->link_id, $cur, false);
            } catch (Exception) {
            }
        }
        # Tirage aléatoire
        if (in_array($wdesc->sortby, ['RANDOM', 'COUNTER'])) {
            shuffle($entries);
            if (My::settings()->get('triggeronrandom')) {
                App::blog()->triggerBlog();
            }
        }

        return $widget->renderDiv(
            $wdesc->content_only,
            $widget->id() . ' ' . $wdesc->class,
            '',
            ($wdesc->title ? $widget->renderTitle(Html::escapeHTML($wdesc->title)) : '') . implode(' ', $entries) .
            (
                $wdesc->showpagelink && My::settings()->get('public_active') ?
                '<p><a href="' . App::blog()->url() . App::url()->getBase(My::id()) . '" title="' . __('view all links') . '">' . __('More links') . '</a></p>' : ''
            )
        );
    }

    public static function parseCats(WidgetsElement $widget): string
    {
        if (!My::settings()->get('active')
            || !My::settings()->get('public_active')
            || !$widget->checkHomeOnly(App::url()->type)
        ) {
            return '';
        }

        $wdesc = new WidgetCatsDescriptor($widget);
        $utils = new Utils();
        $rs    = $utils->getCategories([]);
        if ($rs->isEmpty()) {
            return '';
        }

        $res   = [];
        $res[] = '<li><a href="' .
            App::blog()->url() . App::url()->getBase(My::id()) .
            '" title="' . __('view all links') . '">' . __('all links') .
            '</a>' . ($wdesc->shownumlink ? ' (' . ($utils->getLinks([], true)->f(0)) . ')' : '') .
            '</li>';

        while ($rs->fetch()) {
            $row = new RecordCatsRow($rs);

            $res[] = '<li><a href="' .
                App::blog()->url() . App::url()->getBase('cinecturlink2') . '/' .
                My::settings()->get('public_caturl') . '/' .
                urlencode($row->cat_title) .
                '" title="' . __('view links of this category') . '">' .
                Html::escapeHTML($row->cat_title) .
                '</a>' . ($wdesc->shownumlink ? ' (' .
                    ($utils->getLinks(['cat_id' => $row->cat_id], true)->f(0)) . ')' : '') .
                '</li>';
        }

        return $widget->renderDiv(
            $wdesc->content_only,
            $widget->id() . ' ' . $wdesc->class,
            '',
            ($wdesc->title ? $widget->renderTitle(Html::escapeHTML($wdesc->title)) : '') .
            '<ul>' . implode(' ', $res) . '</ul>'
        );
    }
}
