<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Core\Process;

/**
 * @brief       cinecturlink2 prepend class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
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

        App::url()->register(
            My::id(),
            'cinecturlink',
            '^cinecturlink(?:/(.+))?$',
            FrontendUrl::c2Page(...)
        );

        App::behavior()->addBehaviors([
            'sitemapsDefineParts' => PluginSitemaps::sitemapsDefineParts(...),
            'sitemapsURLsCollect' => PluginSitemaps::sitemapsURLsCollect(...),
        ]);

        return true;
    }
}
