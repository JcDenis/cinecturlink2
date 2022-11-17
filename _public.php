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
if (!defined('DC_RC_PATH')) {
    return null;
}

require_once __DIR__ . '/_widgets.php';

dcCore::app()->blog->settings->addNamespace('cinecturlink2');

$c2_tpl_values = [
    'c2PageFeedID',
    'c2PageFeedURL',
    'c2PageURL',
    'c2PageTitle',
    'c2PageDescription',

    'c2EntryIfOdd',
    'c2EntryIfFirst',
    'c2EntryFeedID',
    'c2EntryID',
    'c2EntryTitle',
    'c2EntryDescription',
    'c2EntryFromAuthor',
    'c2EntryAuthorCommonName',
    'c2EntryAuthorDisplayName',
    'c2EntryAuthorEmail',
    'c2EntryAuthorID',
    'c2EntryAuthorLink',
    'c2EntryAuthorURL',
    'c2EntryLang',
    'c2EntryURL',
    'c2EntryCategory',
    'c2EntryCategoryID',
    'c2EntryCategoryURL',
    'c2EntryImg',
    'c2EntryDate',
    'c2EntryTime',

    'c2PaginationCounter',
    'c2PaginationCurrent',
    'c2PaginationURL',

    'c2CategoryFeedID',
    'c2CategoryFeedURL',
    'c2CategoryID',
    'c2CategoryTitle',
    'c2CategoryDescription',
    'c2CategoryURL',
];

$c2_tpl_blocks = [
    'c2If',

    'c2Entries',
    'c2EntriesHeader',
    'c2EntriesFooter',
    'c2EntryIf',

    'c2Pagination',
    'c2PaginationIf',

    'c2Categories',
    'c2CategoriesHeader',
    'c2CategoriesFooter',
    'c2CategoryIf',
];

if (dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_active) {
    foreach ($c2_tpl_blocks as $v) {
        dcCore::app()->tpl->addBlock($v, ['tplCinecturlink2', $v]);
    }
    foreach ($c2_tpl_values as $v) {
        dcCore::app()->tpl->addValue($v, ['tplCinecturlink2', $v]);
    }
} else {
    foreach (array_merge($c2_tpl_blocks, $c2_tpl_values) as $v) {
        dcCore::app()->tpl->addBlock($v, ['tplCinecturlink2', 'disable']);
    }
}

class urlCinecturlink2 extends dcUrlHandlers
{
    public static function c2Page($args)
    {
        dcCore::app()->blog->settings->addNamespace('cinecturlink2');
        $args = (string) $args;

        if (!dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_active
         || !dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_active) {
            self::p404();

            return null;
        }

        dcCore::app()->tpl->setPath(
            dcCore::app()->tpl->getPath(),
            __DIR__ . '/default-templates/'
        );

        $params = [];

        $n = self::getPageArgs($args, 'c2page');
        if ($n) {
            $GLOBALS['c2_page_number'] = $n;
        }

        $caturl = dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_caturl;
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
        if ($f && in_array($f, ['atom', 'rss2'])) {
            $mime = $f == 'atom' ? 'application/atom+xml' : 'application/xml';

            //dcCore::app()->ctx->short_feed_items = dcCore::app()->blog->settings->system->short_feed_items;

            $params['limit']                   = dcCore::app()->blog->settings->system->nb_post_per_feed;
            dcCore::app()->ctx->c2_page_params = $params;

            header('X-Robots-Tag: ' . context::robotsPolicy(dcCore::app()->blog->settings->system->robots_policy, ''));
            self::serveDocument('cinecturlink2-' . $f . '.xml', $mime);
        } else {
            $d = self::getPageArgs($args, 'c2detail');
            if ($d) {
                if (is_numeric($d)) {
                    $params['link_id'] = (int) $d;
                } else {
                    $params['link_title'] = urldecode($d);
                }
            }

            $params['limit']                   = dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_nbrpp;
            dcCore::app()->ctx->c2_page_params = $params;

            self::serveDocument('cinecturlink2.html', 'text/html');
        }

        return null;
    }

