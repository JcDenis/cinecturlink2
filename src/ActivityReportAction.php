<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Database\Cursor;
use Dotclear\Plugin\activityReport\Action;
use Dotclear\Plugin\activityReport\ActivityReport;
use Dotclear\Plugin\activityReport\Group;

/**
 * @brief       cinecturlink2 plugin activityReport class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ActivityReportAction
{
    use TraitProcess;

    private const CINECTURLINK_CREATE = 'cinecturlink2Create';
    private const CINECTURLINK_UPDATE = 'cinecturlink2Update';
    private const CINECTURLINK_DELETE = 'cinecturlink2Delete';

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
            self::CINECTURLINK_CREATE,
            __('link creation'),
            __('A new cineturlink named "%s" was added by "%s"'),
            'cinecturlink2AfterAddLink',
            self::addLink(...)
        ));
        // from BEHAVIOR cinecturlink2AfterUpdLink in cinecturlink2/inc/class.cinecturlink2.php
        $group->add(new Action(
            self::CINECTURLINK_UPDATE,
            __('updating link'),
            __('Cinecturlink named "%s" has been updated by "%s"'),
            'cinecturlink2AfterUpdLink',
            self::updLink(...)
        ));
        // from BEHAVIOR cinecturlink2BeforeDelLink in cinecturlink2/inc/class.cinecturlink2.php
        $group->add(new Action(
            self::CINECTURLINK_DELETE,
            __('link deletion'),
            __('Cinecturlink named "%s" has been deleted by "%s"'),
            'cinecturlink2BeforeDelLink',
            self::delLink(...)
        ));

        ActivityReport::instance()->groups->add($group);

        return true;
    }

    public static function addLink(Cursor $cur): void
    {
        self::addLog(
            self::CINECTURLINK_CREATE,
            (string) $cur->getField('link_title')
        );
    }

    public static function updLink(Cursor $cur, int $id): void
    {
        self::addLog(
            self::CINECTURLINK_UPDATE,
            (string) (new Utils())->getLinks(['link_id' => $id])->field('link_title')
        );
    }

    public static function delLink(int $id): void
    {
        self::addLog(
            self::CINECTURLINK_DELETE,
            (string) (new Utils())->getLinks(['link_id' => $id])->field('link_title')
        );
    }

    private static function addLog(string $action, string $title)
    {
        ActivityReport::instance()->addLog(My::id(), $action, [
            $title,
            (string) App::auth()->getInfo('user_cn'),
        ]);
    }
}
