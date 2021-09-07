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

/*
 * Taken from cinecturlink for Dotclear 1
 * By Tigroux and Brol
 * Under GNU GPL 2.0 license
 */

if (!defined('DC_RC_PATH')) {

	return null;
}

$this->registerModule(
	/* Name */
	"Cinecturlink 2",
	/* Description*/
	"Widgets and pages about books, musics, films, blogs you are interested in",
	/* Author */
	"Jean-Christian Denis",
	/* Version */
	'0.7',
	/* Properties */
	array(
		'permissions' => 'contentadmin',
		'type' => 'plugin',
		'dc_min' => '2.6',
		'support' => 'http://jcd.lv/q=cinecturlink2',
		'details' => 'http://plugins.dotaddict.org/dc2/details/cinecturlink2'
	)
);
