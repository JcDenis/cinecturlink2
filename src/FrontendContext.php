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
    public static function PaginationNbPages(): int
    {
        if (App::frontend()->context()->c2_pagination === null) {
            return 0;
        }
        $nb_posts    = App::frontend()->context()->c2_pagination->f(0);
        $nb_per_page = App::frontend()->context()->c2_params['limit'][1];
        $nb_pages    = ceil($nb_posts / $nb_per_page);

        return (int) $nb_pages;
    }

    public static function PaginationPosition(string|int $offset = 0): int
    {
        if (isset($GLOBALS['c2_page_number'])) {
            $p = $GLOBALS['c2_page_number'];
        } else {
            $p = 1;
        }
        $p = (int) $p + (int) $offset;
        $n = self::PaginationNbPages();
        if (!$n) {
            return $p;
        }

        return $p > $n || $p <= 0 ? 1 : $p;
    }

    public static function PaginationStart(): bool
    {
        return isset($GLOBALS['c2_page_number']) ? self::PaginationPosition() == 1 : true;
    }

    public static function PaginationEnd(): bool
    {
        return isset($GLOBALS['c2_page_number']) ? self::PaginationPosition() == self::PaginationNbPages() : false;
    }

    public static function PaginationURL(int|string $offset = 0): string
    {
        $args = $_SERVER['URL_REQUEST_PART'];

        $n = self::PaginationPosition($offset);

        $args = preg_replace('#(^|/)c2page/([0-9]+)$#', '', $args);
        if (!is_string($args)) {
            $args = '';
        }

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

    public static function categoryCurrent(): bool
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
