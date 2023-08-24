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

class FrontendContext
{
    public static function PaginationNbPages()
    {
        if (dcCore::app()->ctx->c2_pagination === null) {
            return false;
        }
        $nb_posts    = dcCore::app()->ctx->c2_pagination->f(0);
        $nb_per_page = dcCore::app()->ctx->c2_params['limit'][1];
        $nb_pages    = ceil($nb_posts / $nb_per_page);

        return $nb_pages;
    }

    public static function PaginationPosition($offset = 0)
    {
        if (isset($GLOBALS['c2_page_number'])) {
            $p = $GLOBALS['c2_page_number'];
        } else {
            $p = 1;
        }
        $p = $p + $offset;
        $n = self::PaginationNbPages();
        if (!$n) {
            return $p;
        }

        return $p > $n || $p <= 0 ? 1 : $p;
    }

    public static function PaginationStart()
    {
        if (isset($GLOBALS['c2_page_number'])) {
            return self::PaginationPosition() == 1;
        }

        return true;
    }

    public static function PaginationEnd()
    {
        if (isset($GLOBALS['c2_page_number'])) {
            return self::PaginationPosition() == self::PaginationNbPages();
        }

        return false;
    }

    public static function PaginationURL($offset = 0)
    {
        $args = $_SERVER['URL_REQUEST_PART'];

        $n = self::PaginationPosition($offset);

        $args = preg_replace('#(^|/)c2page/([0-9]+)$#', '', $args);

        $url = dcCore::app()->blog->url . $args;

        if ($n > 1) {
            $url = preg_replace('#/$#', '', $url);
            $url .= '/c2page/' . $n;
        }
        # If search param
        if (!empty($_GET['q'])) {
            $s = strpos($url, '?') !== false ? '&amp;' : '?';
            $url .= $s . 'q=' . rawurlencode($_GET['q']);
        }

        return $url;
    }

    public static function categoryCurrent()
    {
        if (!isset(dcCore::app()->ctx->c2_page_params['cat_id'])
            && !isset(dcCore::app()->ctx->c2_page_params['cat_title'])
        ) {
            return false;
        }
        if (isset(dcCore::app()->ctx->c2_page_params['cat_id'])
            && dcCore::app()->ctx->c2_page_params['cat_id'] == dcCore::app()->ctx->c2_categories->cat_id
        ) {
            return true;
        }
        if (isset(dcCore::app()->ctx->c2_page_params['cat_title'])
            && dcCore::app()->ctx->c2_page_params['cat_title'] == dcCore::app()->ctx->c2_categories->cat_title
        ) {
            return true;
        }

        return false;
    }
}
