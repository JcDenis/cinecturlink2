<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;

/**
 * @brief       cinecturlink2 frontend contxt class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class FrontendContext
{
    public static function PaginationNbPages()
    {
        if (App::frontend()->context()->c2_pagination === null) {
            return false;
        }
        $nb_posts    = App::frontend()->context()->c2_pagination->f(0);
        $nb_per_page = App::frontend()->context()->c2_params['limit'][1];
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

        $url = App::blog()->url() . $args;

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
        if (!isset(App::frontend()->context()->c2_page_params['cat_id'])
            && !isset(App::frontend()->context()->c2_page_params['cat_title'])
        ) {
            return false;
        }
        if (isset(App::frontend()->context()->c2_page_params['cat_id'])
            && App::frontend()->context()->c2_page_params['cat_id'] == App::frontend()->context()->c2_categories->cat_id
        ) {
            return true;
        }
        if (isset(App::frontend()->context()->c2_page_params['cat_title'])
            && App::frontend()->context()->c2_page_params['cat_title'] == App::frontend()->context()->c2_categories->cat_title
        ) {
            return true;
        }

        return false;
    }
}
