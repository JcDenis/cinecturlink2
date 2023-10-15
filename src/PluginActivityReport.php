<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Plugin\activityReport\ActivityReport;

/**
 * @brief       cinecturlink2 activityReport class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class PluginActivityReport
{
    public static function add()
    {
        ActivityReport::instance()->addGroup('cinecturlink2', __('Plugin cinecturlink2'));

        // from BEHAVIOR cinecturlink2AfterAddLink in cinecturlink2/inc/class.cinecturlink2.php
        ActivityReport::instance()->addAction(
            'cinecturlink2',
            'create',
            __('link creation'),
            __('A new cineturlink named "%s" was added by "%s"'),
            'cinecturlink2AfterAddLink',
            self::addLink(...)
        );
        // from BEHAVIOR cinecturlink2AfterUpdLink in cinecturlink2/inc/class.cinecturlink2.php
        ActivityReport::instance()->addAction(
            'cinecturlink2',
            'update',
            __('updating link'),
            __('Cinecturlink named "%s" has been updated by "%s"'),
            'cinecturlink2AfterUpdLink',
            self::updLink(...)
        );
        // from BEHAVIOR cinecturlink2BeforeDelLink in cinecturlink2/inc/class.cinecturlink2.php
        ActivityReport::instance()->addAction(
            'cinecturlink2',
            'delete',
            __('link deletion'),
            __('Cinecturlink named "%s" has been deleted by "%s"'),
            'cinecturlink2BeforeDelLink',
            self::delLink(...)
        );
    }

    public static function addLink($cur)
    {
        $logs = [
            $cur->link_title,
            App::auth()->getInfo('user_cn'),
        ];
        ActivityReport::instance()->addLog('cinecturlink2', 'create', $logs);
    }

    public static function updLink($cur, $id)
    {
        $C2 = new Utils();
        $rs = $C2->getLinks(['link_id' => $id]);

        $logs = [
            $rs->link_title,
            App::auth()->getInfo('user_cn'),
        ];
        ActivityReport::instance()->addLog('cinecturlink2', 'update', $logs);
    }

    public static function delLink($id)
    {
        $C2 = new Utils();
        $rs = $C2->getLinks(['link_id' => $id]);

        $logs = [
            $rs->link_title,
            App::auth()->getInfo('user_cn'),
        ];
        ActivityReport::instance()->addLog('cinecturlink2', 'delete', $logs);
    }
}
