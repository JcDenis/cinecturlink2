<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\Plugin\widgets\WidgetsElement;

/**
 * @brief       cinecturlink2 widget categories descriptor class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class WidgetCatsDescriptor
{
    public readonly string $title;
    public readonly string $class;
    public readonly bool $content_only;

    public readonly bool $shownumlink;

    public function __construct(WidgetsElement $w)
    {
        $this->title        = (string) $w->get('title');
        $this->class        = (string) $w->get('class');
        $this->content_only = !empty($w->get('content_only'));

        $this->shownumlink = !empty($w->get('shownumlink'));
    }
}
