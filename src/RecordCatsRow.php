<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Database\Cursor;
use Dotclear\Database\MetaRecord;

/**
 * @brief       cinecturlink2 record categories row class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class RecordCatsRow
{
    public readonly int $cat_id;
    public readonly string $cat_title;
    public readonly string $cat_desc;
    public readonly int $cat_pos;

    public function __construct(?MetaRecord $rs = null)
    {
        $this->cat_id    = (int) ($rs?->field('cat_id') ?? $_REQUEST['cat_id'] ?? 0);
        $this->cat_title = (string) ($rs?->field('cat_title') ?? $_POST['cat_title'] ?? '');
        $this->cat_desc  = (string) ($rs?->field('cat_desc') ?? $_POST['cat_desc'] ?? '');
        $this->cat_pos   = (int) ($rs?->field('cat_pos') ?? 0);
    }

    public function getCursor(): Cursor
    {
        $cur = App::con()->openCursor(App::con()->prefix() . My::CATEGORY_TABLE_NAME);
        $cur->setField('cat_title', $this->cat_title);
        $cur->setField('cat_desc', $this->cat_desc);

        return $cur;
    }
}
