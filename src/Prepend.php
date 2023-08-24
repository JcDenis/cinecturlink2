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

use dcCore;
use Dotclear\Core\Process;

class Prepend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->url->register(
            My::id(),
            'cinecturlink',
            '^cinecturlink(?:/(.+))?$',
            [FrontendUrl::class, 'c2Page']
        );

        dcCore::app()->addBehaviors([
            'sitemapsDefineParts' => [PluginSitemaps::class, 'sitemapsDefineParts'],
            'sitemapsURLsCollect' => [PluginSitemaps::class, 'sitemapsURLsCollect'],
        ]);

        if (defined('ACTIVITY_REPORT_V2')) {
            PluginActivityReport::add();
        }

        return true;
    }
}
