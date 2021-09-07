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

require_once dirname(__FILE__).'/_widgets.php';

# Admin menu
$_menu['Plugins']->addItem(
	__('My cinecturlink'),
	'plugin.php?p=cinecturlink2',
	'index.php?pf=cinecturlink2/icon.png',
	preg_match(
		'/plugin.php\?p=cinecturlink2(&.*)?$/',
		$_SERVER['REQUEST_URI'])
	,
	$core->auth->check('contentadmin', $core->blog->id)
);

$core->addBehavior(
	'adminDashboardFavorites',
	array('cinecturlink2AdminBehaviors', 'adminDashboardFavorites')
);

class cinecturlink2AdminBehaviors
{
	public static function adminDashboardFavorites($core, $favs)
	{
		$favs->register('cinecturlink2', array(
			'title'		=> __('My cinecturlink'),
			'url'		=> 'plugin.php?p=cinecturlink2#links',
			'small-icon'	=> 'index.php?pf=cinecturlink2/icon.png',
			'large-icon'	=> 'index.php?pf=cinecturlink2/icon-big.png',
			'permissions'	=> $core->auth->check(
				'contentadmin',
				$core->blog->id
			),
			'active_cb'	=> array(
				'cinecturlink2AdminBehaviors', 
				'adminDashboardFavoritesActive'
			)
		));
	}

	public static function adminDashboardFavoritesActive($request, $params)
	{
		return $request == 'plugin.php' 
			&& isset($params['p']) 
			&& $params['p'] == 'cinecturlink2';
	}
}
