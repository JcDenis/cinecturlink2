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
        $this->cat_id    = isset($_REQUEST['cat_id']) && is_numeric($_REQUEST['cat_id']) ? (int) $_REQUEST['cat_id'] : (!is_null($rs) ? $rs->intField('cat_id') : 0);
        $this->cat_title = isset($_POST['cat_title']) && is_string($_POST['cat_title']) ? $_POST['cat_title'] : (!is_null($rs) ? $rs->strField('cat_title') : '');
        $this->cat_desc  = isset($_POST['cat_desc']) && is_string($_POST['cat_desc']) ? $_POST['cat_desc'] : (!is_null($rs) ? $rs->strField('cat_desc') : '');
        $this->cat_pos   = !is_null($rs) ? $rs->intField('cat_pos') : 0;
    }

    public function getCursor(): Cursor
    {
        $cur = App::db()->con()->openCursor(App::db()->con()->prefix() . My::CATEGORY_TABLE_NAME);
        $cur->setField('cat_title', $this->cat_title);
        $cur->setField('cat_desc', $this->cat_desc);

        return $cur;
    }
}
