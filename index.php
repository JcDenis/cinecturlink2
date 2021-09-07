<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of cinecturlink2, a plugin for Dotclear 2.
# 
# Copyright (c) 2009-2013 Jean-Christian Denis and contributors
# contact@jcdenis.fr http://jcd.lv
# 
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) {

	return null;
}

dcPage::check('contentadmin');

# Class
$C2 = new cinecturlink2($core);
require dirname(__FILE__).'/inc/lib.cinecturlink2.list.php';

# Queries
$upd_link_id	= isset($_REQUEST['link_id']) ? $_REQUEST['link_id'] : null;
$new_title	= isset($_POST['new_title']) ? $_POST['new_title'] : '';
$new_desc		= isset($_POST['new_desc']) ? $_POST['new_desc'] : '';
$new_author	= isset($_POST['new_author']) ? $_POST['new_author'] : '';
$new_url		= isset($_POST['new_url']) ? $_POST['new_url'] : '';
$new_category	= isset($_POST['new_category']) ? $_POST['new_category'] : '';
$new_lang		= isset($_POST['new_lang']) ? $_POST['new_lang'] : $core->auth->getInfo('user_lang');
$new_image	= isset($_POST['new_image']) ? $_POST['new_image'] : '';
$new_note		= isset($_POST['new_note']) ? $_POST['new_note'] : 10;
$new_cattitle	= isset($_POST['new_cattitle']) ? $_POST['new_cattitle'] : '';
$new_catdesc	= isset($_POST['new_catdesc']) ? $_POST['new_catdesc'] : '';
$action		= isset($_POST['action']) ? $_POST['action'] : '';

# Actions
try {

	# Update categories
	if ($action == 'update_categories') {

		# Reorder categories
		if (empty($_POST['cats_order']) 
		 && !empty($_POST['catpos'])
		) {
			$order = $_POST['catpos'];
			asort($order);
			$order = array_keys($order);
		}
		elseif (!empty($_POST['cats_order'])) {
			$order = explode(',', $_POST['cats_order']);
		}

		if (!empty($order)) {

			foreach ($order as $pos => $id) {

				$pos = ((integer) $pos)+1;
				if (empty($pos) || empty($id)) {
					continue;
				}

				$cur = $core->con->openCursor($C2->table.'_cat');
				$cur->cat_pos = $pos;
				$C2->updCategory($id, $cur);
			}

			dcPage::addSuccessNotice(
				__('Categories successfully reordered.')
			);
		}

		# Delete categories
		if (!empty($_POST['delcat'])) {

			foreach($_POST['delcat'] as $cat_id) {
				$C2->delCategory($cat_id);
			}

			dcPage::addSuccessNotice(
				__('Categories successfully deleted.')
			);
		}

		http::redirect(empty($_POST['redir']) ? 
			$p_url.'#cats' : $_POST['redir']
		);
	}

	# Create new category
	if ($action == 'create_category') {

		if (empty($new_cattitle)) {
			throw new Exception(__('You must provide a title.'));
		}

		$exists = $C2->getCategories(array('cat_title' => $new_cattitle), true)->f(0);
		if ($exists) {
			throw new Exception(__('Category with same name already exists.'));
		}

		$cur = $core->con->openCursor($C2->table.'_cat');
		$cur->cat_title = $new_cattitle;
		$cur->cat_desc = $new_catdesc;
		
		$C2->addCategory($cur);

		dcPage::addSuccessNotice(
			__('Category successfully created.')
		);
		http::redirect(empty($_POST['redir']) ? 
			$p_url.'#cats' : $_POST['redir']
		);
	}

	# Delete links
	if (!empty($_POST['links']) && $action == 'delete_links') {

		foreach($_POST['links'] as $link_id) {
			$C2->delLink($link_id);
		}

		dcPage::addSuccessNotice(
			__('Links successfully deleted.')
		);
		http::redirect(empty($_POST['redir']) ? 
			$p_url.'#links' : $_POST['redir']
		);
	}

	# Move to right tab
	if (!empty($_POST['links']) && $action == 'moveto_change_links_category') {
		// Nothing to do: show links action page
	}

	# Update category for a group of links
	if (!empty($_POST['links']) && $action == 'change_links_category') {

		foreach($_POST['links'] as $link_id) {
			$cur = $core->con->openCursor($C2->table);
			$cur->cat_id = abs((integer) $_POST['upd_category']);
			$C2->updLink($link_id,$cur);
		}

		dcPage::addSuccessNotice(
			__('Links successfully updated.')
		);
		http::redirect(empty($_POST['redir']) ? 
			$p_url.'#links' : $_POST['redir']
		);
	}

	# Move to right tab
	if (!empty($_POST['links']) && $_POST['action'] == 'moveto_change_links_note') {
		// Nothing to do: show links action page
	}

	# Update note for a group of links
	if (!empty($_POST['links']) && $action == 'change_links_note') {

		foreach($_POST['links'] as $link_id) {
			$cur = $core->con->openCursor($C2->table);
			$cur->link_note = abs((integer) $_POST['upd_note']);
			$C2->updLink($link_id,$cur);
		}

		dcPage::addSuccessNotice(
			__('Links successfully updated.')
		);
		http::redirect(empty($_POST['redir']) ? 
			$p_url.'#links' : $_POST['redir']
		);
	}

	# Create or update link
	if (!empty($_POST['newlink'])) {

		cinecturlink2::test_folder(
			DC_ROOT.'/'.$core->blog->settings->system->public_path,
			$core->blog->settings->cinecturlink2->cinecturlink2_folder
		);

		if (empty($new_title)) {
			throw new Exception(__('You must provide a title.'));
		}
		if (empty($new_author)) {
			throw new Exception(__('You must provide an author.'));
		}
		if (!preg_match('/http:\/\/.+/',$new_image)) {
			throw new Exception(__('You must provide a link to an image.'));
		}

		$cur = $core->con->openCursor($C2->table);
		$cur->link_title = $new_title;
		$cur->link_desc = $new_desc;
		$cur->link_author = $new_author;
		$cur->link_url = $new_url;
		$cur->cat_id = $new_category == '' ? null : $new_category;
		$cur->link_lang = $new_lang;
		$cur->link_img = $new_image;
		$cur->link_note = $new_note;

		# Create link
		if (empty($upd_link_id)) {

			$exists = $C2->getLinks(array('link_title'=>$new_title),true)->f(0);
			if ($exists) {
				throw new Exception(__('Link with same name already exists.'));
			}

			$C2->addLink($cur);

			dcPage::addSuccessNotice(
				__('Link successfully created.')
			);
		}

		# Update link
		else {

			$exists = $C2->getLinks(array('link_id'=>$upd_link_id),true)->f(0);
			if (!$exists) {
				throw new Exception(__('Unknown link.'));
			}

			$C2->updLink($upd_link_id,$cur);

			dcPage::addSuccessNotice(
				__('Link successfully updated.')
			);
		}

		http::redirect(empty($_POST['redir']) ? 
			$p_url.'#links' : $_POST['redir']
		);
	}
}
catch(Exception $e) {
	$core->error->add($e->getMessage());
}

