<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Core\Backend\{
    Notices,
    Page
};
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\{
    Checkbox,
    Div,
    Form,
    Hidden,
    Label,
    Link,
    Note,
    Number,
    Para,
    Submit,
    Table, Thead, Tbody, Th, Tr, Td, Caption
};
use Dotclear\Helper\Html\Html;
use Exception;

/**
 * @brief       cinecturlink2 manage categories class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ManageCats extends Process
{
    private static string $module_redir = '';

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE) && ($_REQUEST['part'] ?? 'links') == 'cats');
    }

    public static function process(): bool
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'cats') {
            return false;
        }

        self::$module_redir = $_REQUEST['redir'] ?? '';

        try {
            $utils = new Utils();

            // reorder categories
            if (!empty($_POST['save'])) {
                $catorder = [];
                if (empty($_POST['im_order']) && !empty($_POST['order'])) {
                    $catorder = $_POST['order'];
                    asort($catorder);
                    $catorder = array_keys($catorder);
                } elseif (!empty($_POST['im_order'])) {
                    $catorder = $_POST['im_order'];
                    if (substr($catorder, -1) == ',') {
                        $catorder = substr($catorder, 0, strlen($catorder) - 1);
                    }
                    $catorder = explode(',', $catorder);
                }
                $i = 0;
                foreach ($catorder as $id) {
                    $i++;
                    $cur = App::con()->openCursor($utils->cat_table);
                    $cur->setField('cat_pos', $i);
                    $utils->updCategory((int) $id, $cur);
                }
                Notices::addSuccessNotice(
                    __('Categories successfully reordered.')
                );
                My::redirect(['part' => 'cats']);
            }
            // delete categories
            if (!empty($_POST['delete']) && !empty($_POST['items_selected'])) {
                foreach ($_POST['items_selected'] as $id) {
                    $utils->delCategory((int) $id);
                }
                Notices::addSuccessNotice(
                    __('Categories successfully deleted.')
                );
                My::redirect(['part' => 'cats']);
            }
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'cats') {
            return;
        }

        $categories = (new Utils())->getCategories();

        $items = [];
        $i     = 0;
        while ($categories->fetch()) {
            $id = $categories->f('cat_id');

            $items[] = (new Tr('l_' . $i))
                ->class('line')
                ->items([
                    (new Td())
                        ->class('handle minimal')
                        ->items([
                            (new Number(['order[' . $id . ']']))
                                ->min(1)
                                ->max($categories->count())
                                ->value($i + 1)
                                ->class('position')
                                ->title(Html::escapeHTML(sprintf(__('position of %s'), (string) $categories->f('cat_title')))),
                            (new Hidden(['dynorder[]', 'dynorder-' . $i], $id)),
                        ]),
                    (new Td())
                        ->class('minimal')
                        ->items([
                            (new Checkbox(['items_selected[]', 'ims-' . $i]))
                                ->value($id),
                        ]),
                    (new Td())
                        ->class('nowrap')
                        ->items([
                            (new Link())
                                ->href(My::manageUrl([
                                    'part'  => 'cat',
                                    'catid' => $id,
                                    'redir' => My::manageUrl([
                                        'part'  => 'cats',
                                        'redir' => self::$module_redir,
                                    ]),
                                ]))
                                ->title(__('Edit'))
                                ->text(Html::escapeHTML((string) $categories->f('cat_title'))),
                        ]),
                    (new Td())
                        ->class('maximal')
                        ->text(Html::escapeHTML((string) $categories->f('cat_desc'))),
                ]);

            $i++;
        }

        Page::openModule(
            My::name(),
            (!App::auth()->prefs()->get('accessibility')->get('nodragdrop') ?
                Page::jsLoad('js/jquery/jquery-ui.custom.js') .
                Page::jsLoad('js/jquery/jquery.ui.touch-punch.js') .
                My::jsLoad('c2cats')
                : '')
        );

        echo
        Page::breadcrumb([
            __('Plugins')    => '',
            My::name()       => My::manageUrl(),
            __('Categories') => '',
        ]) .
        Notices::getNotices();

        if (!empty(self::$module_redir)) {
            echo (new Para())
                ->items([
                    (new Link())
                        ->class('back')
                        ->href(self::$module_redir)
                        ->text(__('Back')),
                ])
                ->render();
        }

        echo (new Para())
            ->class('top-add')
            ->separator(' ')
            ->items([
                (new Link())
                    ->class('button add')
                    ->href(My::manageUrl(['part' => 'cat', 'redir' => My::manageUrl(['part' => 'cats'])]))
                    ->text(__('New Link')),
            ])
            ->render();

        if ($categories->isEmpty()) {
            echo (new Note())
                ->class('info')
                ->text(__('There is no category'))
                ->render();
        } else {
            echo (new Div())
                ->items([
                    (new Form('c2items'))
                        ->action(My::manageUrl())
                        ->method('post')
                        ->fields([
                            (new Div())
                                ->class('table-outer')
                                ->items([
                                    (new Table())
                                        ->class('dragable')
                                        ->items([
                                            (new Thead())
                                                ->items([
                                                    (new Caption(__('Categories list'))),
                                                    (new Tr())
                                                        ->items([
                                                            (new Th())
                                                                ->text(__('Name'))
                                                                ->scope('col')
                                                                ->colspan(3),
                                                            (new Th())
                                                                ->text(__('Description'))
                                                                ->scope('col'),
                                                        ]),
                                                ]),
                                            (new Tbody('c2itemslist'))
                                                ->items($items),
                                        ]),
                                ]),
                            (new Note())
                                ->class('form-note')
                                ->text(__('Check to delete')),
                            (new Para())
                                ->class('border-top')
                                ->separator(' ')
                                ->items([
                                    (new Submit('save'))
                                        ->value(__('Save order') . ' (s)')
                                        ->accesskey('s'),
                                    (new Link())
                                        ->class('button')
                                        ->href(My::manageUrl(['part' => 'cats']))
                                        ->title(__('Cancel'))
                                        ->text(__('Cancel') . ' (c)')
                                        ->accesskey('c'),
                                    (new Submit('delete'))
                                        ->class('delete')
                                        ->value(__('Delete') . ' (d)')
                                        ->accesskey('d'),
                                    ... My::hiddenFields([
                                        'im_order' => '',
                                        'part'     => 'cats',
                                        'redir'    => self::$module_redir,
                                    ]),
                                ]),

                        ]),
                ])
                ->render();
        }

        Page::helpBlock(My::id());

        Page::closeModule();
    }
}
