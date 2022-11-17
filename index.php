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

dcPage::check(dcAuth::PERMISSION_CONTENT_ADMIN);

$linkid     = $_REQUEST['linkid']  ?? '';
$linktitle  = $_POST['linktitle']  ?? '';
$linkdesc   = $_POST['linkdesc']   ?? '';
$linkauthor = $_POST['linkauthor'] ?? '';
$linkurl    = $_POST['linkurl']    ?? '';
$linkcat    = $_POST['linkcat']    ?? null;
$linklang   = $_POST['linklang']   ?? dcCore::app()->auth->getInfo('user_lang');
$linkimage  = $_POST['linkimage']  ?? '';
$linknote   = $_POST['linknote']   ?? '';
$catid      = $_REQUEST['catid']   ?? '';
$cattitle   = $_POST['cattitle']   ?? '';
$catdesc    = $_POST['catdesc']    ?? '';
$redir      = $_REQUEST['redir']   ?? '';
$part       = $_REQUEST['part']    ?? 'links';
$entries    = $_POST['entries']    ?? [];
$headers    = '';
$breadcrumb = [
    __('Plugins')         => '',
    __('My cinecturlink') => dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'links']),
];
if (!in_array($part, ['links', 'link', 'cats', 'cat', 'dellinks', 'updlinksnote', 'updlinkscat'])) {
    $part = 'links';
}
if (!is_array($entries)) {
    $entries == [];
}

try {
    $C2               = new cinecturlink2();
    $categories       = $C2->getCategories();
    $categories_combo = ['-' => ''];
    while ($categories->fetch()) {
        $cat_title                    = html::escapeHTML($categories->cat_title);
        $categories_combo[$cat_title] = $categories->cat_id;
    }
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

if ($part == 'dellinks') {
    try {
        // delete group of links
        if (!empty($entries)) {
            foreach ($entries as $id) {
                $C2->delLink($id);
            }
            dcAdminNotices::addSuccessNotice(
                __('Links successfully deleted.')
            );
            dcCore::app()->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'links']);
        }
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
    $breadcrumb[__('Delete links')] = '';
}

// get list of secleted links
if (in_array($part, ['updlinksnote', 'updlinkscat'])) {
    try {
        $links = $C2->getLinks(['link_id' => $entries]);
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

if ($part == 'updlinksnote') {
    try {
        // update group of links note
        if (!empty($entries) && isset($_POST['newlinknote'])) {
            while ($links->fetch()) {
                if (in_array($links->link_id, $entries)) {
                    $cur            = dcCore::app()->con->openCursor($C2->table);
                    $cur->link_note = (int) $_POST['newlinknote'];
                    $C2->updLink($links->link_id, $cur);
                }
            }
            dcAdminNotices::addSuccessNotice(
                __('Links successfully updated.')
            );
            if (!empty($_POST['redir'])) {
                http::redirect($redir);
            } else {
                dcCore::app()->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'links']);
            }
        }
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
    $breadcrumb[__('Update links rating')] = '';
}

if ($part == 'updlinkscat') {
    try {
        // update group of links category
        if (!empty($entries) && !empty($_POST['newcatid'])) {
            while ($links->fetch()) {
                if (in_array($links->link_id, $entries)) {
                    $cur         = dcCore::app()->con->openCursor($C2->table);
                    $cur->cat_id = (int) $_POST['newcatid'];
                    $C2->updLink($links->link_id, $cur);
                }
            }
            dcAdminNotices::addSuccessNotice(
                __('Links successfully updated.')
            );
            if (!empty($_POST['redir'])) {
                http::redirect($redir);
            } else {
                dcCore::app()->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'links']);
            }
        }
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
    $breadcrumb[__('Update links category')] = '';
}