# Construct lists
$show_filters = false;
$sortby = !empty($_GET['sortby']) ? $_GET['sortby'] : 'link_upddt';
$order = !empty($_GET['order']) ? $_GET['order'] : 'desc';
$page = !empty($_GET['page']) ? (integer) $_GET['page'] : 1;
$nb_per_page =  30;
if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	if ($nb_per_page != $_GET['nb']) $show_filters = true;
	$nb_per_page = (integer) $_GET['nb'];
}

$sortby_combo = array(
	__('Date')		=> 'link_upddt',
	__('Title')		=> 'link_title',
	__('Category')		=> 'cat_title',
	__('My rating')	=> 'link_note',
);

$order_combo = array(
	__('Descending')	=> 'desc',
	__('Ascending')	=> 'asc'
);

$params = array();
$params['limit'] = array((($page-1)*$nb_per_page),$nb_per_page);

if ($sortby != '' && in_array($sortby,$sortby_combo)) {
	if ($order != '' && in_array($order,$order_combo)) {
		$params['order'] = $sortby.' '.$order;
	}
	if ($sortby != 'link_upddt' || $order != 'desc') {
		$show_filters = true;
	}
}

$categories = $C2->getCategories();

$categories_combo = array('-'=> '');
while($categories->fetch()) {
	$cat_title = html::escapeHTML($categories->cat_title);
	$categories_combo[$cat_title] = $categories->cat_id;
}

