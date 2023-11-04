<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Core\Frontend\Url;

/**
 * @brief       cinecturlink2 frontend URLclass.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class FrontendUrl extends Url
{
    public static function c2Page(?string $args): null
    {
        $args = (string) $args;

        if (!My::settings()->avtive
         || !My::settings()->public_active) {
            self::p404();
        }

        $tplset = App::themes()->getDefine(App::blog()->settings()->get('system')->get('theme'))->get('tplset');
        if (empty($tplset) || !is_dir(implode(DIRECTORY_SEPARATOR, [My::path(), 'default-templates', $tplset]))) {
            $tplset = App::config()->defaultTplset();
        }
        App::frontend()->template()->appendPath(implode(DIRECTORY_SEPARATOR, [My::path(), 'default-templates', $tplset]));

        $params = [];

        $n = self::getPageArgs($args, 'c2page');
        if ($n) {
            $GLOBALS['c2_page_number'] = $n;
        }

        $caturl = My::settings()->public_caturl;
        if (!$caturl) {
            $caturl = 'c2cat';
        }

        $c = self::getPageArgs($args, $caturl);
        if ($c) {
            if (is_numeric($c)) {
                $params['cat_id'] = (int) $c;
            } else {
                $params['cat_title'] = urldecode($c);
            }
        }

        $f = self::getPageArgs($args, 'feed');
        if (!empty($f) && in_array($f, ['atom', 'rss2'])) {
            $mime = $f == 'atom' ? 'application/atom+xml' : 'application/xml';

            //App::frontend()->context()->short_feed_items = App::blog()->settings()->system->short_feed_items;

            $params['limit']                           = App::blog()->settings()->get('system')->get('nb_post_per_feed');
            App::frontend()->context()->c2_page_params = $params;

            header('X-Robots-Tag: ' . App::frontend()->context()::robotsPolicy(App::blog()->settings()->get('system')->get('robots_policy'), ''));
            self::serveDocument('cinecturlink2-' . $f . '.xml', $mime);
        } else {
            $d = self::getPageArgs($args, 'c2detail');
            if (!empty($d)) {
                if (is_numeric($d)) {
                    $params['link_id'] = (int) $d;
                } else {
                    $params['link_title'] = urldecode($d);
                }
            }

            $params['limit']                           = (int) My::settings()->public_nbrpp;
            App::frontend()->context()->c2_page_params = $params;

            self::serveDocument('cinecturlink2.html', 'text/html');
        }

        return null;
    }

    protected static function getPageArgs(string &$args, string $part): string
    {
        if (preg_match('#(^|/)' . $part . '/([^/]+)#', $args, $m)) {
            $args = preg_replace('#(^|/)' . $part . '/([^/]+)#', '', $args);

            return $m[2];
        }

        return '';
    }
}
