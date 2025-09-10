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
        $this->link_id     = (int) ($rs?->field('link_id') ?? $_REQUEST['link_id'] ?? 0);
        $this->link_title  = (string) ($rs?->field('link_title') ?? $_POST['link_title'] ?? '');
        $this->link_desc   = (string) ($rs?->field('link_desc') ?? $_POST['link_desc'] ?? '');
        $this->link_author = (string) ($rs?->field('link_author') ?? $_POST['link_author'] ?? '');
        $this->link_url    = (string) ($rs?->field('link_url') ?? $_POST['link_url'] ?? '');
        $this->cat_id      = $rs?->field('cat_id') ? (int) $rs->field('cat_id') : (isset($_POST['cat_id']) ? (int) $_POST['cat_id'] : null);
        $this->cat_title   = (string) ($rs?->field('cat_title') ?? $_POST['cat_title'] ?? '');
        $this->link_lang   = (string) ($rs?->field('link_lang') ?? $_POST['link_lang'] ?? App::auth()->getInfo('user_lang'));
        $this->link_img    = (string) ($rs?->field('link_img') ?? $_POST['link_img'] ?? '');
        $this->link_note   = (string) ($rs?->field('link_note') ?? $_POST['link_note'] ?? '');
        $this->link_upddt  = (string) ($rs?->field('link_upddt') ?? '');
        $this->link_count  = abs((int) $rs?->field('link_count'));
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
