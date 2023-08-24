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
use dcNamespace;
use Dotclear\Core\Process;
use Dotclear\Database\Structure;
use Dotclear\Database\Statement\UpdateStatement;
use Exception;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            self::upgradeSettings();

            $s = new Structure(dcCore::app()->con, dcCore::app()->prefix);
            $s->{My::CINECTURLINK_TABLE_NAME}
                ->link_id('bigint', 0, false)
                ->blog_id('varchar', 32, false)
                ->cat_id('bigint', 0, true)
                ->user_id('varchar', 32, true)
                ->link_type('varchar', 32, false, "'cinecturlink'")
                ->link_title('varchar', 255, false)
                ->link_desc('varchar', 255, false)
                ->link_author('varchar', 255, false)
                ->link_lang('varchar', 5, false, "'en'")
                ->link_url('varchar', 255, false)
                ->link_img('varchar', 255, false)
                ->link_creadt('timestamp', 0, false, 'now()')
                ->link_upddt('timestamp', 0, false, 'now()')
                ->link_pos('smallint', 0, false, "'0'")
                ->link_note('smallint', 0, false, "'10'")
                ->link_count('bigint', 0, false, "'0'")

                ->primary('pk_cinecturlink2', 'link_id')
                ->index('idx_cinecturlink2_title', 'btree', 'link_title')
                ->index('idx_cinecturlink2_author', 'btree', 'link_author')
                ->index('idx_cinecturlink2_blog_id', 'btree', 'blog_id')
                ->index('idx_cinecturlink2_cat_id', 'btree', 'cat_id')
                ->index('idx_cinecturlink2_user_id', 'btree', 'user_id')
                ->index('idx_cinecturlink2_type', 'btree', 'link_type');

            $s->{My::CATEGORY_TABLE_NAME}
                ->cat_id('bigint', 0, false)
                ->blog_id('varchar', 32, false)
                ->cat_title('varchar', 255, false)
                ->cat_desc('varchar', 255, false)
                ->cat_creadt('timestamp', 0, false, 'now()')
                ->cat_upddt('timestamp', 0, false, 'now()')
                ->cat_pos('smallint', 0, false, "'0'")

                ->primary('pk_cinecturlink2_cat', 'cat_id')
                ->index('idx_cinecturlink2_cat_blog_id', 'btree', 'blog_id')
                ->unique('uk_cinecturlink2_cat_title', 'cat_title', 'blog_id');

            (new Structure(dcCore::app()->con, dcCore::app()->prefix))->synchronize($s);

            $s = My::settings();
            $s->put('avtive', true, 'boolean', 'Enable cinecturlink2', false, true);
            $s->put('widthmax', 100, 'integer', 'Maximum width of picture', false, true);
            $s->put('folder', 'cinecturlink', 'string', 'Public folder of pictures', false, true);
            $s->put('triggeronrandom', false, 'boolean', 'Open link in new window', false, true);
            $s->put('public_active', false, 'boolean', 'Enable cinecturlink2', false, true);
            $s->put('public_title', '', 'string', 'Title of public page', false, true);
            $s->put('public_description', '', 'string', 'Description of public page', false, true);
            $s->put('public_nbrpp', 20, 'integer', 'Number of entries per page on public page', false, true);
            $s->put('public_caturl', 'c2cat', 'string', 'Part of URL for a category list', false, true);

            return true;
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());

            return false;
        }
    }

    private static function upgradeSettings(): void
    {
        if (version_compare((string) dcCore::app()->getVersion(My::id()), '2.0', '<')) {
            $ids = [
                'active',
                'widthmax',
                'folder',
                'triggeronrandom',
                'public_active',
                'public_title',
                'public_description',
                'public_nbrpp',
                'public_caturl',
            ];

            foreach ($ids as $id) {
                $sql = new UpdateStatement();
                $sql
                    ->ref(dcCore::app()->prefix . dcNamespace::NS_TABLE_NAME)
                    ->column('setting_id')
                    ->value($id)
                    ->where('setting_id = ' . $sql->quote('cinecturlink2_' . $id))
                    ->and('setting_ns = ' . $sql->quote(My::id()))
                    ->update();
            }
        }
    }
}