    protected static function getPageArgs(&$args, $part)
    {
        if (preg_match('#(^|/)' . $part . '/([^/]+)#', $args, $m)) {
            $args = preg_replace('#(^|/)' . $part . '/([^/]+)#', '', $args);

            return $m[2];
        }

        return false;
    }
}

class tplCinecturlink2
{
    public static function disable($a, $c = null)
    {
        return '';
    }

    public static function c2PageURL($a)
    {
        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->blog->url.dcCore::app()->url->getBase(\'cinecturlink2\')') . '; ?>';
    }

    public static function c2PageTitle($a)
    {
        return "<?php \$title = (string) dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_title; if (empty(\$title)) { \$title = __('My cinecturlink'); } echo " . sprintf(dcCore::app()->tpl->getFilters($a), '$title') . '; ?>';
    }

    public static function c2PageFeedURL($a)
    {
        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->blog->url.dcCore::app()->url->getBase("cinecturlink2")."/feed/' . (!empty($a['type']) && preg_match('#^(rss2|atom)$#', $a['type']) ? $a['type'] : 'atom') . '"') . '; ?>';
    }

    public static function c2PageFeedID($a)
    {
        return 'urn:md5:<?php echo md5(dcCore::app()->blog->id."cinecturlink2"); ?>';
    }

    public static function c2PageDescription($a)
    {
        return '<?php $description = (string) dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_description; echo ' . sprintf(dcCore::app()->tpl->getFilters($a), '$description') . '; ?>';
    }

    public static function c2If($a, $c)
    {
        $if = [];

        $operator = isset($a['operator']) ? self::getOperator($a['operator']) : '&&';

        if (isset($a['request_link'])) {
            $sign = (bool) $a['request_link'] ? '' : '!';
            $if[] = $sign . '(isset(dcCore::app()->ctx->c2_page_params["link_id"]) || isset(dcCore::app()->ctx->c2_page_params["link_title"]))';
        }

        if (isset($a['request_cat'])) {
            $sign = (bool) $a['request_cat'] ? '' : '!';
            $if[] = $sign . '(isset(dcCore::app()->ctx->c2_page_params["cat_id"]) || isset(dcCore::app()->ctx->c2_page_params["cat_title"]))';
        }

        return empty($if) ? $c : '<?php if(' . implode(' ' . $operator . ' ', $if) . ") : ?>\n" . $c . "<?php endif; ?>\n";
    }

    public static function c2Entries($a, $c)
    {
        $lastn = isset($a['lastn']) ? abs((int) $a['lastn']) + 0 : -1;

        $res = 'if (!isset($c2_page_number)) { $c2_page_number = 1; }' . "\n";

        if ($lastn != 0) {
            if ($lastn > 0) {
                $res .= "\$params['limit'] = " . $lastn . ";\n";
            } else {
                $res .= "if (!isset(\$params['limit']) || \$params['limit'] < 1) { \$params['limit'] = 10; }\n";
            }
            if (!isset($a['ignore_pagination']) || $a['ignore_pagination'] == '0') {
                $res .= "\$params['limit'] = array(((\$c2_page_number-1)*\$params['limit']),\$params['limit']);\n";
            } else {
                $res .= "\$params['limit'] = array(0, \$params['limit']);\n";
            }
        }

        if (isset($a['category'])) {
            if ($a['category'] == 'null') {
                $res .= "\$params['sql'] = ' AND L.cat_id IS NULL ';\n";
            } elseif (is_numeric($a['category'])) {
                $res .= "\$params['cat_id'] = " . (int) $a['category'] . ";\n";
            } else {
                $res .= "\$params['cat_title'] = '" . $a['category'] . "';\n";
            }
        }

        $sort   = isset($a['sort'])  && $a['sort'] == 'asc' ? ' asc' : ' desc';
        $sortby = isset($a['order']) && in_array($a['order'], ['link_count','link_upddt','link_creadt','link_note','link_title']) ? $a['order'] : 'link_upddt';

        $res .= "\$params['order'] = '" . $sortby . $sort . "';\n";

        return
        "<?php \n" .
        "\$params = is_array(dcCore::app()->ctx->c2_page_params) ? dcCore::app()->ctx->c2_page_params : array(); \n" .
        $res .
        "dcCore::app()->ctx->c2_params = \$params; unset(\$params);\n" .
        "if (!dcCore::app()->ctx->exists('cinecturlink')) { dcCore::app()->ctx->cinecturlink = new cinecturlink2(); } \n" .
        "dcCore::app()->ctx->c2_entries = dcCore::app()->ctx->cinecturlink->getLinks(dcCore::app()->ctx->c2_params); \n" .
        'while (dcCore::app()->ctx->c2_entries->fetch()) : ?>' . $c . '<?php endwhile; ' . "\n" .
        "dcCore::app()->ctx->pop('c2_entries'); dcCore::app()->ctx->pop('c2_params'); \n" .
        "?>\n";
    }

