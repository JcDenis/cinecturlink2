<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Database\Cursor;
use Dotclear\Database\MetaRecord;

/**
 * @brief       cinecturlink2 record links row class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class RecordLinksRow
{
    public readonly int $link_id;
    public readonly string $link_title;
    public readonly string $link_desc;
    public readonly string $link_author;
    public readonly string $link_url;
    public readonly ?int $cat_id;
    public readonly string $cat_title;
    public readonly string $link_lang;
    public readonly string $link_img;
    public readonly string $link_note;
    public readonly string $link_upddt;
    public readonly int $link_count;

    public function __construct(?MetaRecord $rs = null)
    {
        $link_lang = is_string(App::auth()->getInfo('user_lang')) ? App::auth()->getInfo('user_lang') : '';

        $this->link_id     = isset($_REQUEST['link_id']) && is_numeric($_REQUEST['link_id']) ? (int) $_REQUEST['link_id'] : (!is_null($rs) ? $rs->intField('link_id') : 0);
        $this->link_title  = isset($_POST['link_title']) && is_string($_POST['link_title']) ? $_POST['link_title'] : (!is_null($rs) ? $rs->strField('link_title') : '');
        $this->link_desc   = isset($_POST['link_desc']) && is_string($_POST['link_desc']) ? $_POST['link_desc'] : (!is_null($rs) ? $rs->strField('link_desc') : '');
        $this->link_author = isset($_POST['link_author']) && is_string($_POST['link_author']) ? $_POST['link_author'] : (!is_null($rs) ? $rs->strField('link_author') : '');
        $this->link_url    = isset($_POST['link_url']) && is_string($_POST['link_url']) ? $_POST['link_url'] : (!is_null($rs) ? $rs->strField('link_url') : '');
        $this->cat_id      = isset($_POST['cat_id']) && is_numeric($_POST['cat_id']) ? (int) $_POST['cat_id'] : (!is_null($rs) ? $rs->intField('cat_id', true) : null);
        $this->cat_title   = isset($_POST['cat_title']) && is_string($_POST['cat_title']) ? $_POST['cat_title'] : (!is_null($rs) ? $rs->strField('cat_title') : '');
        $this->link_lang   = isset($_POST['link_lang']) && is_string($_POST['link_lang']) ? $_POST['link_lang'] : (!is_null($rs) ? $rs->strField('link_lang') : $link_lang);
        $this->link_img    = isset($_POST['link_img']) && is_string($_POST['link_img']) ? $_POST['link_img'] : (!is_null($rs) ? $rs->strField('link_img') : '');
        $this->link_note   = isset($_POST['link_note']) && is_string($_POST['link_note']) ? $_POST['link_note'] : (!is_null($rs) ? $rs->strField('link_note') : '');
        $this->link_upddt  = !is_null($rs) ? $rs->strField('link_upddt') : '';
        $this->link_count  = !is_null($rs) ? abs($rs->intField('link_count')) : 0;
    }

    public function getCursor(): Cursor
    {
        $cur = App::db()->con()->openCursor(App::db()->con()->prefix() . My::CINECTURLINK_TABLE_NAME);
        $cur->setField('link_title', $this->link_title);
        $cur->setField('link_desc', $this->link_desc);
        $cur->setField('link_author', $this->link_author);
        $cur->setField('link_url', $this->link_url);
        $cur->setField('cat_id', $this->cat_id);
        $cur->setField('link_lang', $this->link_lang);
        $cur->setField('link_img', $this->link_img);
        $cur->setField('link_note', $this->link_note);

        return $cur;
    }
}
