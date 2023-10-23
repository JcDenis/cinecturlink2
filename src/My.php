<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Module\MyPlugin;

/**
 * @brief       cinecturlink2 My helper.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class My extends MyPlugin
{
    /**
     * Link table name.
     *
     * @var     string  CINECTURLINK_TABLE_NAME
     */
    public const CINECTURLINK_TABLE_NAME = 'cinecturlink2';

    /**
     * Category table name.
     *
     * @var     string  CATEGORY_TABLE_NAME
     */
    public const CATEGORY_TABLE_NAME = 'cinecturlink2_cat';

    /**
     * Allowed media extension.
     *
     * @var     array<int, string>  ALLOWED_MEDIA_EXTENSION
     */
    public const ALLOWED_MEDIA_EXTENSION = ['png', 'jpg', 'gif', 'bmp', 'jpeg'];

    public static function checkCustomContext(int $context): ?bool
    {
        return match ($context) {
            // Add content admin perm to backend
            self::MENU, self::MANAGE => App::task()->checkContext('BACKEND')
                && App::auth()->check(App::auth()->makePermissions([
                    App::auth()::PERMISSION_CONTENT_ADMIN,
                ]), App::blog()->id()),

            default => null,
        };
    }
}
