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

class adminlistCinecturlink2 extends adminGenericList
{
	public function display($page, $nb_per_page, $url)
	{
		if ($this->rs->isEmpty()) {
			echo '<p><strong>'.__('There is no link').'</strong></p>';
		}
		else {
			$pager = new pager($page, $this->rs_count, $nb_per_page,10);

			$pager->base_url = $url;

			$html_block =
			'<table class="clear">'.
			'<thead>'.
			'<tr>'.
			'<th class="nowrap" colspan="2">'.__('Title').'</th>'.
			'<th class="nowrap">'.__('Author').'</th>'.
			'<th class="maximal">'.__('Description').'</th>'.
			'<th class="maximal">'.__('Links').'</th>'.
			'<th class="nowrap">'.__('Category').'</th>'.
			'<th class="nowrap">'.__('My rating').'</th>'.
			'<th class="nowrap">'.__('Date').'</th>'.
			'</tr>'.
			'</thead>'.
			'<tbody>%s</tbody>'.
			'</table>';

			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
			$blocks = explode('%s',$html_block);
			echo $blocks[0];

			$this->rs->index(((integer)$page - 1) * $nb_per_page);
			$iter = 0;
			while ($iter < $nb_per_page) {
				echo $this->linkLine($url,$iter);

				if ($this->rs->isEnd())
					break;
				else
					$this->rs->moveNext();

				$iter++;
			}
			echo $blocks[1];
			echo '<p>'.__('Page(s)').' : '.$pager->getLinks().'</p>';
		}
	}

	private function linkLine($url, $loop)
	{
		return
		'<tr class="line">'."\n".
		'<td class="nowrap">'.
			form::checkbox(array('links[]'), $this->rs->link_id, 0).
		'</td>'.
		'<td class="nowrap">'.
		'<a href="plugin.php?p=cinecturlink2&amp;link_id='.$this->rs->link_id.'#newlink" title="'.__('Edit').'">'.
		html::escapeHTML($this->rs->link_title).
		'</a>'.
		"</td>\n".
		'<td class="nowrap">'.
		html::escapeHTML($this->rs->link_author).
		"</td>\n".
		'<td class="maximal">'.
		html::escapeHTML($this->rs->link_desc).
		"</td>\n".
		'<td class="nowrap">'.
		'<a href="'.$this->rs->link_url.'" title="'.html::escapeHTML($this->rs->link_url).'">'.__('URL').'</a> '.
		'<a href="'.$this->rs->link_img.'" title="'.html::escapeHTML($this->rs->link_img).'">'.__('image').'</a> '.
		"</td>\n".
		'<td class="nowrap">'.
		html::escapeHTML($this->rs->cat_title).
		"</td>\n".
		'<td class="nowrap">'.
		html::escapeHTML($this->rs->link_note).'/20'.
		"</td>\n".
		'<td class="nowrap">'.
		dt::dt2str($GLOBALS['core']->blog->settings->system->date_format.', '.$GLOBALS['core']->blog->settings->system->time_format,$this->rs->link_upddt,$GLOBALS['core']->auth->getInfo('user_tz')).
		"</td>\n".
		'</tr>'."\n";
	}
}
