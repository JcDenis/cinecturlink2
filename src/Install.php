<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Database\MetaRecord;
use Dotclear\Database\Statement\DeleteStatement;
use Dotclear\Database\Statement\SelectStatement;
use Dotclear\Database\Statement\UpdateStatement;
use Exception;

/**
 * @brief       cinecturlink2 install class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Install
{
    use TraitProcess;

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

            $s = App::db()->structure();
            $s->table(My::CINECTURLINK_TABLE_NAME)
                ->field('link_id', 'bigint', 0, false)
                ->field('blog_id', 'varchar', 32, false)
                ->field('cat_id', 'bigint', 0, true)
                ->field('user_id', 'varchar', 32, true)
                ->field('link_type', 'varchar', 32, false, "'cinecturlink'")
                ->field('link_title', 'varchar', 255, false)
                ->field('link_desc', 'varchar', 255, false)
                ->field('link_author', 'varchar', 255, false)
                ->field('link_lang', 'varchar', 5, false, "'en'")
                ->field('link_url', 'varchar', 255, false)
                ->field('link_img', 'varchar', 255, false)
                ->field('link_creadt', 'timestamp', 0, false, 'now()')
                ->field('link_upddt', 'timestamp', 0, false, 'now()')
                ->field('link_pos', 'smallint', 0, false, "'0'")
                ->field('link_note', 'smallint', 0, false, "'10'")
                ->field('link_count', 'bigint', 0, false, "'0'")

                ->primary('pk_cinecturlink2', 'link_id')
                ->index('idx_cinecturlink2_title', 'btree', 'link_title')
                ->index('idx_cinecturlink2_author', 'btree', 'link_author')
                ->index('idx_cinecturlink2_blog_id', 'btree', 'blog_id')
                ->index('idx_cinecturlink2_cat_id', 'btree', 'cat_id')
                ->index('idx_cinecturlink2_user_id', 'btree', 'user_id')
                ->index('idx_cinecturlink2_type', 'btree', 'link_type');

            $s->table(My::CATEGORY_TABLE_NAME)
                ->field('cat_id', 'bigint', 0, false)
                ->field('blog_id', 'varchar', 32, false)
                ->field('cat_title', 'varchar', 255, false)
                ->field('cat_desc', 'varchar', 255, false)
                ->field('cat_creadt', 'timestamp', 0, false, 'now()')
                ->field('cat_upddt', 'timestamp', 0, false, 'now()')
                ->field('cat_pos', 'smallint', 0, false, "'0'")

                ->primary('pk_cinecturlink2_cat', 'cat_id')
                ->index('idx_cinecturlink2_cat_blog_id', 'btree', 'blog_id')
                ->unique('uk_cinecturlink2_cat_title', 'cat_title', 'blog_id');

            App::db()->structure()->synchronize($s);

            $s = My::settings();
            $s->put('active', true, 'boolean', 'Enable cinecturlink2', false, true);
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
            App::error()->add($e->getMessage());

            return false;
        }
    }

    private static function upgradeSettings(): void
    {
        if (version_compare(App::version()->getVersion(My::id()), '2.0', '<')) {
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
                    ->ref(App::db()->con()->prefix() . App::blogWorkspace()::NS_TABLE_NAME)
                    ->column('setting_id')
                    ->value($id)
                    ->where('setting_id = ' . $sql->quote('cinecturlink2_' . $id))
                    ->and('setting_ns = ' . $sql->quote(My::id()))
                    ->update();
            }
        }

        // Setting "active" was saved as "avtive" (typo)
        if (version_compare(App::version()->getVersion(My::id()), '2.8', '<')) {
            $table = App::db()->con()->prefix() . App::blogWorkspace()::NS_TABLE_NAME;

            // Blogs settings (global one is set again on install)
            $sql = new SelectStatement();
            $rs  = $sql
                ->column('blog_id')
                ->from($table)
                ->where('setting_id = ' . $sql->quote('avtive'))
                ->and('setting_ns = ' . $sql->quote(My::id()))
                ->and('blog_id IS NOT NULL')
                ->select();

            while ($rs instanceof MetaRecord && $rs->fetch()) {
                $blog_id = $rs->strField('blog_id');

                // Keep an existing "active" setting as it is the one used until now
                $sql    = new SelectStatement();
                $exists = $sql
                    ->column($sql->count('setting_id'))
                    ->from($table)
                    ->where('setting_id = ' . $sql->quote('active'))
                    ->and('setting_ns = ' . $sql->quote(My::id()))
                    ->and('blog_id = ' . $sql->quote($blog_id))
                    ->select()?->cardinal();

                if (!$exists) {
                    $sql = new UpdateStatement();
                    $sql
                        ->ref($table)
                        ->column('setting_id')
                        ->value('active')
                        ->where('setting_id = ' . $sql->quote('avtive'))
                        ->and('setting_ns = ' . $sql->quote(My::id()))
                        ->and('blog_id = ' . $sql->quote($blog_id))
                        ->update();
                }
            }

            $sql = new DeleteStatement();
            $sql
                ->from($table)
                ->where('setting_id = ' . $sql->quote('avtive'))
                ->and('setting_ns = ' . $sql->quote(My::id()))
                ->delete();
        }
    }
}
