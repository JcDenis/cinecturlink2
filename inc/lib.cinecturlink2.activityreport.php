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

if (!defined('DC_RC_PATH')) {

	return null;
}

# This file is used with plugin activityReport
$core->activityReport->addGroup('cinecturlink2', __('Plugin cinecturlink2'));

# from BEHAVIOR cinecturlink2AfterAddLink in cinecturlink2/inc/class.cinecturlink2.php
$core->activityReport->addAction(
	'cinecturlink2',
	'create',
	__('link creation'),
	__('A new cineturlink named "%s" was added by "%s"'),
	'cinecturlink2AfterAddLink',
	array('cinecturlink2ActivityReportBehaviors', 'addLink')
);
# from BEHAVIOR cinecturlink2AfterUpdLink in cinecturlink2/inc/class.cinecturlink2.php
$core->activityReport->addAction(
	'cinecturlink2',
	'update',
	__('updating link'),
	__('Cinecturlink named "%s" has been updated by "%s"'),
	'cinecturlink2AfterUpdLink',
	array('cinecturlink2ActivityReportBehaviors', 'updLink')
);
# from BEHAVIOR cinecturlink2BeforeDelLink in cinecturlink2/inc/class.cinecturlink2.php
$core->activityReport->addAction(
	'cinecturlink2',
	'delete',
	__('link deletion'),
	__('Cinecturlink named "%s" has been deleted by "%s"'),
	'cinecturlink2BeforeDelLink',
	array('cinecturlink2ActivityReportBehaviors', 'delLink')
);

class cinecturlink2ActivityReportBehaviors
{
	public static function addLink($cur)
	{
		global $core;

		$logs = array(
			$cur->link_title,
			$core->auth->getInfo('user_cn')
		);

		$core->activityReport->addLog('cinecturlink2', 'create', $logs);
	}

	public static function updLink($cur, $id)
	{
		global $core;
		$C2 = new cinecturlink2($core);
		$rs = $C2->getLinks(array('link_id'=>$id));

		$logs = array(
			$rs->link_title,
			$core->auth->getInfo('user_cn')
		);

		$core->activityReport->addLog('cinecturlink2', 'update', $logs);
	}

	public static function delLink($id)
	{
		global $core;
		$C2 = new cinecturlink2($core);
		$rs = $C2->getLinks(array('link_id'=>$id));

		$logs = array(
			$rs->link_title,
			$core->auth->getInfo('user_cn')
		);

		$core->activityReport->addLog('cinecturlink2', 'delete', $logs);
	}
}
