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

dcPage::check('contentadmin');

$C2 = new cinecturlink2($core);

$catid = $_REQUEST['catid'] ?? '';
$cattitle = $_POST['cattitle'] ?? '';
$catdesc = $_POST['catdesc'] ?? '';
$part = $_REQUEST['part'] ?? '';
if (!in_array($part, ['links', 'link', 'cats', 'cat'])) {
    $part = 'links';
}
$headers = '';

$categories = $C2->getCategories();

$breadcrumb = [
    html::escapeHTML($core->blog->name) => '',
    __('My cinecturlink') => $part != 'links' ? $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'links']) : ''
];

if ($part == 'link') {
    $breadcrumb[__('Link')] = '';
}

if ($part == 'cats') {
    $breadcrumb[__('Categories')] = '';

    $core->auth->user_prefs->addWorkspace('accessibility');
    if (!$core->auth->user_prefs->accessibility->nodragdrop) {
        $headers .=
            dcPage::jsLoad('js/jquery/jquery-ui.custom.js') .
            dcPage::jsLoad('js/jquery/jquery.ui.touch-punch.js') .
            dcPage::jsLoad(dcPage::getPF('cinecturlink2/js/cinecturlink2.js'));
    }

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
            foreach($catorder as $id) {
                $i++;
                $cur = $core->con->openCursor($C2->table . '_cat');
                $cur->cat_pos = $i;
                $C2->updCategory($id, $cur);
            }
            dcPage::addSuccessNotice(
                __('Categories successfully reordered.')
            );
            $core->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }
        // delete categories
        if (!empty($_POST['delete']) && !empty($_POST['items_selected'])) {
            foreach ($_POST['items_selected'] as $id) {
                $C2->delCategory($id);
            }
            dcPage::addSuccessNotice(
                __('Category successfully deleted.')
            );
            $core->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }

    } catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}

if ($part == 'cat') {
    $breadcrumb[__('Categories')] = $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cats']);
    $breadcrumb[__('Category')] = '';

    try {
        // create category
        if (!empty($_POST['save']) && empty($catid) && !empty($cattitle) && !empty($catdesc)) {
            $exists = $C2->getCategories(['cat_title' => $cattitle], true)->f(0);
            if ($exists) {
                throw new Exception(__('Category with same name already exists.'));
            }
            $cur = $core->con->openCursor($C2->table . '_cat');
            $cur->cat_title = $cattitle;
            $cur->cat_desc = $catdesc;

            $catid = $C2->addCategory($cur);

            dcPage::addSuccessNotice(
                __('Category successfully created.')
            );
            $core->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }
        // update category
        if (!empty($_POST['save']) && !empty($catid) && !empty($cattitle) && !empty($catdesc)) {
            $exists = $C2->getCategories(['cat_title' => $cattitle, 'exclude_cat_id' => $catid], true)->f(0);
            if ($exists) {
                throw new Exception(__('Category with same name already exists.'));
            }
            $cur = $core->con->openCursor($C2->table . '_cat');
            $cur->cat_title = $cattitle;
            $cur->cat_desc = $catdesc;

            $C2->updCategory($catid, $cur);

            dcPage::addSuccessNotice(
                __('Category successfully updated.')
            );
            $core->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }
        // delete category
        if (!empty($_POST['delete']) && !empty($catid)) {
            $C2->delCategory($catid);

            dcPage::addSuccessNotice(
                __('Category successfully deleted.')
            );
            $core->adminurl->redirect('admin.plugin.cinecturlink2', ['part' => 'cats']);
        }
    } catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}


echo 
'<html><head><title>'.__('Cinecturlink 2').'</title>' .
$headers .
'</head><body>'.
dcPage::breadcrumb($breadcrumb) .
dcPage::notices();

if ($part == "links") {
    echo 
    '<p><a href="' . 
        $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cats']) . 
    '">' . __('Edit categories') .' </a></p>' .
    '<p class="top-add"><a class="button add" href="' . 
        $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'link']) . 
    '">' . __('New Link') .' </a></p>';

}