$medias_combo = $tmp_medias_combo = $dir = null;
try {
	$allowed_medias = array('png','jpg','gif','bmp','jpeg');
	$core->media = new dcMedia($core);
	$core->media->chdir($core->blog->settings->cinecturlink2->cinecturlink2_folder);
	$core->media->getDir();
	$dir =& $core->media->dir;
	
	foreach($dir['files'] as $file) {
		if (!in_array(files::getExtension($file->relname),$allowed_medias)) continue;
		$tmp_medias_combo[$file->media_title] = $file->file_url;
	}
	if (!empty($tmp_medias_combo)) {
		$medias_combo = array_merge(array('-'=>''),$tmp_medias_combo);
	}
}
catch (Exception $e) {
	//$core->error->add($e->getMessage());
}

$langs_combo = l10n::getISOcodes(true);
$links_action_combo = array(
	__('delete')			=> 'delete_links',
	__('change category')	=> 'moveto_change_links_category',
	__('change my rating')	=> 'moveto_change_links_note'
);
$notes_combo = array();
for ($i=0;$i<21;$i++) {
	$notes_combo[$i.'/20'] = $i;
}

$pager_base_url = $p_url.
	'&amp;sortby='.$sortby.
	'&amp;order='.$order.
	'&amp;nb='.$nb_per_page.
	'&amp;page=%s';

try {
	$links = $C2->getLinks($params);
	$links_counter = $C2->getLinks($params,true)->f(0);
	$links_list = new adminListCinecturlink2($core,$links,$links_counter,$pager_base_url);
}
catch (Exception $e) {
	$core->error->add($e->getMessage());
}

# Page headers
echo 
'<html><head>'.
'<title>'.__('Cinecturlink 2').'</title>'.
dcPage::jsToolBar().
dcPage::jsToolMan().
dcPage::jsLoad('js/_posts_list.js').
dcPage::jsLoad('index.php?pf=cinecturlink2/js/cinecturlink2.js').
dcPage::jsPageTabs().
"<script type=\"text/javascript\"> 
//<![CDATA[\n".
dcPage::jsVar('dotclear.msg.show_filters', $show_filters ? 'true':'false')."\n".
dcPage::jsVar('dotclear.msg.filter_posts_list', __('Show filters and display options'))."\n".
dcPage::jsVar('dotclear.msg.cancel_the_filter', __('Cancel filters and display options'))."\n".
"
\$(function(){if(!document.getElementById){return;} 
 \$('#newlinksearch').openGoogle('".$core->auth->getInfo('user_lang')."','#new_title'); 
 \$('#newimagesearch').openAmazon('".$core->auth->getInfo('user_lang')."','#new_title'); 
 \$('#newimageselect').fillLink('#new_image'); 
});
//]]>\n".
"</script>\n".
'</head>'.
'<body>';

# Change category for links
if (!empty($_POST['links']) && $action == 'moveto_change_links_category') {

	echo
	dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			__('Cinecturlink 2') => $p_url.'&amp;move=1#links',
			__('Change category of links') => ''
		)
	).
	dcPage::notices();
	
	echo '
	<p class="nav_prevnext">
	<a title="'.__('Go back to links list').'" href="'.$p_url.'&amp;move=1#links">«&nbsp;'.__('Back').'</a>
	</p>

	<div id="changecatlinks">
	<form method="post" action="'.$p_url.'#links">
	<p>'.__('Change category for this selection :').'</p>';

	foreach($_POST['links'] as $k => $link) {
		echo
		'<p><label class="classic" for="links'.$k.'">'.
		form::checkbox(array('links[]', 'links'.$k), $link, 1).' '.
		$C2->getLinks(array('link_id' => $link))->f('link_title').
		'</label></p>';
	}

	echo '
	<p><label for="upd_category">'.__('Select a category:').' '.
	form::combo('upd_category', $categories_combo, '', 'maximal').'
	</label></p><p>
	<input type="submit" name="updlinkcat" value="ok" />'.
	form::hidden(array('action'), 'change_links_category').
	$core->formNonce().'
	</p>
	</form>
	</div>';
}

