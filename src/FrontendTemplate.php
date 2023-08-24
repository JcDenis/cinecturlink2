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

use ArrayObject;
use dcCore;
use dcTemplate;
use Dotclear\Helper\Html\Html;

class FrontendTemplate
{
    public static function disable(ArrayObject $a, ?string $c = null): string
    {
        return '';
    }

    public static function c2PageURL(ArrayObject $a): string
    {
        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->blog->url.dcCore::app()->url->getBase(\'cinecturlink2\')') . '; ?>';
    }

    public static function c2PageTitle(ArrayObject $a): string
    {
        return "<?php \$title = (string) dcCore::app()->blog->settings->cinecturlink2->public_title; if (empty(\$title)) { \$title = __('My cinecturlink'); } echo " . sprintf(dcCore::app()->tpl->getFilters($a), '$title') . '; ?>';
    }

    public static function c2PageFeedURL(ArrayObject $a): string
    {
        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->blog->url.dcCore::app()->url->getBase("' . My::id() . '")."/feed/' . (!empty($a['type']) && preg_match('#^(rss2|atom)$#', $a['type']) ? $a['type'] : 'atom') . '"') . '; ?>';
    }

    public static function c2PageFeedID(ArrayObject $a): string
    {
        return 'urn:md5:<?php echo md5(dcCore::app()->blog->id."' . My::id() . '"); ?>';
    }

    public static function c2PageDescription(ArrayObject $a): string
    {
        return '<?php $description = (string) dcCore::app()->blog->settings->cinecturlink2->public_description; echo ' . sprintf(dcCore::app()->tpl->getFilters($a), '$description') . '; ?>';
    }

    public static function c2If(ArrayObject $a, string $c): string
    {
        $if = [];

        $operator = isset($a['operator']) ? dcTemplate::getOperator($a['operator']) : '&&';

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

    public static function c2Entries(ArrayObject $a, string $c): string
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
        "if (!dcCore::app()->ctx->exists('cinecturlink')) { dcCore::app()->ctx->cinecturlink = new " . Utils::class . "(); } \n" .
        "dcCore::app()->ctx->c2_entries = dcCore::app()->ctx->cinecturlink->getLinks(dcCore::app()->ctx->c2_params); \n" .
        'while (dcCore::app()->ctx->c2_entries->fetch()) : ?>' . $c . '<?php endwhile; ' . "\n" .
        "dcCore::app()->ctx->pop('c2_entries'); dcCore::app()->ctx->pop('c2_params'); \n" .
        "?>\n";
    }

