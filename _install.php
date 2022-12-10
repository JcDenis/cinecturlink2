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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

try {
    if (!dcCore::app()->newVersion(
        basename(__DIR__), 
        dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version')
    )) {
        return null;
    }

    $s = new dbStruct(dcCore::app()->con, dcCore::app()->prefix);
    $s->{initCinecturlink2::CINECTURLINK_TABLE_NAME}
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

    $s->{initCinecturlink2::CATEGORY_TABLE_NAME}
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

    $si      = new dbStruct(dcCore::app()->con, dcCore::app()->prefix);
    $changes = $si->synchronize($s);

    dcCore::app()->blog->settings->addNamespace('cinecturlink2');
    $s = dcCore::app()->blog->settings->cinecturlink2;
    $s->put('cinecturlink2_active', true, 'boolean', 'Enable cinecturlink2', false, true);
    $s->put('cinecturlink2_widthmax', 100, 'integer', 'Maximum width of picture', false, true);
    $s->put('cinecturlink2_folder', 'cinecturlink', 'string', 'Public folder of pictures', false, true);
    $s->put('cinecturlink2_triggeronrandom', false, 'boolean', 'Open link in new window', false, true);
    $s->put('cinecturlink2_public_active', false, 'boolean', 'Enable cinecturlink2', false, true);
    $s->put('cinecturlink2_public_title', '', 'string', 'Title of public page', false, true);
    $s->put('cinecturlink2_public_description', '', 'string', 'Description of public page', false, true);
    $s->put('cinecturlink2_public_nbrpp', 20, 'integer', 'Number of entries per page on public page', false, true);
    $s->put('cinecturlink2_public_caturl', 'c2cat', 'string', 'Part of URL for a category list', false, true);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
