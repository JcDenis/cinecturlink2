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

require_once dirname(__FILE__).'/_widgets.php';

$core->blog->settings->addNamespace('cinecturlink2');

$c2_tpl_values = array(
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
    'c2CategoryURL'
);

$c2_tpl_blocks = array(
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
    'c2CategoryIf'
);

if ($core->blog->settings->cinecturlink2->cinecturlink2_active) {

    foreach($c2_tpl_blocks as $v) {
        $core->tpl->addBlock($v, array('tplCinecturlink2', $v));
    }
    foreach($c2_tpl_values as $v) {
        $core->tpl->addValue($v, array('tplCinecturlink2', $v));
    }
}
else {

    foreach(array_merge($c2_tpl_blocks, $c2_tpl_values) as $v) {
        $core->tpl->addBlock($v, array('tplCinecturlink2', 'disable'));
    }
}

class urlCinecturlink2 extends dcUrlHandlers
{
    public static function c2Page($args)
    {
        global $core, $_ctx;
        $core->blog->settings->addNamespace('cinecturlink2');

        if (!$core->blog->settings->cinecturlink2->cinecturlink2_active 
         || !$core->blog->settings->cinecturlink2->cinecturlink2_public_active)
        {
            self::p404();

            return null;
        }

        $core->tpl->setPath(
            $core->tpl->getPath(),
            dirname(__FILE__).'/default-templates/'
        );

        $params = array();

        $n = self::getPageArgs($args, 'c2page');
        if ($n) {
            $GLOBALS['c2_page_number'] = $n;
        }

        $caturl = $core->blog->settings->cinecturlink2->cinecturlink2_public_caturl;
        if (!$caturl) $caturl = 'c2cat';

        $c = self::getPageArgs($args, $caturl);
        if ($c) {
            if (is_numeric($c)) {
                $params['cat_id'] = (integer) $c;
            }
            else {
                $params['cat_title'] = urldecode($c);
            }
        }

        $f = self::getPageArgs($args, 'feed');
        if ($f && in_array($f, array('atom', 'rss2'))) {
            $mime = $f == 'atom' ? 'application/atom+xml' : 'application/xml';

            //$_ctx->short_feed_items = $core->blog->settings->system->short_feed_items;

            $params['limit'] = $core->blog->settings->system->nb_post_per_feed;
            $_ctx->c2_page_params = $params;

            header('X-Robots-Tag: '.context::robotsPolicy($core->blog->settings->system->robots_policy, ''));
            self::serveDocument('cinecturlink2-'.$f.'.xml', $mime);
        }
        else {
            $d = self::getPageArgs($args, 'c2detail');
            if ($d) {
                if (is_numeric($d)) {
                    $params['link_id'] = (integer) $d;
                }
                else {
                    $params['link_title'] = urldecode($d);
                }
            }

            $params['limit'] = $core->blog->settings->cinecturlink2->cinecturlink2_public_nbrpp;
            $_ctx->c2_page_params = $params;

            self::serveDocument('cinecturlink2.html', 'text/html');
        }

        return null;
    }

    protected static function getPageArgs(&$args, $part)
    {
        if (preg_match('#(^|/)'.$part.'/([^/]+)#', $args, $m)) {
            $args = preg_replace('#(^|/)'.$part.'/([^/]+)#', '', $args);

            return $m[2];
        }

        return false;
    }
}

class tplCinecturlink2
{
    public static function disable($a, $c=null)
    {
        return '';
    }

    public static function c2PageURL($a)
    {
        return "<?php echo ".sprintf($GLOBALS['core']->tpl->getFilters($a), '$core->blog->url.$core->url->getBase(\'cinecturlink2\')')."; ?>";
    }

    public static function c2PageTitle($a)
    {
        return "<?php \$title = (string) \$core->blog->settings->cinecturlink2->cinecturlink2_public_title; if (empty(\$title)) { \$title = __('My cinecturlink'); } echo ".sprintf($GLOBALS['core']->tpl->getFilters($a), '$title')."; ?>";
    }

    public static function c2PageFeedURL($a)
    {
        return "<?php echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$core->blog->url.$core->url->getBase("cinecturlink2")."/feed/'.(!empty($a['type']) && preg_match('#^(rss2|atom)$#', $a['type']) ? $a['type'] : 'atom').'"')."; ?>";
    }

    public static function c2PageFeedID($a)
    {
        return 'urn:md5:<?php echo md5($core->blog->blog_id."cinecturlink2"); ?>';
    }

    public static function c2PageDescription($a)
    {
        return "<?php \$description = (string) \$core->blog->settings->cinecturlink2->cinecturlink2_public_description; echo ".sprintf($GLOBALS['core']->tpl->getFilters($a), '$description')."; ?>";
    }

