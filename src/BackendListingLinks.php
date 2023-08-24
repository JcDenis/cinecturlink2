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
use Dotclear\Core\Backend\Filter\Filters;
use Dotclear\Core\Backend\Listing\{
    Listing,
    Pager
};
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Form\{
    Checkbox,
    Div,
    Link,
    Note,
    Para,
    Text,
    Table, Tbody, Th, Tr, Td, Caption
};
use Dotclear\Helper\Html\Html;

class BackendListingLinks extends Listing
{
    private string $redir;

    public function display(Filters $filter, string $enclose_block = '', string $redir = ''): void
    {
        if ($this->rs->isEmpty()) {
            echo (new Note())
                ->class('info')
                ->text($filter->show() ? __('No link matches the filter') : __('No link'))
                ->render();

            return;
        }

        $this->redir = $redir;
        $links       = [];
        if (isset($_REQUEST['entries'])) {
            foreach ($_REQUEST['entries'] as $v) {
                $links[(int) $v] = true;
            }
        }

        $pager = new Pager((int) $filter->value('page'), $this->rs_count, (int) $filter->value('nb'), 10);

        $cols = new ArrayObject([
            'title' => (new Th())
                ->text(__('Title'))
                ->class('first')
                ->colspan(2),
            'author' => (new Th())
                ->text(__('Author'))
                ->scope('col'),
            'desc' => (new Th())
                ->text(__('Description'))
                ->scope('col'),
            'link' => (new Th())
                ->text(__('Link'))
                ->scope('col'),
            'cat' => (new Th())
                ->text(__('Category'))
                ->scope('col'),
            'note' => (new Th())
                ->text(__('Rating'))
                ->scope('col'),
            'date' => (new Th())
                ->text(__('Date'))
                ->scope('col'),
        ]);

        $this->userColumns(My::id(), $cols);

        $lines = [];
        while ($this->rs->fetch()) {
            $lines[] = $this->linkLine(isset($links[$this->rs->link_id]));
        }

        echo
        $pager->getLinks() .
        sprintf(
            $enclose_block,
            (new Div())
                ->class('table-outer')
                ->items([
                    (new Table())
                        ->items([
                            (new Caption(
                                $filter->show() ?
                                sprintf(__('List of %s links matching the filter.'), $this->rs_count) :
                                sprintf(__('List of links. (%s)'), $this->rs_count)
                            )),
                            (new Tr())
                                ->items(iterator_to_array($cols)),
                            (new Tbody())
                                ->items($lines),
                        ]),
                ])
                ->render()
        ) .
        $pager->getLinks();
    }

    private function linkLine(bool $checked): Para
    {
        $cols = new ArrayObject([
            'check' => (new Td())
                ->class('nowrap minimal')
                ->items([
                    (new Checkbox(['entries[]'], $checked))
                        ->value($this->rs->link_id),
                ]),
            'title' => (new Td())
                ->class('maximal')
                ->items([
                    (new Link())
                        ->href(My::manageUrl(['part' => 'link', 'linkid' => $this->rs->link_id, 'redir' => $this->redir]))
                        ->title(__('Edit'))
                        ->text(Html::escapeHTML($this->rs->link_title)),
                ]),
            'author' => (new Td())
                ->text(Html::escapeHTML($this->rs->link_author))
                ->class('nowrap'),
            'desc' => (new Td())
                ->text(Html::escapeHTML($this->rs->link_desc))
                ->class('nowrap'),
            'link' => (new Text('td'))
                ->separator(' ')
                ->items([
                    (new Link())
                        ->href($this->rs->link_url)
                        ->title(__('URL'))
                        ->text(Html::escapeHTML($this->rs->link_title)),
                    (new Link())
                        ->href($this->rs->link_img)
                        ->title(__('image'))
                        ->text(Html::escapeHTML($this->rs->link_title)),
                ]),
            'cat' => (new Td())
                ->items([
                    (new Link())
                        ->href(My::manageUrl(['part' => 'cat', 'catid' => $this->rs->cat_id, 'redir' => $this->redir]))
                        ->title(__('Edit'))
                        ->text(Html::escapeHTML($this->rs->cat_title)),
                ]),
            'note' => (new Td())
                ->text(Html::escapeHTML($this->rs->link_note))
                ->class('number'),
            'date' => (new Td())
                ->text(Html::escapeHTML(Date::dt2str(__('%Y-%m-%d %H:%M'), $this->rs->link_upddt, (string) dcCore::app()->auth->getInfo('user_tz'))))
                ->class('nowrap'),
        ]);

        $this->userColumns(My::id(), $cols);

        return
        (new Para('p' . $this->rs->kut_id, 'tr'))
            ->class('line')
            ->items(iterator_to_array($cols));
    }
}
