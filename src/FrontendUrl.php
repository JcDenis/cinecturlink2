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
use dcUrlHandlers;
use context;
use Dotclear\Core\Frontend\Utility;
use Dotclear\Helper\File\Path;

class FrontendUrl extends dcUrlHandlers
{
    public static function c2Page(?string $args)
    {
        $args = (string) $args;

        if (!My::settings()->avtive
         || !My::settings()->public_active) {
            self::p404();
        }

        $tplset = dcCore::app()->themes->getDefine(dcCore::app()->blog->settings->system->theme)->get('tplset');
        $tpldir = Path::real(dcCore::app()->plugins->getDefine(My::id())->get('root')) . DIRECTORY_SEPARATOR . Utility::TPL_ROOT . DIRECTORY_SEPARATOR;
        dcCore::app()->tpl->setPath(dcCore::app()->tpl->getPath(), $tpldir . (!empty($tplset) && is_dir($tpldir . $tplset) ? $tplset : DC_DEFAULT_TPLSET));

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

            //dcCore::app()->ctx->short_feed_items = dcCore::app()->blog->settings->system->short_feed_items;

            $params['limit']                   = dcCore::app()->blog->settings->system->nb_post_per_feed;
            dcCore::app()->ctx->c2_page_params = $params;

            header('X-Robots-Tag: ' . context::robotsPolicy(dcCore::app()->blog->settings->system->robots_policy, ''));
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

            $params['limit']                   = (int) My::settings()->public_nbrpp;
            dcCore::app()->ctx->c2_page_params = $params;

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