    public static function c2If($a ,$c)
    {
        $if = array();

        $operator = isset($a['operator']) ? self::getOperator($a['operator']) : '&&';

        if (isset($a['request_link'])) {
            $sign = (boolean) $a['request_link'] ? '' : '!';
            $if[] = $sign.'(isset($_ctx->c2_page_params["link_id"]) || isset($_ctx->c2_page_params["link_title"]))';
        }

        if (isset($a['request_cat'])) {
            $sign = (boolean) $a['request_cat'] ? '' : '!';
            $if[] = $sign.'(isset($_ctx->c2_page_params["cat_id"]) || isset($_ctx->c2_page_params["cat_title"]))';
        }

        return empty($if) ? $c : "<?php if(".implode(' '.$operator.' ', $if).") : ?>\n".$c."<?php endif; ?>\n";
    }

    public static function c2Entries($a, $c)
    {
        $lastn = isset($a['lastn']) ? abs((integer) $a['lastn'])+0 : -1;

        $res = 'if (!isset($c2_page_number)) { $c2_page_number = 1; }'."\n";

        if ($lastn != 0) {
            if ($lastn > 0) {
                $res .= "\$params['limit'] = ".$lastn.";\n";
            }
            else  {
                $res .= "if (!isset(\$params['limit']) || \$params['limit'] < 1) { \$params['limit'] = 10; }\n";
            }
            if (!isset($a['ignore_pagination']) || $a['ignore_pagination'] == "0") {
                $res .= "\$params['limit'] = array(((\$c2_page_number-1)*\$params['limit']),\$params['limit']);\n";
            }
            else {
                $res .= "\$params['limit'] = array(0, \$params['limit']);\n";
            }
        }

        if (isset($a['category'])) {
            if ($a['category'] == 'null') {
                $res .= "\$params['sql'] = ' AND L.cat_id IS NULL ';\n";
            }
            elseif (is_numeric($a['category'])) {
                $res .= "\$params['cat_id'] = ".(integer) $a['category'].";\n";
            }
            else {
                $res .= "\$params['cat_title'] = '".$a['category']."';\n";
            }
        }

        $sort = isset($a['sort']) && $a['sort'] == 'asc' ? ' asc' : ' desc';
        $sortby = isset($a['order']) && in_array($a['order'],array('link_count','link_upddt','link_creadt','link_note','link_title')) ? $a['order'] : 'link_upddt';

        $res .= 
        "\$params['order'] = '".$sortby.$sort."';\n";

        return 
        "<?php \n".
        "\$params = is_array(\$_ctx->c2_page_params) ? \$_ctx->c2_page_params : array(); \n".
        $res.
        "\$_ctx->c2_params = \$params; unset(\$params);\n".
        "if (!\$_ctx->exists('cinecturlink')) { \$_ctx->cinecturlink = new cinecturlink2(\$core); } \n".
        "\$_ctx->c2_entries = \$_ctx->cinecturlink->getLinks(\$_ctx->c2_params); \n".
        'while ($_ctx->c2_entries->fetch()) : ?>'.$c.'<?php endwhile; '."\n".
        "\$_ctx->c2_entries = null; \$_ctx->c2_params = null; \n".
        "?>\n";
    }

    public static function c2EntriesHeader($a, $c)
    {
        return "<?php if (\$_ctx->c2_entries->isStart()) : ?>".$c."<?php endif; ?>";
    }

    public static function c2EntriesFooter($a, $c)
    {
        return "<?php if (\$_ctx->c2_entries->isEnd()) : ?>".$c."<?php endif; ?>";
    }

    public static function c2EntryIf($a, $c)
    {
        $if = array();

        $operator = isset($a['operator']) ? self::getOperator($a['operator']) : '&&';

        if (isset($a['has_category'])) {
            $sign = (boolean) $a['has_category'] ? '!' : '=';
            $if[] = '($_ctx->exists("c2_entries") && "" '.$sign.'= $_ctx->c2_entries->cat_title)';
        }

        return empty($if) ? $c : "<?php if(".implode(' '.$operator.' ',$if).") : ?>\n".$c."<?php endif; ?>\n";
    }

    public static function c2EntryIfFirst($a)
    {
        return '<?php if ($_ctx->c2_entries->index() == 0) { echo "'.(isset($a['return']) ? addslashes(html::escapeHTML($a['return'])) : 'first').'"; } ?>';
    }

    public static function c2EntryIfOdd($a)
    {
        return '<?php if (($_ctx->c2_entries->index()+1)%2 == 1) { echo "'.(isset($a['return']) ? addslashes(html::escapeHTML($a['return'])) : 'odd').'"; } ?>';
    }

