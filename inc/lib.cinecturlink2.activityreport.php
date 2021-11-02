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

class cinecturlink2ActivityReportBehaviors
{
    public static function add($core)
    {
        $core->activityReport->addGroup('cinecturlink2', __('Plugin cinecturlink2'));

        // from BEHAVIOR cinecturlink2AfterAddLink in cinecturlink2/inc/class.cinecturlink2.php
        $core->activityReport->addAction(
            'cinecturlink2',
            'create',
            __('link creation'),
            __('A new cineturlink named "%s" was added by "%s"'),
            'cinecturlink2AfterAddLink',
            ['cinecturlink2ActivityReportBehaviors', 'addLink']
        );
        // from BEHAVIOR cinecturlink2AfterUpdLink in cinecturlink2/inc/class.cinecturlink2.php
        $core->activityReport->addAction(
            'cinecturlink2',
            'update',
            __('updating link'),
            __('Cinecturlink named "%s" has been updated by "%s"'),
            'cinecturlink2AfterUpdLink',
            ['cinecturlink2ActivityReportBehaviors', 'updLink']
        );
        // from BEHAVIOR cinecturlink2BeforeDelLink in cinecturlink2/inc/class.cinecturlink2.php
        $core->activityReport->addAction(
            'cinecturlink2',
            'delete',
            __('link deletion'),
            __('Cinecturlink named "%s" has been deleted by "%s"'),
            'cinecturlink2BeforeDelLink',
            ['cinecturlink2ActivityReportBehaviors', 'delLink']
        );
    }

    public static function addLink($cur)
    {
        global $core;

        $logs = [
            $cur->link_title,
            $core->auth->getInfo('user_cn')
        ];
        $core->activityReport->addLog('cinecturlink2', 'create', $logs);
    }

    public static function updLink($cur, $id)
    {
        global $core;
        $C2 = new cinecturlink2($core);
        $rs = $C2->getLinks(['link_id' => $id]);

        $logs = [
            $rs->link_title,
            $core->auth->getInfo('user_cn')
        ];
        $core->activityReport->addLog('cinecturlink2', 'update', $logs);
    }

    public static function delLink($id)
    {
        global $core;
        $C2 = new cinecturlink2($core);
        $rs = $C2->getLinks(['link_id' => $id]);

        $logs = [
            $rs->link_title,
            $core->auth->getInfo('user_cn')
        ];
        $core->activityReport->addLog('cinecturlink2', 'delete', $logs);
    }
}