if ($part == 'links') {
    $action_combo = [
        __('Delete')          => 'dellinks',
        __('Change category') => 'updlinkscat',
        __('Change rating')   => 'updlinksnote',
    ];

    $c2link_filter = new adminGenericFilterV2('c2link');
    $c2link_filter->add('part', 'links');
    $c2link_filter->add(dcAdminFilters::getPageFilter());
    $c2link_filter->add(dcAdminFilters::getSearchFilter());
    $c2link_filter->add(dcAdminFilters::getSelectFilter(
        'catid',
        __('Category:'),
        $categories_combo,
        'cat_id'
    ));

    $params               = $c2link_filter->params();
    $params['link_type']  = 'cinecturlink';
    $params['no_content'] = true;

    $links_list = null;

    try {
        $links         = $C2->getLinks($params);
        $links_counter = $C2->getLinks($params, true)->f(0);
        $links_list    = new adminlistCinecturlink2($links, $links_counter);
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }

    $breadcrumb[__('My cinecturlink')] = '';
    $headers .= dcPage::jsVars(['dotclear.filter_reset_url' => dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'links'])]) .
        dcPage::jsFilterControl($c2link_filter->show()) .
        dcPage::jsLoad(dcPage::getPF('cinecturlink2/js/c2links.js'));
}

if ($part == 'link') {
    $langs_combo = l10n::getISOcodes(true);
    $notes_combo = range(0, 20);
    $media_combo = $tmp_media_combo = $dir = null;

    try {
        $allowed_media       = ['png', 'jpg', 'gif', 'bmp', 'jpeg'];
        dcCore::app()->media = new dcMedia();
        dcCore::app()->media->chdir(dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_folder);
        dcCore::app()->media->getDir();
        $dir = & dcCore::app()->media->dir;

        foreach ($dir['files'] as $file) {
            if (!in_array(files::getExtension($file->relname), $allowed_media)) {
                continue;
            }
            $tmp_media_combo[$file->media_title] = $file->file_url;
        }
        if (!empty($tmp_media_combo)) {
            $media_combo = array_merge(['-' => ''], $tmp_media_combo);
        }
    } catch (Exception $e) {
        //dcCore::app()->error->add($e->getMessage());
    }

    if (!empty($_POST['save'])) {
        try {
            cinecturlink2::makePublicDir(
                DC_ROOT . '/' . dcCore::app()->blog->settings->system->public_path,
                dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_folder
            );
            if (empty($linktitle)) {
                throw new Exception(__('You must provide a title.'));
            }
            if (empty($linkauthor)) {
                throw new Exception(__('You must provide an author.'));
            }
            if (!preg_match('/https?:\/\/.+/', $linkimage)) {
                throw new Exception(__('You must provide a link to an image.'));
            }

            $cur              = dcCore::app()->con->openCursor($C2->table);
            $cur->link_title  = $linktitle;
            $cur->link_desc   = $linkdesc;
            $cur->link_author = $linkauthor;
            $cur->link_url    = $linkurl;
            $cur->cat_id      = $linkcat == '' ? null : $linkcat;
            $cur->link_lang   = $linklang;
            $cur->link_img    = $linkimage;
            $cur->link_note   = $linknote;

            // create a link
            if (empty($linkid)) {
                $exists = $C2->getLinks(['link_title' => $linktitle], true)->f(0);
                if ($exists) {
                    throw new Exception(__('Link with same name already exists.'));
                }
                $linkid = $C2->addLink($cur);

                dcAdminNotices::addSuccessNotice(
                    __('Link successfully created.')
                );
            // update a link
            } else {
                $exists = $C2->getLinks(['link_id' => $linkid], true)->f(0);
                if (!$exists) {
                    throw new Exception(__('Unknown link.'));
                }
                $C2->updLink($linkid, $cur);

                dcAdminNotices::addSuccessNotice(
                    __('Link successfully updated.')
                );
            }
            dcCore::app()->adminurl->redirect(
                'admin.plugin.cinecturlink2',
                [
                    'part'   => 'link',
                    'linkid' => $linkid,
                    'redir'  => $redir,
                ]
            );
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }
    }

    if (!empty($_POST['delete']) && !empty($linkid)) {
        try {
            $C2->delLink($linkid);

            dcAdminNotices::addSuccessNotice(
                __('Link successfully deleted.')
            );
            if (!empty($_POST['redir'])) {
                http::redirect($redir);
            } else {
                dcCore::app()->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'links']);
            }
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }
    }

    if (!empty($linkid)) {
        $link = $C2->getLinks(['link_id' => $linkid]);
        if (!$link->isEmpty()) {
            $linktitle  = $link->link_title;
            $linkdesc   = $link->link_desc;
            $linkauthor = $link->link_author;
            $linkurl    = $link->link_url;
            $linkcat    = $link->cat_id;
            $linklang   = $link->link_lang;
            $linkimage  = $link->link_img;
            $linknote   = $link->link_note;
        }
    }
    $breadcrumb[(empty($linkid) ? __('New link') : __('Edit link'))] = '';
    $headers .= dcPage::jsVars(['dotclear.c2_lang' => dcCore::app()->auth->getInfo('user_lang')]) .
        dcPage::jsLoad(dcPage::getPF('cinecturlink2/js/c2link.js'));
}

