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
if (!defined('DC_CONTEXT_MODULE')) {
    return null;
}

$redir = empty($_REQUEST['redir']) ?
    $list->getURL() . '#plugins' : $_REQUEST['redir'];

$core->blog->settings->addNamespace('cinecturlink2');
$s                                = $core->blog->settings->cinecturlink2;
$cinecturlink2_active             = (bool) $s->cinecturlink2_active;
$cinecturlink2_widthmax           = abs((int) $s->cinecturlink2_widthmax);
$cinecturlink2_folder             = (string) $s->cinecturlink2_folder;
$cinecturlink2_triggeronrandom    = (bool) $s->cinecturlink2_triggeronrandom;
$cinecturlink2_public_active      = (bool) $s->cinecturlink2_public_active;
$cinecturlink2_public_title       = (string) $s->cinecturlink2_public_title;
$cinecturlink2_public_description = (string) $s->cinecturlink2_public_description;
$cinecturlink2_public_nbrpp       = (int) $s->cinecturlink2_public_nbrpp;
if ($cinecturlink2_public_nbrpp < 1) {
    $cinecturlink2_public_nbrpp = 10;
}

$combo_dirs = cinecturlink2::getPublicDirs($core);

if (!empty($_POST['save'])) {
    try {
        $cinecturlink2_active   = !empty($_POST['cinecturlink2_active']);
        $cinecturlink2_widthmax = abs((int) $_POST['cinecturlink2_widthmax']);
        $cinecturlink2_newdir   = (string) files::tidyFileName($_POST['cinecturlink2_newdir']);
        $cinecturlink2_folder   = empty($cinecturlink2_newdir) ?
            (string) files::tidyFileName($_POST['cinecturlink2_folder']) :
            $cinecturlink2_newdir;
        $cinecturlink2_triggeronrandom    = !empty($_POST['cinecturlink2_triggeronrandom']);
        $cinecturlink2_public_active      = !empty($_POST['cinecturlink2_public_active']);
        $cinecturlink2_public_title       = (string) $_POST['cinecturlink2_public_title'];
        $cinecturlink2_public_description = (string) $_POST['cinecturlink2_public_description'];
        $cinecturlink2_public_nbrpp       = (int) $_POST['cinecturlink2_public_nbrpp'];
        if ($cinecturlink2_public_nbrpp < 1) {
            $cinecturlink2_public_nbrpp = 10;
        }
        if (empty($cinecturlink2_folder)) {
            throw new Exception(__('You must provide a specific folder for images.'));
        }
        cinecturlink2::makePublicDir(
            DC_ROOT . '/' . $core->blog->settings->system->public_path,
            $cinecturlink2_folder,
            true
        );
        $s->put('cinecturlink2_active', $cinecturlink2_active);
        $s->put('cinecturlink2_public_active', $cinecturlink2_public_active);
        $s->put('cinecturlink2_public_title', $cinecturlink2_public_title);
        $s->put('cinecturlink2_public_description', $cinecturlink2_public_description);
        $s->put('cinecturlink2_public_nbrpp', $cinecturlink2_public_nbrpp);
        $s->put('cinecturlink2_widthmax', $cinecturlink2_widthmax);
        $s->put('cinecturlink2_folder', $cinecturlink2_folder);
        $s->put('cinecturlink2_triggeronrandom', $cinecturlink2_triggeronrandom);

        dcPage::addSuccessNotice(
            __('Configuration successfully updated.')
        );
        $core->adminurl->redirect(
            'admin.plugins',
            ['module' => 'cinecturlink2', 'conf' => 1, 'redir' => $list->getRedir()]
        );
    } catch (Exception $e) {
        $core->error->add($e->getMessage());
    }
}

echo '
<div class="fieldset">
<h4>' . __('General') . '</h4>

<p><label class="classic" for="cinecturlink2_active">' .
form::checkbox('cinecturlink2_active', 1, $cinecturlink2_active) .
__('Enable plugin') . '</label></p>

<p><label for="cinecturlink2_folder">' . __('Public folder of images (under public folder of blog):') . '</label>' .
form::combo('cinecturlink2_folder', $combo_dirs, $cinecturlink2_folder) . '</p>

<p><label for="cinecturlink2_newdir">' . __('Or create a new public folder of images:') . '</label>' .
form::field('cinecturlink2_newdir', 60, 64, '', 'maximal') . '</p>

<p><label for="cinecturlink2_widthmax">' . __('Maximum width of images (in pixel):') . '</label>' .
form::number('cinecturlink2_widthmax', 10, 512, $cinecturlink2_widthmax) . '</p>

</div>

<div class="fieldset">
<h4>' . __('Widget') . '</h4>

<p><label class="classic" for="cinecturlink2_triggeronrandom">' .
form::checkbox('cinecturlink2_triggeronrandom', 1, $cinecturlink2_triggeronrandom) .
__('Update cache when use "Random" or "Number of view" order on widget (Need reload of widgets on change)') . '</label></p>
<p class="form-note">' . __('This increases the random effect, but updates the cache of the blog whenever the widget is displayed, which reduces the perfomances of your blog.') . '</p>

</div>

<div class="fieldset">
<h4>' . __('Public page') . '</h4>

<p><label class="classic" for="cinecturlink2_public_active">' .
form::checkbox('cinecturlink2_public_active', 1, $cinecturlink2_public_active) .
__('Enable public page') . '</label></p>
<p class="form-note">' . sprintf(__('Public page has url: %s'), '<a href="' . $core->blog->url . $core->url->getBase('cinecturlink2') . '" title="public page">' . $core->blog->url . $core->url->getBase('cinecturlink2') . '</a>') . '</p>

<p><label for="cinecturlink2_public_title">' . __('Title of the public page:') . '</label>' .
form::field('cinecturlink2_public_title', 60, 255, $cinecturlink2_public_title, 'maximal') . '</p>

<p><label for="cinecturlink2_public_description">' . __('Description of the public page:') . '</label>' .
form::field('cinecturlink2_public_description', 60, 255, $cinecturlink2_public_description, 'maximal') . '</p>

<p><label for="cinecturlink2_public_nbrpp">' . __('Limit number of entries per page on pulic page to:') . '</label>' .
form::number('cinecturlink2_public_nbrpp', 1, 100, $cinecturlink2_public_nbrpp) . '</p>

</div>

<div class="fieldset">
<h4>' . __('Informations') . '</h4>

<ul>
<li>' . __('Once the extension has been configured and your links have been created, you can place one of the cinecturlink widgets in the sidebar.') . '</li>
<li>' . sprintf(__('In order to open links in new window you can use plugin %s.'), '<a href="http://plugins.dotaddict.org/dc2/details/externalLinks">External Links</a>') . '</li>
<li>' . sprintf(__('In order to change URL of public page you can use plugin %s.'), '<a href="http://lab.dotclear.org/wiki/plugin/myUrlHandlers">My URL handlers</a>') . '</li>
<li>' . sprintf(__('You can add public pages of cinecturlink to the plugin %s.'), '<a href="http://plugins.dotaddict.org/dc2/details/sitemaps">sitemaps</a>') . '</li>
<li>' . sprintf(__('The plugin Cinecturlink2 is compatible with plugin %s.'), '<a href="http://plugins.dotaddict.org/dc2/details/rateIt">Rate it</a>') . '</li>
<li>' . sprintf(__('The plugin Cinecturlink2 is compatible with plugin %s.'), '<a href="http://plugins.dotaddict.org/dc2/details/activityReport">Activity report</a>') . '</li>
</ul>

</div>';