# Change note for links
elseif (!empty($_POST['links']) && $action == 'moveto_change_links_note')
{
	echo	dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			__('Cinecturlink 2') => $p_url.'&amp;move=1#links',
			__('Change note of links') => ''
		)
	).
	dcPage::notices();

	echo '
	<p class="nav_prevnext">
	<a title="'.__('Go back to links list').'" href="'.$p_url.'&amp;move=1#links">«&nbsp;'.__('Back').'</a>
	</p>

	<div id="changenotelinks">
	<form method="post" action="'.$p_url.'#links">
	<p>'.__('Change note for this selection :').'</p>';

	foreach($_POST['links'] as $k => $link) {
		echo
		'<p><label class="classic" for="links'.$k.'">'.
		form::checkbox(array('links[]', 'links'.$k), $link, 1).' '.
		$C2->getLinks(array('link_id'=>$link))->f('link_title').
		'</label></p>';
	}

	echo '
	<p><label for="upd_note">'.__('Select a rating:').' '.
	form::combo('upd_note',$notes_combo, 10, 'maximal').'
	</label></p><p>
	<input type="submit" name="updlinknote" value="ok" />'.
	form::hidden(array('action'), 'change_links_note').
	$core->formNonce().'
	</p>
	</form>
	</div>';
}