    public static function c2EntriesHeader($a, $c)
    {
        return '<?php if (dcCore::app()->ctx->c2_entries->isStart()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2EntriesFooter($a, $c)
    {
        return '<?php if (dcCore::app()->ctx->c2_entries->isEnd()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2EntryIf($a, $c)
    {
        $if = [];

        $operator = isset($a['operator']) ? self::getOperator($a['operator']) : '&&';

        if (isset($a['has_category'])) {
            $sign = (bool) $a['has_category'] ? '!' : '=';
            $if[] = '(dcCore::app()->ctx->exists("c2_entries") && "" ' . $sign . '= dcCore::app()->ctx->c2_entries->cat_title)';
        }

        return empty($if) ? $c : '<?php if(' . implode(' ' . $operator . ' ', $if) . ") : ?>\n" . $c . "<?php endif; ?>\n";
    }

    public static function c2EntryIfFirst($a)
    {
        return '<?php if (dcCore::app()->ctx->c2_entries->index() == 0) { echo "' . (isset($a['return']) ? addslashes(html::escapeHTML($a['return'])) : 'first') . '"; } ?>';
    }

    public static function c2EntryIfOdd($a)
    {
        return '<?php if ((dcCore::app()->ctx->c2_entries->index()+1)%2 == 1) { echo "' . (isset($a['return']) ? addslashes(html::escapeHTML($a['return'])) : 'odd') . '"; } ?>';
    }

    public static function c2EntryFeedID($a)
    {
        return 'urn:md5:<?php echo md5(dcCore::app()->ctx->c2_entries->blog_id.dcCore::app()->ctx->c2_entries->link_id.dcCore::app()->ctx->c2_entries->link_creadt); ?>';
    }

    public static function c2EntryID($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_id', $a);
    }

    public static function c2EntryTitle($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_title', $a);
    }

    public static function c2EntryDescription($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_desc', $a);
    }

    public static function c2EntryAuthorCommonName($a)
    {
        return self::getGenericValue('dcUtils::getUserCN(dcCore::app()->ctx->c2_entries->user_id,dcCore::app()->ctx->c2_entries->user_name,dcCore::app()->ctx->c2_entries->user_firstname,dcCore::app()->ctx->c2_entries->user_displayname)', $a);
    }

    public static function c2EntryAuthorDisplayName($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->user_displayname', $a);
    }

    public static function c2EntryAuthorID($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->user_id', $a);
    }

    public static function c2EntryAuthorEmail($a)
    {
        return self::getGenericValue((isset($a['spam_protected']) && !$a['spam_protected'] ? 'dcCore::app()->ctx->c2_entries->user_email' : "strtr(dcCore::app()->ctx->c2_entries->user_email,array('@'=>'%40','.'=>'%2e'))"), $a);
    }

    public static function c2EntryAuthorLink($a)
    {
        return self::getGenericValue('sprintf((dcCore::app()->ctx->c2_entries->user_url ? \'<a href="%2$s">%1$s</a>\' : \'%1$s\'),html::escapeHTML(dcUtils::getUserCN(dcCore::app()->ctx->c2_entries->user_id,dcCore::app()->ctx->c2_entries->user_name,dcCore::app()->ctx->c2_entries->user_firstname,dcCore::app()->ctx->c2_entries->user_displayname)),html::escapeHTML(dcCore::app()->ctx->c2_entries->user_url))', $a);
    }

    public static function c2EntryAuthorURL($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->user_url', $a);
    }

    public static function c2EntryFromAuthor($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_author', $a);
    }

    public static function c2EntryLang($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_lang', $a);
    }

    public static function c2EntryURL($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_url', $a);
    }

    public static function c2EntryCategory($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->cat_title', $a);
    }

    public static function c2EntryCategoryID($a)
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->cat_id', $a);
    }

    public static function c2EntryCategoryURL($a)
    {
        return self::getGenericValue('dcCore::app()->blog->url.dcCore::app()->url->getBase("cinecturlink2")."/".dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_caturl."/".urlencode(dcCore::app()->ctx->c2_entries->cat_title)', $a);
    }

    public static function c2EntryImg($a)
    {
        $f     = dcCore::app()->tpl->getFilters($a);
        $style = isset($a['style']) ? html::escapeHTML($a['style']) : '';

        return
        "<?php if (dcCore::app()->ctx->exists('c2_entries')) { " .
        '$widthmax = (integer) dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_widthmax; ' .
        "\$img = sprintf('<img src=\"%s\" alt=\"%s\" %s/>'," .
        'dcCore::app()->ctx->c2_entries->link_img, ' .
        "html::escapeHTML(dcCore::app()->ctx->c2_entries->link_title.' - '.dcCore::app()->ctx->c2_entries->link_author), " .
        "(\$widthmax ? ' style=\"width:'.\$widthmax.'px;$style\"' : '') " .
        '); ' .
        'echo ' . sprintf($f, '$img') . "; unset(\$img); } ?> \n";
    }

    public static function c2EntryDate($a)
    {
        $format = !empty($a['format']) ? addslashes($a['format']) : '';

        if (!empty($a['rfc822'])) {
            $p = 'dt::rfc822(strtotime(dcCore::app()->ctx->c2_entries->link_creadt), dcCore::app()->blog->settings->system->blog_timezone)';
        } elseif (!empty($a['iso8601'])) {
            $p = 'dt::iso8601(strtotime(dcCore::app()->ctx->c2_entries->link_creadt), dcCore::app()->blog->settings->system->blog_timezone)';
        } elseif ($format) {
            $p = "dt::dt2str('" . $format . "', dcCore::app()->ctx->c2_entries->link_creadt)";
        } else {
            $p = 'dt::dt2str(dcCore::app()->blog->settings->system->date_format, dcCore::app()->ctx->c2_entries->link_creadt)';
        }

        return self::getGenericValue($p, $a);
    }

    public static function c2EntryTime($a)
    {
        return self::getGenericValue('dt::dt2str(' . (!empty($a['format']) ? "'" . addslashes($a['format']) . "'" : 'dcCore::app()->blog->settings->system->time_format') . ', dcCore::app()->ctx->c2_entries->link_creadt)', $a);
    }

    public static function c2Pagination($a, $c)
    {
        $p = "<?php\n" .
        "\$params = dcCore::app()->ctx->c2_params;\n" .
        "dcCore::app()->ctx->c2_pagination = dcCore::app()->ctx->cinecturlink->getLinks(\$params,true); unset(\$params);\n" .
        "?>\n";

        return isset($a['no_context']) ? $p . $c : $p . '<?php if (dcCore::app()->ctx->c2_pagination->f(0) > dcCore::app()->ctx->c2_entries->count()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2PaginationCounter($a)
    {
        return self::getGenericValue('cinecturlink2Context::PaginationNbPages()', $a);
    }

    public static function c2PaginationCurrent($a)
    {
        return self::getGenericValue('cinecturlink2Context::PaginationPosition(' . (isset($a['offset']) ? (int) $a['offset'] : 0) . ')', $a);
    }

    public static function c2PaginationIf($a, $c)
    {
        $if = [];

        if (isset($a['start'])) {
            $sign = (bool) $a['start'] ? '' : '!';
            $if[] = $sign . 'cinecturlink2Context::PaginationStart()';
        }
        if (isset($a['end'])) {
            $sign = (bool) $a['end'] ? '' : '!';
            $if[] = $sign . 'cinecturlink2Context::PaginationEnd()';
        }

        return empty($if) ? $c : '<?php if(' . implode(' && ', $if) . ') : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2PaginationURL($a)
    {
        return self::getGenericValue('cinecturlink2Context::PaginationURL(' . (isset($a['offset']) ? (int) $a['offset'] : 0) . ')', $a);
    }

    public static function c2Categories($a, $c)
    {
        return
        "<?php \n" .
        "if (!dcCore::app()->ctx->exists('cinecturlink')) { dcCore::app()->ctx->cinecturlink = new cinecturlink2(); } \n" .
        "dcCore::app()->ctx->c2_categories = dcCore::app()->ctx->cinecturlink->getCategories(); \n" .
        'while (dcCore::app()->ctx->c2_categories->fetch()) : ?>' . $c . '<?php endwhile; ' . "\n" .
        "dcCore::app()->ctx->c2_categories = null; \n" .
        "?>\n";
    }

    public static function c2CategoriesHeader($a, $c)
    {
        return '<?php if (dcCore::app()->ctx->c2_categories->isStart()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2CategoriesFooter($a, $c)
    {
        return '<?php if (dcCore::app()->ctx->c2_categories->isEnd()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2CategoryIf($a, $c)
    {
        $if = [];

        if (isset($a['current'])) {
            $sign = (bool) $a['current'] ? '' : '!';
            $if[] = $sign . 'cinecturlink2Context::CategoryCurrent()';
        }
        if (isset($a['first'])) {
            $sign = (bool) $a['first'] ? '' : '!';
            $if[] = $sign . 'dcCore::app()->ctx->c2_categories->isStart()';
        }

        return empty($if) ? $c : '<?php if(' . implode(' && ', $if) . ') : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2CategoryFeedURL($a)
    {
        $p = !empty($a['type']) ? $a['type'] : 'atom';

        if (!preg_match('#^(rss2|atom)$#', $p)) {
            $p = 'atom';
        }

        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->blog->url.dcCore::app()->url->getBase("cinecturlink2")."/".dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_caturl."/".urlencode(dcCore::app()->ctx->c2_categories->cat_title)."/feed/' . $p . '"') . '; ?>';
    }

    public static function c2CategoryFeedID($a)
    {
        return 'urn:md5:<?php echo md5(dcCore::app()->blog->id."cinecturlink2".dcCore::app()->ctx->c2_categories->cat_id); ?>';
    }

    public static function c2CategoryID($a)
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_categories')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->ctx->c2_categories->cat_id') . '; } ?>';
    }

    public static function c2CategoryTitle($a)
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_categories')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->ctx->c2_categories->cat_title') . '; } ?>';
    }

    public static function c2CategoryDescription($a)
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_categories')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->ctx->c2_categories->cat_desc') . '; } ?>';
    }

    public static function c2CategoryURL($a)
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_categories')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->blog->url.dcCore::app()->url->getBase("cinecturlink2")."/".dcCore::app()->blog->settings->cinecturlink2->cinecturlink2_public_caturl."/".urlencode(dcCore::app()->ctx->c2_categories->cat_title)') . '; } ?>';
    }

    protected static function getGenericValue($p, $a)
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_entries')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), "$p") . '; } ?>';
    }

    protected static function getOperator($op)
    {
        switch (strtolower($op)) {
            case 'or':
            case '||':
                return '||';
            case 'and':
            case '&&':
            default:
                return '&&';
        }
    }
}