    public static function c2EntryFeedID($a)
    {
        return 'urn:md5:<?php echo md5($_ctx->c2_entries->blog_id.$_ctx->c2_entries->link_id.$_ctx->c2_entries->link_dt); ?>';
    }

    public static function c2EntryID($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->link_id', $a);
    }

    public static function c2EntryTitle($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->link_title', $a);
    }

    public static function c2EntryDescription($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->link_desc', $a);
    }

    public static function c2EntryAuthorCommonName($a)
    {
        return self::getGenericValue('dcUtils::getUserCN($_ctx->c2_entries->user_id,$_ctx->c2_entries->user_name,$_ctx->c2_entries->user_firstname,$_ctx->c2_entries->user_displayname)', $a);
    }

    public static function c2EntryAuthorDisplayName($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->user_displayname', $a);
    }

    public static function c2EntryAuthorID($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->user_id', $a);
    }

    public static function c2EntryAuthorEmail($a)
    {
        return self::getGenericValue((isset($a['spam_protected']) && !$a['spam_protected'] ? '$_ctx->c2_entries->user_email' : "strtr(\$_ctx->c2_entries->user_email,array('@'=>'%40','.'=>'%2e'))"), $a);
    }

    public static function c2EntryAuthorLink($a)
    {
        return self::getGenericValue('sprintf(($_ctx->c2_entries->user_url ? \'<a href="%2$s">%1$s</a>\' : \'%1$s\'),html::escapeHTML(dcUtils::getUserCN($_ctx->c2_entries->user_id,$_ctx->c2_entries->user_name,$_ctx->c2_entries->user_firstname,$_ctx->c2_entries->user_displayname)),html::escapeHTML($_ctx->c2_entries->user_url))', $a);
    }

    public static function c2EntryAuthorURL($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->user_url', $a);
    }

    public static function c2EntryFromAuthor($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->link_author', $a);
    }

    public static function c2EntryLang($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->link_lang', $a);
    }

    public static function c2EntryURL($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->link_url', $a);
    }

    public static function c2EntryCategory($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->cat_title', $a);
    }

    public static function c2EntryCategoryID($a)
    {
        return self::getGenericValue('$_ctx->c2_entries->cat_id', $a);
    }

    public static function c2EntryCategoryURL($a)
    {
        return self::getGenericValue('$core->blog->url.$core->url->getBase("cinecturlink2")."/".$core->blog->settings->cinecturlink2->cinecturlink2_public_caturl."/".urlencode($_ctx->c2_entries->cat_title)', $a);
    }

    public static function c2EntryImg($a)
    {
        global $core;
        $f = $core->tpl->getFilters($a);
        $style = isset($a['style']) ? html::escapeHTML($a['style']) : '';

        return 
        "<?php if (\$_ctx->exists('c2_entries')) { ".
        "\$widthmax = (integer) \$core->blog->settings->cinecturlink2->cinecturlink2_widthmax; ".
        "\$img = sprintf('<img src=\"%s\" alt=\"%s\" %s/>',".
        "\$_ctx->c2_entries->link_img, ".
        "html::escapeHTML(\$_ctx->c2_entries->link_title.' - '.\$_ctx->c2_entries->link_author), ".
        "(\$widthmax ? ' style=\"width:'.\$widthmax.'px;$style\"' : '') ".
        "); ".
        "echo ".sprintf($f,'$img')."; unset(\$img); } ?> \n";
    }

    public static function c2EntryDate($a)
    {
        $format = !empty($a['format']) ? addslashes($a['format']) : '';

        if (!empty($a['rfc822']))
            $p = "dt::rfc822(strtotime(\$_ctx->c2_entries->link_creadt), \$_ctx->posts->post_tz)";
        elseif (!empty($a['iso8601']))
            $p = "dt::iso8601(strtotime(\$_ctx->c2_entries->link_creadt), \$_ctx->posts->post_tz)";
        elseif ($format)
            $p = "dt::dt2str('".$format."', \$_ctx->c2_entries->link_creadt)";
        else
            $p = "dt::dt2str(\$core->blog->settings->system->date_format, \$_ctx->c2_entries->link_creadt)";

        return self::getGenericValue($p, $a);
    }

    public static function c2EntryTime($a)
    {
        return self::getGenericValue("dt::dt2str(".(!empty($a['format']) ? "'".addslashes($a['format'])."'" : '$core->blog->settings->system->time_format').", \$_ctx->c2_entries->link_creadt)", $a);
    }

    public static function c2Pagination($a, $c)
    {
        $p = 
        "<?php\n".
        "\$params = \$_ctx->c2_params;\n".
        "\$_ctx->c2_pagination = \$_ctx->cinecturlink->getLinks(\$params,true); unset(\$params);\n".
        "?>\n";

        return isset($a['no_context']) ? $p.$c : $p.'<?php if ($_ctx->c2_pagination->f(0) > $_ctx->c2_entries->count()) : ?>'.$c.'<?php endif; ?>';
    }