if ($part == 'cats') {
    try {
        // reorder categories
        if (!empty($_POST['save'])) {
            $catorder = [];
            if (empty($_POST['im_order']) && !empty($_POST['order'])) {
                $catorder = $_POST['order'];
                asort($catorder);
                $catorder = array_keys($catorder);
            } elseif (!empty($_POST['im_order'])) {
                $catorder = $_POST['im_order'];
                if (substr($catorder, -1) == ',') {
                    $catorder = substr($catorder, 0, strlen($catorder) - 1);
                }
                $catorder = explode(',', $catorder);
            }
            $i = 0;
            foreach ($catorder as $id) {
                $i++;
                $cur          = dcCore::app()->con->openCursor($C2->table . '_cat');
                $cur->cat_pos = $i;
                $C2->updCategory($id, $cur);
            }
            dcAdminNotices::addSuccessNotice(
                __('Categories successfully reordered.')
            );
            dcCore::app()->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }
        // delete categories
        if (!empty($_POST['delete']) && !empty($_POST['items_selected'])) {
            foreach ($_POST['items_selected'] as $id) {
                $C2->delCategory($id);
            }
            dcAdminNotices::addSuccessNotice(
                __('Categories successfully deleted.')
            );
            dcCore::app()->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }

    $breadcrumb[__('Categories')] = '';

    dcCore::app()->auth->user_prefs->addWorkspace('accessibility');
    if (!dcCore::app()->auth->user_prefs->accessibility->nodragdrop) {
        $headers .= dcPage::jsLoad('js/jquery/jquery-ui.custom.js') .
            dcPage::jsLoad('js/jquery/jquery.ui.touch-punch.js') .
            dcPage::jsLoad(dcPage::getPF('cinecturlink2/js/c2cats.js'));
    }
}

if ($part == 'cat') {
    try {
        // create category
        if (!empty($_POST['save']) && empty($catid) && !empty($cattitle) && !empty($catdesc)) {
            $exists = $C2->getCategories(['cat_title' => $cattitle], true)->f(0);
            if ($exists) {
                throw new Exception(__('Category with same name already exists.'));
            }
            $cur            = dcCore::app()->con->openCursor($C2->table . '_cat');
            $cur->cat_title = $cattitle;
            $cur->cat_desc  = $catdesc;

            $catid = $C2->addCategory($cur);

            dcAdminNotices::addSuccessNotice(
                __('Category successfully created.')
            );
            dcCore::app()->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }
        // update category
        if (!empty($_POST['save']) && !empty($catid) && !empty($cattitle) && !empty($catdesc)) {
            $exists = $C2->getCategories(['cat_title' => $cattitle, 'exclude_cat_id' => $catid], true)->f(0);
            if ($exists) {
                throw new Exception(__('Category with same name already exists.'));
            }
            $cur            = dcCore::app()->con->openCursor($C2->table . '_cat');
            $cur->cat_title = $cattitle;
            $cur->cat_desc  = $catdesc;

            $C2->updCategory($catid, $cur);

            dcAdminNotices::addSuccessNotice(
                __('Category successfully updated.')
            );
            dcCore::app()->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }
        // delete category
        if (!empty($_POST['delete']) && !empty($catid)) {
            $C2->delCategory($catid);

            dcAdminNotices::addSuccessNotice(
                __('Category successfully deleted.')
            );
            dcCore::app()->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
    $breadcrumb[__('Categories')]                                           = dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cats']);
    $breadcrumb[(empty($catid) ? __('New category') : __('Edit category'))] = '';
}

echo
'<html><head><title>' . __('Cinecturlink 2') . '</title>' .
$headers .
'</head><body>' .
dcPage::breadcrumb($breadcrumb) .
dcPage::notices();

if (!empty($redir)) {
    echo '<p><a class="back" href="' . $redir . '">' . __('Back') . ' </a></p>';
}
if (!empty($title)) {
    echo '<h3>' . $title . '</h3>';
}

if ($part == 'updlinksnote') {
    if ($links->isEmpty()) {
        echo '<p>' . __('There is no link') . '</p>';
    } else {
        echo '<h4>' . __('Links') . '</h4><ul>';
        while ($links->fetch()) {
            echo '<li><strong>' . $links->link_title . '</strong> - ' . $links->link_note . '/20</li>';
        }
        echo '</ul>';

        echo '<h4>' . __('Rating') . '</h4>
        <form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.cinecturlink2') . '">' .
        '<p><label for="newlinknote" class="ib">' . __('New rating:') . '</label> ' .
            form::number('newlinknote', [
                'min'     => 0,
                'max'     => 20,
                'default' => 10,
            ]) . '/20' . '</p>' .
        '<p>' .
        '<input type="submit" value="' . __('Save') . ' (s)" accesskey="s" name="save" /> ' .
        '<a id="post-cancel" href="' . (
            $redir ? $redir :
            dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'links'])
        ) . '" class="button" accesskey="c">' . __('Cancel') . ' (c)</a> ';
        foreach ($entries as $id) {
            echo form::hidden(['entries[]'], $id);
        }
        echo
        form::hidden('part', 'updlinksnote') .
        form::hidden('redir', $redir) .
        dcCore::app()->formNonce() . '</p>' .
        '</form>';
    }
}

if ($part == 'updlinkscat') {
    if ($links->isEmpty()) {
        echo '<p>' . __('There is no link') . '</p>';
    } else {
        echo '<h4>' . __('Links') . '</h4><ul>';
        while ($links->fetch()) {
            echo '<li><strong>' . $links->link_title . '</strong> - ' . ($links->cat_title ?? __('no categories')) . '</li>';
        }
        echo '</ul>';

        echo '<h4>' . __('Category') . '</h4>
        <form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.cinecturlink2') . '">' .
        '<p><label for="newcatid" class="ib">' . __('New category:') . '</label> ' .
        form::combo('newcatid', $categories_combo, $catid) . '</p>' .
        '<input type="submit" value="' . __('Save') . ' (s)" accesskey="s" name="save" /> ' .
        '<a id="post-cancel" href="' . (
            $redir ? $redir :
            dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'links'])
        ) . '" class="button" accesskey="c">' . __('Cancel') . ' (c)</a> ';
        foreach ($entries as $id) {
            echo form::hidden(['entries[]'], $id);
        }
        echo
        form::hidden('part', 'updlinkscat') .
        form::hidden('redir', $redir) .
        dcCore::app()->formNonce() . '</p>' .
        '</form>';
    }
}

