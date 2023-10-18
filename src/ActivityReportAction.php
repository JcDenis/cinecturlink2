<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Database\Cursor;
use Dotclear\Plugin\activityReport\{
    Action,
    ActivityReport,
    Group
};

/**
 * @brief       cinecturlink2 plugin activityReport class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ActivityReportAction extends Process
{
    public static function init(): bool
    {
        return self::status(true);
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        $group = new Group(My::id(), My::name());

        // from BEHAVIOR cinecturlink2AfterAddLink in cinecturlink2/inc/class.cinecturlink2.php
        $group->add(new Action(
            'cinecturlink2Create',
            __('link creation'),
            __('A new cineturlink named "%s" was added by "%s"'),
            'cinecturlink2AfterAddLink',
            self::addLink(...)
        ));
        // from BEHAVIOR cinecturlink2AfterUpdLink in cinecturlink2/inc/class.cinecturlink2.php
        $group->add(new Action(
            'cinecturlink2Update',
            __('updating link'),
            __('Cinecturlink named "%s" has been updated by "%s"'),
            'cinecturlink2AfterUpdLink',
            self::updLink(...)
        ));
        // from BEHAVIOR cinecturlink2BeforeDelLink in cinecturlink2/inc/class.cinecturlink2.php
        $group->add(new Action(
            'cinecturlink2Delete',
            __('link deletion'),
            __('Cinecturlink named "%s" has been deleted by "%s"'),
            'cinecturlink2BeforeDelLink',
            self::delLink(...)
        ));

        ActivityReport::instance()->groups->add($group);

        return true;
    }

    public static function addLink(Cursor $cur)
    {
        $logs = [
            $cur->link_title,
            App::auth()->getInfo('user_cn'),
        ];
        ActivityReport::instance()->addLog('cinecturlink2', 'create', $logs);
    }

    public static function updLink(Cursor $cur, int $id)
    {
        $C2 = new Utils();
        $rs = $C2->getLinks(['link_id' => $id]);

        $logs = [
            $rs->link_title,
            App::auth()->getInfo('user_cn'),
        ];
        ActivityReport::instance()->addLog('cinecturlink2', 'update', $logs);
    }

    public static function delLink(int $id)
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
