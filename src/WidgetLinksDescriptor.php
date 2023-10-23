<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @brief       cinecturlink2 widget links descriptor class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class WidgetLinksDescriptor
{
    public readonly string $title;
    public readonly string $class;
    public readonly bool $content_only;

    public readonly string $category;
    public readonly string $sortby;
    public readonly string $sort;
    public readonly int $limit;
    public readonly bool $shownote;
    public readonly bool $showdesc;
    public readonly bool $withlink;
    public readonly bool $showauthor;
    public readonly bool $showpagelink;

    public function __construct(WidgetsElement $w)
    {
        $this->title        = (string) $w->get('title');
        $this->class        = (string) $w->get('class');
        $this->content_only = !empty($w->get('content_only'));

        $this->category     = (string) $w->get('category');
        $this->sortby       = (string) $w->get('sortby');
        $this->sort         = $w->get('sort') == 'desc' ? 'desc' : 'asc';
        $this->limit        = abs((int) $w->get('limit'));
        $this->shownote     = !empty($w->get('shownote'));
        $this->showdesc     = !empty($w->get('showdesc'));
        $this->withlink     = !empty($w->get('withlink'));
        $this->showauthor   = !empty($w->get('showauthor'));
        $this->showpagelink = !empty($w->get('showpagelink'));
    }
}