    public static function c2EntriesHeader(ArrayObject $a, string $c): string
    {
        return '<?php if (dcCore::app()->ctx->c2_entries->isStart()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2EntriesFooter(ArrayObject $a, string $c): string
    {
        return '<?php if (dcCore::app()->ctx->c2_entries->isEnd()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2EntryIf(ArrayObject $a, string $c): string
    {
        $if = [];

        $operator = isset($a['operator']) ? dcTemplate::getOperator($a['operator']) : '&&';

        if (isset($a['has_category'])) {
            $sign = (bool) $a['has_category'] ? '!' : '=';
            $if[] = '(dcCore::app()->ctx->exists("c2_entries") && "" ' . $sign . '= dcCore::app()->ctx->c2_entries->cat_title)';
        }

        return empty($if) ? $c : '<?php if(' . implode(' ' . $operator . ' ', $if) . ") : ?>\n" . $c . "<?php endif; ?>\n";
    }

    public static function c2EntryIfFirst(ArrayObject $a): string
    {
        return '<?php if (dcCore::app()->ctx->c2_entries->index() == 0) { echo "' . (isset($a['return']) ? addslashes(Html::escapeHTML($a['return'])) : 'first') . '"; } ?>';
    }

    public static function c2EntryIfOdd(ArrayObject $a): string
    {
        return '<?php if ((dcCore::app()->ctx->c2_entries->index()+1)%2 == 1) { echo "' . (isset($a['return']) ? addslashes(Html::escapeHTML($a['return'])) : 'odd') . '"; } ?>';
    }

    public static function c2EntryFeedID(ArrayObject $a): string
    {
        return 'urn:md5:<?php echo md5(dcCore::app()->ctx->c2_entries->blog_id.dcCore::app()->ctx->c2_entries->link_id.dcCore::app()->ctx->c2_entries->link_creadt); ?>';
    }

    public static function c2EntryID(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_id', $a);
    }

    public static function c2EntryTitle(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_title', $a);
    }

    public static function c2EntryDescription(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_desc', $a);
    }

    public static function c2EntryAuthorCommonName(ArrayObject $a): string
    {
        return self::getGenericValue('dcUtils::getUserCN(dcCore::app()->ctx->c2_entries->user_id,dcCore::app()->ctx->c2_entries->user_name,dcCore::app()->ctx->c2_entries->user_firstname,dcCore::app()->ctx->c2_entries->user_displayname)', $a);
    }

    public static function c2EntryAuthorDisplayName(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->user_displayname', $a);
    }

    public static function c2EntryAuthorID(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->user_id', $a);
    }

    public static function c2EntryAuthorEmail(ArrayObject $a): string
    {
        return self::getGenericValue((isset($a['spam_protected']) && !$a['spam_protected'] ? 'dcCore::app()->ctx->c2_entries->user_email' : "strtr(dcCore::app()->ctx->c2_entries->user_email,array('@'=>'%40','.'=>'%2e'))"), $a);
    }

    public static function c2EntryAuthorLink(ArrayObject $a): string
    {
        return self::getGenericValue('sprintf((dcCore::app()->ctx->c2_entries->user_url ? \'<a href="%2$s">%1$s</a>\' : \'%1$s\'),html::escapeHTML(dcUtils::getUserCN(dcCore::app()->ctx->c2_entries->user_id,dcCore::app()->ctx->c2_entries->user_name,dcCore::app()->ctx->c2_entries->user_firstname,dcCore::app()->ctx->c2_entries->user_displayname)),html::escapeHTML(dcCore::app()->ctx->c2_entries->user_url))', $a);
    }

    public static function c2EntryAuthorURL(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->user_url', $a);
    }

    public static function c2EntryFromAuthor(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_author', $a);
    }

    public static function c2EntryLang(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_lang', $a);
    }

    public static function c2EntryURL(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->link_url', $a);
    }

    public static function c2EntryCategory(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->cat_title', $a);
    }

    public static function c2EntryCategoryID(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->ctx->c2_entries->cat_id', $a);
    }

    public static function c2EntryCategoryURL(ArrayObject $a): string
    {
        return self::getGenericValue('dcCore::app()->blog->url.dcCore::app()->url->getBase("' . My::id() . '")."/".dcCore::app()->blog->settings->cinecturlink2->public_caturl."/".urlencode(dcCore::app()->ctx->c2_entries->cat_title)', $a);
    }

    public static function c2EntryImg(ArrayObject $a): string
    {
        $f     = dcCore::app()->tpl->getFilters($a);
        $style = isset($a['style']) ? Html::escapeHTML($a['style']) : '';

        return
        "<?php if (dcCore::app()->ctx->exists('c2_entries')) { " .
        '$widthmax = (integer) dcCore::app()->blog->settings->cinecturlink2->widthmax; ' .
        "\$img = sprintf('<img src=\"%s\" alt=\"%s\" %s/>'," .
        'dcCore::app()->ctx->c2_entries->link_img, ' .
        "html::escapeHTML(dcCore::app()->ctx->c2_entries->link_title.' - '.dcCore::app()->ctx->c2_entries->link_author), " .
        "(\$widthmax ? ' style=\"width:'.\$widthmax.'px;$style\"' : '') " .
        '); ' .
        'echo ' . sprintf($f, '$img') . "; unset(\$img); } ?> \n";
    }

    public static function c2EntryDate(ArrayObject $a): string
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

    public static function c2EntryTime(ArrayObject $a): string
    {
        return self::getGenericValue('dt::dt2str(' . (!empty($a['format']) ? "'" . addslashes($a['format']) . "'" : 'dcCore::app()->blog->settings->system->time_format') . ', dcCore::app()->ctx->c2_entries->link_creadt)', $a);
    }

    public static function c2Pagination(ArrayObject $a, string $c): string
    {
        $p = "<?php\n" .
        "\$params = dcCore::app()->ctx->c2_params;\n" .
        "dcCore::app()->ctx->c2_pagination = dcCore::app()->ctx->cinecturlink->getLinks(\$params,true); unset(\$params);\n" .
        "?>\n";

        return isset($a['no_context']) ? $p . $c : $p . '<?php if (dcCore::app()->ctx->c2_pagination->f(0) > dcCore::app()->ctx->c2_entries->count()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2PaginationCounter(ArrayObject $a): string
    {
        return self::getGenericValue(FrontendContext::class . '::PaginationNbPages()', $a);
    }

    public static function c2PaginationCurrent(ArrayObject $a): string
    {
        return self::getGenericValue(FrontendContext::class . '::PaginationPosition(' . (isset($a['offset']) ? (int) $a['offset'] : 0) . ')', $a);
    }

    public static function c2PaginationIf(ArrayObject $a, string $c): string
    {
        $if = [];

        if (isset($a['start'])) {
            $sign = (bool) $a['start'] ? '' : '!';
            $if[] = $sign . FrontendContext::class . '::PaginationStart()';
        }
        if (isset($a['end'])) {
            $sign = (bool) $a['end'] ? '' : '!';
            $if[] = $sign . FrontendContext::class . '::PaginationEnd()';
        }

        return empty($if) ? $c : '<?php if(' . implode(' && ', $if) . ') : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2PaginationURL($a): string
    {
        return self::getGenericValue(FrontendContext::class . '::PaginationURL(' . (isset($a['offset']) ? (int) $a['offset'] : 0) . ')', $a);
    }

    public static function c2Categories(ArrayObject $a, string $c): string
    {
        return
        "<?php \n" .
        "if (!dcCore::app()->ctx->exists('cinecturlink')) { dcCore::app()->ctx->cinecturlink = new " . Utils::class . "(); } \n" .
        "dcCore::app()->ctx->c2_categories = dcCore::app()->ctx->cinecturlink->getCategories(); \n" .
        'while (dcCore::app()->ctx->c2_categories->fetch()) : ?>' . $c . '<?php endwhile; ' . "\n" .
        "dcCore::app()->ctx->c2_categories = null; \n" .
        "?>\n";
    }

    public static function c2CategoriesHeader(ArrayObject $a, string $c): string
    {
        return '<?php if (dcCore::app()->ctx->c2_categories->isStart()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2CategoriesFooter(ArrayObject $a, string $c): string
    {
        return '<?php if (dcCore::app()->ctx->c2_categories->isEnd()) : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2CategoryIf(ArrayObject $a, string $c): string
    {
        $if = [];

        if (isset($a['current'])) {
            $sign = (bool) $a['current'] ? '' : '!';
            $if[] = $sign . FrontendContext::class . '::CategoryCurrent()';
        }
        if (isset($a['first'])) {
            $sign = (bool) $a['first'] ? '' : '!';
            $if[] = $sign . 'dcCore::app()->ctx->c2_categories->isStart()';
        }

        return empty($if) ? $c : '<?php if(' . implode(' && ', $if) . ') : ?>' . $c . '<?php endif; ?>';
    }

    public static function c2CategoryFeedURL(ArrayObject $a): string
    {
        $p = !empty($a['type']) ? $a['type'] : 'atom';

        if (!preg_match('#^(rss2|atom)$#', $p)) {
            $p = 'atom';
        }

        return '<?php echo ' . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->blog->url.dcCore::app()->url->getBase("' . My::id() . '")."/".dcCore::app()->blog->settings->cinecturlink2->public_caturl."/".urlencode(dcCore::app()->ctx->c2_categories->cat_title)."/feed/' . $p . '"') . '; ?>';
    }

    public static function c2CategoryFeedID(ArrayObject $a): string
    {
        return 'urn:md5:<?php echo md5(dcCore::app()->blog->id."' . My::id() . '".dcCore::app()->ctx->c2_categories->cat_id); ?>';
    }

    public static function c2CategoryID(ArrayObject $a): string
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_categories')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->ctx->c2_categories->cat_id') . '; } ?>';
    }

    public static function c2CategoryTitle(ArrayObject $a): string
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_categories')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->ctx->c2_categories->cat_title') . '; } ?>';
    }

    public static function c2CategoryDescription(ArrayObject $a): string
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_categories')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->ctx->c2_categories->cat_desc') . '; } ?>';
    }

    public static function c2CategoryURL(ArrayObject $a): string
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_categories')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), 'dcCore::app()->blog->url.dcCore::app()->url->getBase("' . My::id() . '")."/".dcCore::app()->blog->settings->cinecturlink2->public_caturl."/".urlencode(dcCore::app()->ctx->c2_categories->cat_title)') . '; } ?>';
    }

    protected static function getGenericValue(string $p, ArrayObject $a): string
    {
        return "<?php if (dcCore::app()->ctx->exists('c2_entries')) { echo " . sprintf(dcCore::app()->tpl->getFilters($a), "$p") . '; } ?>';
    }
}
