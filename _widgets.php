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

dcCore::app()->addBehavior(
    'initWidgets',
    ['cinecturlink2Widget', 'adminLinks']
);
dcCore::app()->addBehavior(
    'initWidgets',
    ['cinecturlink2Widget', 'adminCats']
);

class cinecturlink2Widget
{
    public static function adminLinks($w)
    {
        $C2 = new cinecturlink2();

        $categories_combo = ['' => '', __('Uncategorized') => 'null'];
        $categories       = $C2->getCategories();
        while ($categories->fetch()) {
            $cat_title                    = html::escapeHTML($categories->cat_title);
            $categories_combo[$cat_title] = $categories->cat_id;
        }

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
                ['cinecturlink2Widget', 'publicLinks'],
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
    }

    public static function adminCats($w)
    {
        $w
            ->create(
                'cinecturlink2cats',
                __('List of categories of cinecturlink'),
                ['cinecturlink2Widget', 'publicCats'],
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

    public static function publicLinks($w)
    {
        dcCore::app()->blog->settings->addNamespace('cinecturlink2');

        if (!dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_active
            || !$w->checkHomeOnly(dcCore::app()->url->type)
        ) {
            return null;
        }

        $C2 = new cinecturlink2();

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
                return null;
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
            return null;
        }

        $widthmax = (int) dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_widthmax;
        $style    = $widthmax ? ' style="width:' . $widthmax . 'px;"' : '';

        $entries = [];
        while ($rs->fetch()) {
            $url    = $rs->link_url;
            $img    = $rs->link_img;
            $title  = html::escapeHTML($rs->link_title);
            $author = html::escapeHTML($rs->link_author);
            $cat    = html::escapeHTML($rs->cat_title);
            $note   = $w->shownote ? ' <em>(' . $rs->link_note . '/20)</em>' : '';
            $desc   = $w->showdesc ? '<br /><em>' . html::escapeHTML($rs->link_desc) . '</em>' : '';
            $lang   = $rs->link_lang ? ' hreflang="' . $rs->link_lang . '"' : '';
            $count  = abs((int) $rs->link_count);

            # --BEHAVIOR-- cinecturlink2WidgetLinks
            $bhv = dcCore::app()->callBehavior('cinecturlink2WidgetLinks', $rs->link_id);

            $entries[] = '<p style="text-align:center;">' .
            ($w->withlink && !empty($url) ? '<a href="' . $url . '"' . $lang . ' title="' . $cat . '">' : '') .
            '<strong>' . $title . '</strong>' . $note . '<br />' .
            ($w->showauthor ? $author . '<br />' : '') . '<br />' .
            '<img src="' . $img . '" alt="' . $title . ' - ' . $author . '"' . $style . ' />' .
            $desc .
            ($w->withlink && !empty($url) ? '</a>' : '') .
            '</p>' . $bhv;

            try {
                $cur             = dcCore::app()->con->openCursor($C2->table);
                $cur->link_count = ($count + 1);
                $C2->updLink($rs->link_id, $cur, false);
            } catch (Exception $e) {
            }
        }
        # Tirage aléatoire
        if ($w->sortby    == 'RANDOM'
            || $w->sortby == 'COUNTER'
        ) {
            shuffle($entries);
            if (dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_triggeronrandom) {
                dcCore::app()->blog->triggerBlog();
            }
        }

        return $w->renderDiv(
            $w->content_only,
            'cinecturlink2list ' . $w->class,
            '',
            ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '') . implode(' ', $entries) .
            (
                $w->showpagelink && dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_active ?
                '<p><a href="' . dcCore::app()->blog->url . dcCore::app()->url->getBase('cinecturlink2') . '" title="' . __('view all links') . '">' . __('More links') . '</a></p>' : ''
            )
        );
    }

    public static function publicCats($w)
    {
        dcCore::app()->blog->settings->addNamespace('cinecturlink2');

        if (!dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_active
            || !dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_active
            || !$w->checkHomeOnly(dcCore::app()->url->type)
        ) {
            return null;
        }

        $C2 = new cinecturlink2();
        $rs = $C2->getCategories([]);
        if ($rs->isEmpty()) {
            return null;
        }

        $res   = [];
        $res[] = '<li><a href="' .
            dcCore::app()->blog->url . dcCore::app()->url->getBase('cinecturlink2') .
            '" title="' . __('view all links') . '">' . __('all links') .
            '</a>' . ($w->shownumlink ? ' (' . ($C2->getLinks([], true)->f(0)) . ')' : '') .
            '</li>';

        while ($rs->fetch()) {
            $res[] = '<li><a href="' .
                dcCore::app()->blog->url . dcCore::app()->url->getBase('cinecturlink2') . '/' .
                dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_caturl . '/' .
                urlencode($rs->cat_title) .
                '" title="' . __('view links of this category') . '">' .
                html::escapeHTML($rs->cat_title) .
                '</a>' . ($w->shownumlink ? ' (' .
                    ($C2->getLinks(['cat_id' => $rs->cat_id], true)->f(0)) . ')' : '') .
                '</li>';
        }

        return $w->renderDiv(
            $w->content_only,
            'cinecturlink2cat ' . $w->class,
            '',
            ($w->title ? $w->renderTitle(html::escapeHTML($w->title)) : '') .
            '<ul>' . implode(' ', $res) . '</ul>'
        );
    }
}