if ($part == 'links') {
    $links_redir = dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', $c2link_filter->values());

    echo
    '<p class="top-add"><a class="button add" href="' .
        dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'link', 'redir' => $links_redir]) .
    '">' . __('New Link') . '</a> <a class="button add" href="' .
        dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cats', 'redir' => $links_redir]) .
    '">' . __('Edit categories') . ' </a></p>';

    if ($links->isEmpty() && !$c2link_filter->show()) {
        echo '<p>' . __('There is no link') . '</p>';
    } else {
        $c2link_filter->display(
            'admin.plugin.cinecturlink2',
            form::hidden('p', 'cinecturlink2') . form::hidden('part', 'links')
        );

        $links_list->display(
            $c2link_filter->page,
            $c2link_filter->nb,
            '<form action="' . dcCore::app()->adminurl->get('admin.plugin.cinecturlink2') . '" method="post" id="form-entries">' .

            '%s' .

            '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .

            '<p class="col right"><label for="action" class="classic">' . __('Selected links action:') . '</label> ' .
            form::combo('part', $action_combo) .
            '<input id="do-action" type="submit" value="' . __('ok') . '" disabled /></p>' .
            dcCore::app()->adminurl->getHiddenFormFields('admin.plugin.cinecturlink2', array_diff_key($c2link_filter->values(), ['part' => ''])) .
            form::hidden(['redir'], $links_redir) .
            dcCore::app()->formNonce() .
            '</div>' .
            '</form>',
            $c2link_filter->show(),
            $links_redir
        );
    }
}