# All other tabs
else {

	echo dcPage::breadcrumb(
		array(
			html::escapeHTML($core->blog->name) => '',
			__('Cinecturlink 2') => ''
		)
	).
	dcPage::notices();

	# List links
	echo '
	<div class="multi-part" id="links" title="'.__('Links').'">';

	if ($links->isEmpty()) {
		echo '<p>'.__('There is no link').'</p>';
	}
	else {
		echo '
		<form action="'.$p_url.'#links" method="get" id="filters-form">
		<h3 class="out-of-screen-if-js">'.__('Show filters and display options').'</h3>

		<div class="table">'.

		'<div class="cell">'.
		'<p><label for="sortby" class="ib">'.__('Order by:').'</label> '.
		form::combo('sortby',$sortby_combo,$sortby).'</p>'.
		'</div><div class="cell">'.
		'<p><label for="order" class="ib">'.__('Sort:').'</label> '.
		form::combo('order',$order_combo,$order).'</p>'.
		'</div><div class="cell">'.
		'<p><span class="label ib">'.__('Show').'</span> <label for="nb" class="classic">'.
		form::field('nb',3,3,$nb_per_page).' '.
		__('entries per page').'</label></p>'.
		'</div>'.
		'</div>'.

		'<p><input type="submit" value="'.__('Apply filters and display options').'" />'.
		form::hidden(array('p'), 'cinecturlink2').
		'<br class="clear" /></p>'. //Opera sucks

		'</form>

		<form action="'.$p_url.'#links" method="post" id="form-actions">';
		
		$links_list->display($page,$nb_per_page,$pager_base_url);

		echo '
		<div class="two-cols">
		<p class="col checkboxes-helpers"></p>
		<p class="col right">'.__('Selected links action:').' '.
		form::combo(array('action'),$links_action_combo).'
		<input type="submit" value="'.__('ok').'" />'.
		form::hidden(array('sortby'),$sortby).
		form::hidden(array('order'),$order).
		form::hidden(array('page'),$page).
		form::hidden(array('nb'),$nb_per_page).
		$core->formNonce().'
		</p>
		</div>
		</form>';
	}
	echo '	
	</div>';

	# Create/edit link
	$title = __('New link');

	if (null !== $upd_link_id) {

		$upd_rs = $C2->getLinks(array('link_id' => $upd_link_id));
		$upd_link_id = null;
		if (!$upd_rs->isEmpty()) {
			$new_title	= $upd_rs->link_title;
			$new_desc		= $upd_rs->link_desc;
			$new_author	= $upd_rs->link_author;
			$new_url		= $upd_rs->link_url;
			$new_category	= $upd_rs->cat_id;
			$new_lang		= $upd_rs->link_lang;
			$new_image	= $upd_rs->link_img;
			$new_note		= $upd_rs->link_note;
			$upd_link_id	= $upd_rs->link_id;
			$title		= __('Edit link');
		}
	}

	echo '
	<div class="multi-part" id="newlink" title="'.$title.'">
	<form id="newlinkform" method="post" action="'.$p_url.'#newlink">

	<div class="two-cols clearfix">
	<div class="col70">

	<p><label for="new_title">'.__('Title:').' '.
	form::field('new_title', 60, 255, $new_title, 'maximal').
	'</label></p>

	<p><label for="new_desc">'.__('Description:').' '.
	form::field('new_desc', 60, 255, $new_desc, 'maximal').
	'</label></p>

	<p><label for="new_author">'.__('Author:').' '.
	form::field('new_author', 60, 255, $new_author, 'maximal').
	'</label></p>

	<p><label for="new_url">'.__('Details URL:').' '.
	form::field('new_url', 60, 255, $new_url, 'maximal').'</label>'.
	'<a class="modal" href="http://google.com" id="newlinksearch">'.
	__('Search with Google').'</a>'.
	'</p>

	<p><label for="new_image">'.__('Image URL:').' '.
	form::field('new_image', 60, 255, $new_image, 'maximal').'</label>'.
	'<a class="modal" href="http://amazon.com" id="newimagesearch">'.
	__('Search with Amazon').'</a>'.
	'</p>';

	if (empty($medias_combo)) {
		echo
		'<p class="form-note">'.__('There is no image in cinecturlink media path.').'</p>';
	}
	else {
		echo '
		<p><label for="newimageselect">'.__('or select from repository:').' '.
		form::combo('newimageselect', $medias_combo, '', 'maximal').
		'</label></p>'.
		'<p class="form-note">'.__('Go to media manager to add image to cinecturlink path.').'</p>';
	}

	echo '

	</div>
	<div class="col30">

	<p><label for="new_category">'.__('Category:').' '.
	form::combo('new_category', $categories_combo, $new_category, 'maximal').
	'</label></p>

	<p><label for="new_lang">'.__('Lang:').' '.
	form::combo('new_lang', $langs_combo, $new_lang, 'maximal').
	'</label></p>

	<p><label for="new_note">'.__('My rating:').' '.
	form::combo('new_note', $notes_combo, $new_note, 'maximal').
	'</label></p>

	</div></div>

	<p>'.
	form::hidden(array('link_id'),$upd_link_id).
	form::hidden(array('action'),'create_link').
	$core->formNonce().'
	<input type="submit" name="newlink" value="'.($upd_link_id ? __('update link') : __('create link')).'" />
	</p>
	</form>
	</div>';

	# List categories
	echo '<div class="multi-part" id="cats" title="'.__('Categories').'">';

	if ($categories->isEmpty()) {
		echo '<p>'.__('There is no category').'</p>';
	}
	else {
		echo '
		<form method="post" action="'.$p_url.'#cats">
		<table class="maximal dragable">
		<thead><tr><th colspan="2">#</th><th>'.__('name').'</th><th>'.__('description').'</th></tr></thead>
		<tbody id="links-list-cat">';

		$i = 0;
		while($categories->fetch()) {
			$i++;
			$id = $categories->cat_id;
			$title = html::escapeHTML($categories->cat_title);
			$desc = html::escapeHTML($categories->cat_desc);

			echo '
			<tr class="line" id="l_'.$id.'">
			<td class="minimal handle">'.form::field(array('catpos['.$id.']'),2,5,$i).'</td>
			<td class="minimal">'.form::checkbox(array('delcat[]'),$id).'</td>
			<td class="nowrap">'.$title.'</td>
			<td class="maximal">'.$desc.'</td>
			</tr>';
		}

		echo '
		</tbody>
		</table>
		<p class="form-note">'.__('Check to delete').'</p>
		<p>'.
		form::hidden('cats_order', '').
		form::hidden(array('action'), 'update_categories').
		$core->formNonce().'
		<input type="submit" name="updcats" value="'.__('update categories').'" />
		</p>
		</form>';
	}

	echo '
	</div>';

	# Create category
	echo '
	<div class="multi-part" id="newcat" title="'.__('New category').'">
	<form method="post" action="'.$p_url.'#newcat">

	<p><label for="new_cattitle">'.__('Title:').' '.
	form::field('new_cattitle', 60, 64, $new_cattitle, 'maximal').
	'</label></p>

	<p><label for="new_catdesc">'.__('Description:').' '.
	form::field('new_catdesc', 60, 64, $new_catdesc, 'maximal').
	'</label></p>

	<p>'.
	form::hidden(array('action'), 'create_category').
	$core->formNonce().'
	<input type="submit" name="newcat" value="'.__('save').'" />
	</p>
	</form>
	</div>';
}

dcPage::helpBlock('cinecturlink2');

# Page footer
echo 
'<hr class="clear"/><p class="right modules">
<a class="module-config" '.
'href="plugins.php?module=cinecturlink2&amp;conf=1&amp;redir='.
urlencode('plugin.php?p=cinecturlink2').'">'.__('Configuration').'</a> - 
cinecturlink2 - '.$core->plugins->moduleInfo('cinecturlink2', 'version').'&nbsp;
<img alt="'.__('cinecturlink2').'" src="index.php?pf=cinecturlink2/icon.png" />
</p>
</body></html>';
