<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\Helper\Process\TraitProcess;

/**
 * @brief       cinecturlink2 manage class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Manage
{
    use TraitProcess;

    public static function init(): bool
    {
        return self::status(match ($_REQUEST['part'] ?? 'links') {
            'links' => ManageLinks::init(),
            'link'  => ManageLink::init(),
            'cats'  => ManageCats::init(),
            'cat'   => ManageCat::init(),
            default => false,
        });
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        return self::status(match ($_REQUEST['part'] ?? 'links') {
            'links' => ManageLinks::process(),
            'link'  => ManageLink::process(),
            'cats'  => ManageCats::process(),
            'cat'   => ManageCat::process(),
            default => false,
        });
    }

    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        match ($_REQUEST['part'] ?? 'links') {
            'links' => ManageLinks::render(),
            'link'  => ManageLink::render(),
            'cats'  => ManageCats::render(),
            'cat'   => ManageCat::render(),
            default => false,
        };
    }
}