    public static function c2PaginationCounter($a)
    {
        return self::getGenericValue('cinecturlink2Context::PaginationNbPages()', $a);
    }

    public static function c2PaginationCurrent($a)
    {
        return self::getGenericValue('cinecturlink2Context::PaginationPosition('.(isset($a['offset']) ? (integer) $a['offset'] : 0).')', $a);
    }

    public static function c2PaginationIf($a, $c)
    {
        $if = array();

        if (isset($a['start'])) {
            $sign = (boolean) $a['start'] ? '' : '!';
            $if[] = $sign.'cinecturlink2Context::PaginationStart()';
        }
        if (isset($a['end'])) {
            $sign = (boolean) $a['end'] ? '' : '!';
            $if[] = $sign.'cinecturlink2Context::PaginationEnd()';
        }

        return empty($if) ? $c : '<?php if('.implode(' && ', $if).') : ?>'.$c.'<?php endif; ?>';
    }

    public static function c2PaginationURL($a)
    {
        return self::getGenericValue('cinecturlink2Context::PaginationURL('.(isset($a['offset']) ? (integer) $a['offset'] : 0).')', $a);
    }

    public static function c2Categories($a, $c)
    {
        return 
        "<?php \n".
        "if (!\$_ctx->exists('cinecturlink')) { \$_ctx->cinecturlink = new cinecturlink2(\$core); } \n".
        "\$_ctx->c2_categories = \$_ctx->cinecturlink->getCategories(); \n".
        'while ($_ctx->c2_categories->fetch()) : ?>'.$c.'<?php endwhile; '."\n".
        "\$_ctx->c2_categories = null; \n".
        "?>\n";
    }

    public static function c2CategoriesHeader($a, $c)
    {
        return "<?php if (\$_ctx->c2_categories->isStart()) : ?>".$c."<?php endif; ?>";
    }

    public static function c2CategoriesFooter($a, $c)
    {
        return "<?php if (\$_ctx->c2_categories->isEnd()) : ?>".$c."<?php endif; ?>";
    }

    public static function c2CategoryIf($a, $c)
    {
        $if = array();

        if (isset($a['current'])) {
            $sign = (boolean) $a['current'] ? '' : '!';
            $if[] = $sign.'cinecturlink2Context::CategoryCurrent()';
        }
        if (isset($a['first'])) {
            $sign = (boolean) $a['first'] ? '' : '!';
            $if[] = $sign.'$_ctx->c2_categories->isStart()';
        }

        return empty($if) ? $c : '<?php if('.implode(' && ', $if).') : ?>'.$c.'<?php endif; ?>';
    }

    public static function c2CategoryFeedURL($a)
    {
        $p = !empty($a['type']) ? $a['type'] : 'atom';

        if (!preg_match('#^(rss2|atom)$#', $p)) {
            $p = 'atom';
        }

        return "<?php echo ".sprintf($GLOBALS['core']->tpl->getFilters($a),'$core->blog->url.$core->url->getBase("cinecturlink2")."/".$core->blog->settings->cinecturlink2->cinecturlink2_public_caturl."/".urlencode($_ctx->c2_categories->cat_title)."/feed/'.$p.'"')."; ?>";
    }

    public static function c2CategoryFeedID($a)
    {
        return 'urn:md5:<?php echo md5($core->blog->blog_id."cinecturlink2".$_ctx->c2_categories->cat_id); ?>';
    }

    public static function c2CategoryID($a)
    {
        return "<?php if (\$_ctx->exists('c2_categories')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a), '$_ctx->c2_categories->cat_id')."; } ?>";
    }

    public static function c2CategoryTitle($a)
    {
        return "<?php if (\$_ctx->exists('c2_categories')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a), '$_ctx->c2_categories->cat_title')."; } ?>";
    }

    public static function c2CategoryDescription($a)
    {
        return "<?php if (\$_ctx->exists('c2_categories')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a), '$_ctx->c2_categories->cat_desc')."; } ?>";
    }

    public static function c2CategoryURL($a)
    {
        return "<?php if (\$_ctx->exists('c2_categories')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a), '$core->blog->url.$core->url->getBase("cinecturlink2")."/".$core->blog->settings->cinecturlink2->cinecturlink2_public_caturl."/".urlencode($_ctx->c2_categories->cat_title)')."; } ?>";
    }

    protected static function getGenericValue($p,$a)
    {
        return "<?php if (\$_ctx->exists('c2_entries')) { echo ".sprintf($GLOBALS['core']->tpl->getFilters($a), "$p")."; } ?>";
    }

    protected static function getOperator($op)
    {
        switch (strtolower($op))
        {
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