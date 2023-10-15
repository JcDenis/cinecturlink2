<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\Core\Process;
use Dotclear\Plugin\Uninstaller\Uninstaller;

/**
 * @brief       cinecturlink2 uninstall class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Uninstall extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::UNINSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        Uninstaller::instance()
            ->addUserAction(
                'settings',
                'delete_all',
                My::id()
            )
            ->addUserAction(
                My::id() . 'DeletePostsMeta',
                'delete_all',
                My::id()
            )
            ->addUserAction(
                'tables',
                'delete',
                My::CINECTURLINK_TABLE_NAME,
            )
            ->addUserAction(
                'tables',
                'delete',
                My::CATEGORY_TABLE_NAME,
            )
            ->addUserAction(
                'plugins',
                'delete',
                My::id()
            )
            ->addUserAction(
                'versions',
                'delete',
                My::id()
            )

            ->addDirectAction(
                'plugins',
                'delete',
                My::id()
            )
            ->addDirectAction(
                'versions',
                'delete',
                My::id()
            )
        ;

        return false;
    }
}
