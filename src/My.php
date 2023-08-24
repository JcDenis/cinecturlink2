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
use Dotclear\Module\MyPlugin;

class My extends MyPlugin
{
    public const CINECTURLINK_TABLE_NAME = \initCinecturlink2::CINECTURLINK_TABLE_NAME;
    public const CATEGORY_TABLE_NAME     = \initCinecturlink2::CATEGORY_TABLE_NAME;

    public const ALLOWED_MEDIA_EXTENSION = ['png', 'jpg', 'gif', 'bmp', 'jpeg'];

    public static function checkCustomContext(int $context): ?bool
    {
        if (in_array($context, [My::MENU, My::BACKEND])) {
            return defined('DC_CONTEXT_ADMIN')
                && !is_null(dcCore::app()->blog)
                && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
                    dcCore::app()->auth::PERMISSION_CONTENT_ADMIN,
                ]), dcCore::app()->blog->id);
        }

        return null;
    }
}
