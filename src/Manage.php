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
declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\Core\Process;

class Manage extends Process
{
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