if ($part == 'link') {
    echo '
    <form id="newlinkform" method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.cinecturlink2') . '">

    <div class="two-cols clearfix">
    <div class="col70">
    <p><label for="linktitle">' . __('Title:') . ' ' .
    form::field('linktitle', 60, 255, html::escapeHTML($linktitle), 'maximal') .
    '</label></p>
    <p><label for="linkdesc">' . __('Description:') . ' ' .
    form::field('linkdesc', 60, 255, html::escapeHTML($linkdesc), 'maximal') .
    '</label></p>
    <p><label for="linkauthor">' . __('Author:') . ' ' .
    form::field('linkauthor', 60, 255, html::escapeHTML($linkauthor), 'maximal') .
    '</label></p>
    <p><label for="linkurl">' . __('Details URL:') . ' ' .
    form::field('linkurl', 60, 255, html::escapeHTML($linkurl), 'maximal') . '</label>' .
    '<a class="modal hidden-if-no-js" href="http://google.com" id="newlinksearch">' .
    __('Search with Google') . '</a>' .
    '</p>
    <p><label for="linkimage">' . __('Image URL:') . ' ' .
    form::field('linkimage', 60, 255, html::escapeHTML($linkimage), 'maximal') . '</label>' .
    '<a class="modal hidden-if-no-js"" href="http://amazon.com" id="newimagesearch">' .
    __('Search with Amazon') . '</a>' .
    '</p>';

    if (empty($media_combo)) {
        echo
        '<p class="form-note">' . __('There is no image in cinecturlink media path.') . '</p>';
    } else {
        echo '
        <p><label for="newimageselect">' . __('or select from repository:') . ' ' .
        form::combo('newimageselect', $media_combo, '', 'maximal') .
        '</label></p>' .
        '<p class="form-note"><a href="' .
            dcCore::app()->adminurl->get('admin.media', ['d' => (string) dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_folder]) . '">' .
            __('Go to media manager to add image to cinecturlink path.') .
        '<a></p>';
    }

    echo '
    </div>
    <div class="col30">
    <p><label for="linkcat">' . __('Category:') . '</label> ' .
    form::combo('linkcat', $categories_combo, $linkcat) .
    '</p>
    <p><label for="linklang">' . __('Lang:') . '</label> ' .
    form::combo('linklang', $langs_combo, $linklang) .
    '</p>
    <p><label for="linknote">' . __('Rating:') . '</label> ' .
        form::number('linknote', [
            'min'     => 0,
            'max'     => 20,
            'default' => $linknote,
        ]) . '/20' . '</p>
    </div></div>

    <p class="border-top">' .
    '<input type="submit" value="' . __('Save') . ' (s)" accesskey="s" name="save" /> ' .
    '<a id="post-cancel" href="' .
        dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'links']) .
    '" class="button" accesskey="c">' . __('Cancel') . ' (c)</a> ' .
    '<input type="submit" class="delete" value="' . __('Delete') . '" name="delete" />' .
    form::hidden('linkid', $linkid) .
    form::hidden('part', 'link') .
    form::hidden('redir', $redir) .
    dcCore::app()->formNonce() . '
    </p>
    </form>';
}

