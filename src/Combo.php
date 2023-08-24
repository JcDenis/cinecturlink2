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
use dcMedia;
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\L10n;
use Exception;

class Combo
{
    /**
     * @return  array<string,string>
     */
    public static function categoriesCombo(): array
    {
        $stack = ['-' => ''];

        try {
            $rs = (new Utils())->getCategories();
            while ($rs->fetch()) {
                $stack[Html::escapeHTML((string) $rs->f('cat_title'))] = $rs->f('cat_id');
            }
        } catch (Exception $e) {
        }

        return $stack;
    }

    /**
     * @return  array<string,string>
     */
    public static function langsCombo(): array
    {
        return L10n::getISOcodes(true);
    }

    /**
     * @return  array<int,int>
     */
    public static function notesCombo(): array
    {
        return range(0, 20);
    }

    /**
     * @return  array<string,string>
     */
    public static function mediaCombo(): array
    {
        $stack = $tmp = [];
        $dir   = null;

        try {
            dcCore::app()->media = new dcMedia();
            dcCore::app()->media->chdir(My::settings()->folder);
            dcCore::app()->media->getDir();
            $dir = & dcCore::app()->media->dir;

            foreach ($dir['files'] as $file) {
                if (!in_array($file->extension, My::ALLOWED_MEDIA_EXTENSION)) {
                    continue;
                }
                $tmp[$file->media_title] = $file->file_url;
            }
            if (!empty($tmp)) {
                $stack = array_merge(['-' => ''], $tmp);
            }
        } catch (Exception $e) {
        }

        return $stack;
    }
}