if ($part == "link") {

}

if ($part == "cats") {
    echo 
    '<p class="top-add"><a class="button add" href="' . 
        $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cat']) . 
    '">' . __('New Category') .' </a></p>';

    if ($categories->isEmpty()) {
        echo '<p>'.__('There is no category').'</p>';
    }
    else {
        echo '
        <form id="c2items" method="post" action="' . 
            $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cats']) . '">
        <div class="table-outer">
        <table class="dragable">
        <caption>' . __('Categories list') . '</caption>
        <thead><tr>
        <th colspan="3" scope="col">'.__('name').'</th>
        <th scope="col">'.__('description').'</th>
        </tr></thead>
        <tbody  id="c2itemslist">';

        $i = 0;
        while($categories->fetch()) {
            $id = $categories->cat_id;

            echo 
            '<tr class="line" id="l_' . $i . '">' .
            '<td class="handle minimal">' .
            form::number(['order[' . $id . ']'], [
                'min'        => 1,
                'max'        => $categories->count(),
                'default'    => $i +1,
                'class'      => 'position',
                'extra_html' => 'title="' . sprintf(__('position of %s'), html::escapeHTML($categories->cat_title)) . '"'
            ]) .
            form::hidden(['dynorder[]', 'dynorder-' . $i], $id) . '</td>
            <td class="minimal">' . form::checkbox(['items_selected[]', 'ims-' . $i], $id) . '</td>
            <td class="nowrap"><a title="' . __('Edit') .'" href="' . 
                $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cat', 'catid' => $id]) . 
            '">' . html::escapeHTML($categories->cat_title) . '</a></td>
            <td class="maximal">' . html::escapeHTML($categories->cat_desc) . '</td>
            </tr>';
            $i++;
        }

        echo '
        </tbody>
        </table>
        </div>
        <p class="form-note">'.__('Check to delete').'</p>
        <p class="border-top">' .
        '<input type="submit" value="' . __('Save order') . ' (s)" accesskey="s" name="save" /> ' .
        '<a id="post-cancel" href="' . 
            $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cats']) . 
        '" class="button" accesskey="c">' . __('Cancel') . ' (c)</a> '.
        '<input type="submit" class="delete" value="' . __('Delete selection') . '" name="delete" />' .
        form::hidden('im_order', '') .
        $core->formNonce() . '</p>' .
        '</form>';
    }
}

if ($part == 'cat') {
    if (!empty($catid)) {
        $category = $C2->getCategories(['cat_id' => $catid]);
        if (!$category->isEmpty()) {
            $cattitle = $category->cat_title;
            $catdesc = $category->cat_desc;
        }
    }
    echo '
    <h3>' . (empty($catid) ? __('Add categorie') : __('Edit categorie')) . '</h3>
    <form method="post" action="'. $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cat']) .'">
    <p><label for="cattitle">' . __('Title:') . ' ' .
    form::field('cattitle', 60, 64, $cattitle, 'maximal') .
    '</label></p>
    <p><label for="catdesc">' . __('Description:') . ' ' .
    form::field('catdesc', 60, 64, $catdesc, 'maximal') .
    '</label></p>
    <p class="border-top">' .
    '<input type="submit" value="' . __('Save') . ' (s)" accesskey="s" name="save" /> ' .
    '<a id="post-cancel" href="' . 
        $core->adminurl->get('admin.plugin.cinecturlink2', ['part' => 'cats']) . 
    '" class="button" accesskey="c">' . __('Cancel') . ' (c)</a> '.
    (!empty($catid) ? ' <input type="submit" class="delete" value="' . __('Delete') . '" name="delete" />' : '') .
    form::hidden('catid', $catid) .
    $core->formNonce() . '</p>' .
    '</form>';
}

dcPage::helpBlock('cinecturlink2');

echo '</body></html>';