if ($part == 'cats') {
    echo
    '<p class="top-add"><a class="button add" href="' .
        dcCore::app()->adminurl->get(
            'admin.plugin.cinecturlink2',
            [
                'part'  => 'cat',
                'redir' => dcCore::app()->adminurl->get(
                    'admin.plugin.cinecturlink2',
                    [
                        'part'  => 'cats',
                        'redir' => $redir,
                    ]
                ),
            ]
        ) .
    '">' . __('New Category') . ' </a></p>';

    if ($categories->isEmpty()) {
        echo '<p>' . __('There is no category') . '</p>';
    } else {
        echo '
        <form id="c2items" method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.cinecturlink2') . '">
        <div class="table-outer">
        <table class="dragable">
        <caption>' . __('Categories list') . '</caption>
        <thead><tr>
        <th colspan="3" scope="col">' . __('name') . '</th>
        <th scope="col">' . __('description') . '</th>
        </tr></thead>
        <tbody  id="c2itemslist">';

        $i = 0;
        while ($categories->fetch()) {
            $id = $categories->cat_id;

            echo
            '<tr class="line" id="l_' . $i . '">' .
            '<td class="handle minimal">' .
            form::number(['order[' . $id . ']'], [
                'min'        => 1,
                'max'        => $categories->count(),
                'default'    => $i + 1,
                'class'      => 'position',
                'extra_html' => 'title="' . sprintf(__('position of %s'), html::escapeHTML($categories->cat_title)) . '"',
            ]) .
            form::hidden(['dynorder[]', 'dynorder-' . $i], $id) . '</td>
            <td class="minimal">' . form::checkbox(['items_selected[]', 'ims-' . $i], $id) . '</td>
            <td class="nowrap"><a title="' . __('Edit') . '" href="' .
                dcCore::app()->adminurl->get(
                    'admin.plugin.cinecturlink2',
                    [
                        'part'  => 'cat',
                        'catid' => $id,
                        'redir' => dcCore::app()->adminurl->get(
                            'admin.plugin.cinecturlink2',
                            [
                                'part'  => 'cats',
                                'redir' => $redir,
                            ]
                        ),
                    ]
                ) .
            '">' . html::escapeHTML($categories->cat_title) . '</a></td>
            <td class="maximal">' . html::escapeHTML($categories->cat_desc) . '</td>
            </tr>';
            $i++;
        }

        echo '
        </tbody>
        </table>
        </div>
        <p class="form-note">' . __('Check to delete') . '</p>
        <p class="border-top">' .
        '<input type="submit" value="' . __('Save order') . ' (s)" accesskey="s" name="save" /> ' .
        '<a id="post-cancel" href="' .
            dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cats']) .
        '" class="button" accesskey="c">' . __('Cancel') . ' (c)</a> ' .
        '<input type="submit" class="delete" value="' . __('Delete selection') . '" name="delete" />' .
        form::hidden('im_order', '') .
        form::hidden('part', 'cats') .
        dcCore::app()->formNonce() . '</p>' .
        '</form>';
    }
}

if ($part == 'cat') {
    if (!empty($catid)) {
        $category = $C2->getCategories(['cat_id' => $catid]);
        if (!$category->isEmpty()) {
            $cattitle = $category->cat_title;
            $catdesc  = $category->cat_desc;
        }
    }

    if ($catid) {
        $links = $C2->getLinks(['cat_id' => $catid], true)->f(0);
        echo '<p class="info">' . (
            empty($links) ?
            __('No link uses this category.') :
            sprintf(__('A link uses this category.', '%s links use this category.', $links), $links)
        ) . '</p>';
    }
    echo '
    <form method="post" action="' . dcCore::app()->adminurl->get('admin.plugin.cinecturlink2') . '">
    <p><label for="cattitle">' . __('Title:') . ' ' .
    form::field('cattitle', 60, 64, $cattitle, 'maximal') .
    '</label></p>
    <p><label for="catdesc">' . __('Description:') . ' ' .
    form::field('catdesc', 60, 64, $catdesc, 'maximal') .
    '</label></p>
    <p class="border-top">' .
    '<input type="submit" value="' . __('Save') . ' (s)" accesskey="s" name="save" /> ' .
    '<a id="post-cancel" href="' .
        dcCore::app()->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cats']) .
    '" class="button" accesskey="c">' . __('Cancel') . ' (c)</a> ' .
    (!empty($catid) ? ' <input type="submit" class="delete" value="' . __('Delete') . '" name="delete" />' : '') .
    form::hidden('catid', $catid) .
    form::hidden('part', 'cat') .
    dcCore::app()->formNonce() . '</p>' .
    '</form>';
}

dcPage::helpBlock('cinecturlink2');

echo '</body></html>';
