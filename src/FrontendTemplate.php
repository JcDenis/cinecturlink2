<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Frontend\Tpl;
use Dotclear\Helper\Html\Html;

/**
 * @brief       cinecturlink2 frontend template class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class FrontendTemplate
{
    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function disable(ArrayObject $a, ?string $c = null): string
    {
        return '';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2PageURL(ArrayObject $a): string
    {
        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($a), 'App::blog()->url().App::url()->getBase(\'cinecturlink2\')') . '; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2PageTitle(ArrayObject $a): string
    {
        return "<?php \$title = (string) App::blog()->settings()->cinecturlink2->public_title; if (empty(\$title)) { \$title = __('My cinecturlink'); } echo " . sprintf(App::frontend()->template()->getFilters($a), '$title') . '; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2PageFeedURL(ArrayObject $a): string
    {
        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($a), 'App::blog()->url().App::url()->getBase("' . My::id() . '")."/feed/' . (!empty($a['type']) && preg_match('#^(rss2|atom)$#', $a['type']) ? $a['type'] : 'atom') . '"') . '; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2PageFeedID(ArrayObject $a): string
    {
        return 'urn:md5:<?php echo md5(App::blog()->id()."' . My::id() . '"); ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2PageDescription(ArrayObject $a): string
    {
        return '<?php $description = (string) App::blog()->settings()->cinecturlink2->public_description; echo ' . sprintf(App::frontend()->template()->getFilters($a), '$description') . '; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2If(ArrayObject $a, string $c): string
    {
        $if = [];

        $operator = isset($a['operator']) ? Tpl::getOperator($a['operator']) : '&&';

        if (isset($a['request_link'])) {
            $sign = (bool) $a['request_link'] ? '' : '!';
            $if[] = $sign . '(isset(App::frontend()->context()->c2_page_params["link_id"]) || isset(App::frontend()->context()->c2_page_params["link_title"]))';
        }

        if (isset($a['request_cat'])) {
            $sign = (bool) $a['request_cat'] ? '' : '!';
            $if[] = $sign . '(isset(App::frontend()->context()->c2_page_params["cat_id"]) || isset(App::frontend()->context()->c2_page_params["cat_title"]))';
        }

        return empty($if) ? $c : '<?php if(' . implode(' ' . $operator . ' ', $if) . ") : ?>\n" . $c . "<?php endif; ?>\n";
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
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
        "\$params = is_array(App::frontend()->context()->c2_page_params) ? App::frontend()->context()->c2_page_params : array(); \n" .
        $res .
        "App::frontend()->context()->c2_params = \$params; unset(\$params);\n" .
        "if (!App::frontend()->context()->exists('cinecturlink')) { App::frontend()->context()->cinecturlink = new " . Utils::class . "(); } \n" .
        "App::frontend()->context()->c2_entries = App::frontend()->context()->cinecturlink->getLinks(App::frontend()->context()->c2_params); \n" .
        'while (App::frontend()->context()->c2_entries->fetch()) : ?>' . $c . '<?php endwhile; ' . "\n" .
        "App::frontend()->context()->pop('c2_entries'); App::frontend()->context()->pop('c2_params'); \n" .
        "?>\n";
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntriesHeader(ArrayObject $a, string $c): string
    {
        return '<?php if (App::frontend()->context()->c2_entries->isStart()) : ?>' . $c . '<?php endif; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntriesFooter(ArrayObject $a, string $c): string
    {
        return '<?php if (App::frontend()->context()->c2_entries->isEnd()) : ?>' . $c . '<?php endif; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryIf(ArrayObject $a, string $c): string
    {
        $if = [];

        $operator = isset($a['operator']) ? Tpl::getOperator($a['operator']) : '&&';

        if (isset($a['has_category'])) {
            $sign = (bool) $a['has_category'] ? '!' : '=';
            $if[] = '(App::frontend()->context()->exists("c2_entries") && "" ' . $sign . '= App::frontend()->context()->c2_entries->cat_title)';
        }

        return empty($if) ? $c : '<?php if(' . implode(' ' . $operator . ' ', $if) . ") : ?>\n" . $c . "<?php endif; ?>\n";
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryIfFirst(ArrayObject $a): string
    {
        return '<?php if (App::frontend()->context()->c2_entries->index() == 0) { echo "' . (isset($a['return']) ? addslashes(Html::escapeHTML($a['return'])) : 'first') . '"; } ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryIfOdd(ArrayObject $a): string
    {
        return '<?php if ((App::frontend()->context()->c2_entries->index()+1)%2 == 1) { echo "' . (isset($a['return']) ? addslashes(Html::escapeHTML($a['return'])) : 'odd') . '"; } ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryFeedID(ArrayObject $a): string
    {
        return 'urn:md5:<?php echo md5(App::frontend()->context()->c2_entries->blog_id.App::frontend()->context()->c2_entries->link_id.App::frontend()->context()->c2_entries->link_creadt); ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryID(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->link_id', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryTitle(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->link_title', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryDescription(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->link_desc', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryAuthorCommonName(ArrayObject $a): string
    {
        return self::getGenericValue('App::users()->getUserCN(App::frontend()->context()->c2_entries->user_id,App::frontend()->context()->c2_entries->user_name,App::frontend()->context()->c2_entries->user_firstname,App::frontend()->context()->c2_entries->user_displayname)', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryAuthorDisplayName(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->user_displayname', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryAuthorID(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->user_id', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryAuthorEmail(ArrayObject $a): string
    {
        return self::getGenericValue((isset($a['spam_protected']) && !$a['spam_protected'] ? 'App::frontend()->context()->c2_entries->user_email' : "strtr(App::frontend()->context()->c2_entries->user_email,array('@'=>'%40','.'=>'%2e'))"), $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryAuthorLink(ArrayObject $a): string
    {
        return self::getGenericValue('sprintf((App::frontend()->context()->c2_entries->user_url ? \'<a href="%2$s">%1$s</a>\' : \'%1$s\'),html::escapeHTML(App::users()->getUserCN(App::frontend()->context()->c2_entries->user_id,App::frontend()->context()->c2_entries->user_name,App::frontend()->context()->c2_entries->user_firstname,App::frontend()->context()->c2_entries->user_displayname)),html::escapeHTML(App::frontend()->context()->c2_entries->user_url))', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryAuthorURL(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->user_url', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryFromAuthor(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->link_author', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryLang(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->link_lang', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryURL(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->link_url', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryCategory(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->cat_title', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryCategoryID(ArrayObject $a): string
    {
        return self::getGenericValue('App::frontend()->context()->c2_entries->cat_id', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryCategoryURL(ArrayObject $a): string
    {
        return self::getGenericValue('App::blog()->url().App::url()->getBase("' . My::id() . '")."/".App::blog()->settings()->cinecturlink2->public_caturl."/".urlencode(App::frontend()->context()->c2_entries->cat_title)', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryImg(ArrayObject $a): string
    {
        $f     = App::frontend()->template()->getFilters($a);
        $style = isset($a['style']) ? Html::escapeHTML($a['style']) : '';

        return
        "<?php if (App::frontend()->context()->exists('c2_entries')) { " .
        '$widthmax = (integer) App::blog()->settings()->cinecturlink2->widthmax; ' .
        "\$img = sprintf('<img src=\"%s\" alt=\"%s\" %s/>'," .
        'App::frontend()->context()->c2_entries->link_img, ' .
        "html::escapeHTML(App::frontend()->context()->c2_entries->link_title.' - '.App::frontend()->context()->c2_entries->link_author), " .
        "(\$widthmax ? ' style=\"width:'.\$widthmax.'px;$style\"' : '') " .
        '); ' .
        'echo ' . sprintf($f, '$img') . "; unset(\$img); } ?> \n";
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryDate(ArrayObject $a): string
    {
        $format = !empty($a['format']) ? addslashes($a['format']) : '';

        if (!empty($a['rfc822'])) {
            $p = 'dt::rfc822(strtotime(App::frontend()->context()->c2_entries->link_creadt), App::blog()->settings()->system->blog_timezone)';
        } elseif (!empty($a['iso8601'])) {
            $p = 'dt::iso8601(strtotime(App::frontend()->context()->c2_entries->link_creadt), App::blog()->settings()->system->blog_timezone)';
        } elseif ($format) {
            $p = "dt::dt2str('" . $format . "', App::frontend()->context()->c2_entries->link_creadt)";
        } else {
            $p = 'dt::dt2str(App::blog()->settings()->system->date_format, App::frontend()->context()->c2_entries->link_creadt)';
        }

        return self::getGenericValue($p, $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2EntryTime(ArrayObject $a): string
    {
        return self::getGenericValue('dt::dt2str(' . (!empty($a['format']) ? "'" . addslashes($a['format']) . "'" : 'App::blog()->settings()->system->time_format') . ', App::frontend()->context()->c2_entries->link_creadt)', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2Pagination(ArrayObject $a, string $c): string
    {
        $p = "<?php\n" .
        "\$params = App::frontend()->context()->c2_params;\n" .
        "App::frontend()->context()->c2_pagination = App::frontend()->context()->cinecturlink->getLinks(\$params,true); unset(\$params);\n" .
        "?>\n";

        return isset($a['no_context']) ? $p . $c : $p . '<?php if (App::frontend()->context()->c2_pagination->f(0) > App::frontend()->context()->c2_entries->count()) : ?>' . $c . '<?php endif; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2PaginationCounter(ArrayObject $a): string
    {
        return self::getGenericValue(FrontendContext::class . '::PaginationNbPages()', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2PaginationCurrent(ArrayObject $a): string
    {
        return self::getGenericValue(FrontendContext::class . '::PaginationPosition(' . (isset($a['offset']) ? (int) $a['offset'] : 0) . ')', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
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

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2PaginationURL(ArrayObject $a): string
    {
        return self::getGenericValue(FrontendContext::class . '::PaginationURL(' . (isset($a['offset']) ? (int) $a['offset'] : 0) . ')', $a);
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2Categories(ArrayObject $a, string $c): string
    {
        return
        "<?php \n" .
        "if (!App::frontend()->context()->exists('cinecturlink')) { App::frontend()->context()->cinecturlink = new " . Utils::class . "(); } \n" .
        "App::frontend()->context()->c2_categories = App::frontend()->context()->cinecturlink->getCategories(); \n" .
        'while (App::frontend()->context()->c2_categories->fetch()) : ?>' . $c . '<?php endwhile; ' . "\n" .
        "App::frontend()->context()->c2_categories = null; \n" .
        "?>\n";
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2CategoriesHeader(ArrayObject $a, string $c): string
    {
        return '<?php if (App::frontend()->context()->c2_categories->isStart()) : ?>' . $c . '<?php endif; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2CategoriesFooter(ArrayObject $a, string $c): string
    {
        return '<?php if (App::frontend()->context()->c2_categories->isEnd()) : ?>' . $c . '<?php endif; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2CategoryIf(ArrayObject $a, string $c): string
    {
        $if = [];

        if (isset($a['current'])) {
            $sign = (bool) $a['current'] ? '' : '!';
            $if[] = $sign . FrontendContext::class . '::CategoryCurrent()';
        }
        if (isset($a['first'])) {
            $sign = (bool) $a['first'] ? '' : '!';
            $if[] = $sign . 'App::frontend()->context()->c2_categories->isStart()';
        }

        return empty($if) ? $c : '<?php if(' . implode(' && ', $if) . ') : ?>' . $c . '<?php endif; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2CategoryFeedURL(ArrayObject $a): string
    {
        $p = !empty($a['type']) ? $a['type'] : 'atom';

        if (!preg_match('#^(rss2|atom)$#', $p)) {
            $p = 'atom';
        }

        return '<?php echo ' . sprintf(App::frontend()->template()->getFilters($a), 'App::blog()->url().App::url()->getBase("' . My::id() . '")."/".App::blog()->settings()->cinecturlink2->public_caturl."/".urlencode(App::frontend()->context()->c2_categories->cat_title)."/feed/' . $p . '"') . '; ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2CategoryFeedID(ArrayObject $a): string
    {
        return 'urn:md5:<?php echo md5(App::blog()->id()."' . My::id() . '".App::frontend()->context()->c2_categories->cat_id); ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2CategoryID(ArrayObject $a): string
    {
        return "<?php if (App::frontend()->context()->exists('c2_categories')) { echo " . sprintf(App::frontend()->template()->getFilters($a), 'App::frontend()->context()->c2_categories->cat_id') . '; } ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2CategoryTitle(ArrayObject $a): string
    {
        return "<?php if (App::frontend()->context()->exists('c2_categories')) { echo " . sprintf(App::frontend()->template()->getFilters($a), 'App::frontend()->context()->c2_categories->cat_title') . '; } ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2CategoryDescription(ArrayObject $a): string
    {
        return "<?php if (App::frontend()->context()->exists('c2_categories')) { echo " . sprintf(App::frontend()->template()->getFilters($a), 'App::frontend()->context()->c2_categories->cat_desc') . '; } ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    public static function c2CategoryURL(ArrayObject $a): string
    {
        return "<?php if (App::frontend()->context()->exists('c2_categories')) { echo " . sprintf(App::frontend()->template()->getFilters($a), 'App::blog()->url().App::url()->getBase("' . My::id() . '")."/".App::blog()->settings()->cinecturlink2->public_caturl."/".urlencode(App::frontend()->context()->c2_categories->cat_title)') . '; } ?>';
    }

    /**
     * @param      ArrayObject<string, mixed>  $a   The attributes
     */
    protected static function getGenericValue(string $p, ArrayObject $a): string
    {
        return "<?php if (App::frontend()->context()->exists('c2_entries')) { echo " . sprintf(App::frontend()->template()->getFilters($a), "$p") . '; } ?>';
    }
}
