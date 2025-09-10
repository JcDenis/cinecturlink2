<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Input;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Note;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Html;
use Exception;

/**
 * @brief       cinecturlink2 manage category class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ManageCat
{
    use TraitProcess;

    private static string $module_redir = '';
    private static RecordCatsRow $row;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE) && ($_REQUEST['part'] ?? 'links') == 'cat');
    }

    public static function process(): bool
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'cat') {
            return false;
        }

        self::$module_redir = $_REQUEST['redir'] ?? '';
        self::$row          = new RecordCatsRow();
        $utils              = new Utils();

        try {
            // create category
            if (!empty($_POST['save']) && empty(self::$row->cat_id) && !empty(self::$row->cat_title) && !empty(self::$row->cat_desc)) {
                $exists = $utils->getCategories(['cat_title' => self::$row->cat_title], true)->f(0);
                if ($exists) {
                    throw new Exception(__('Category with same name already exists.'));
                }
                $cat_id = $utils->addCategory(self::$row->getCursor());

                Notices::addSuccessNotice(
                    __('Category successfully created.')
                );
                My::redirect(['part' => 'cats']);
            }
            // update category
            if (!empty($_POST['save']) && !empty(self::$row->cat_id) && !empty(self::$row->cat_title) && !empty(self::$row->cat_desc)) {
                $exists = $utils->getCategories(['cat_title' => self::$row->cat_title, 'exclude_cat_id' => self::$row->cat_id], true)->f(0);
                if ($exists) {
                    throw new Exception(__('Category with same name already exists.'));
                }
                $cat_id = $utils->updCategory(self::$row->cat_id, self::$row->getCursor());

                Notices::addSuccessNotice(
                    __('Category successfully updated.')
                );
                My::redirect(['part' => 'cats']);
            }
            // delete category
            if (!empty($_POST['delete']) && !empty(self::$row->cat_id)) {
                $utils->delCategory(self::$row->cat_id);

                Notices::addSuccessNotice(
                    __('Category successfully deleted.')
                );
                My::redirect(['part' => 'cats']);
            }

            if (self::$row->cat_id) {
                self::$row = new RecordCatsRow(
                    $utils->getCategories(['cat_id' => self::$row->cat_id])
                );
            }
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'cat') {
            return;
        }

        $utils = new Utils();

        Page::openModule(My::name());

        echo
        Page::breadcrumb([
            __('Plugins')                                                          => '',
            My::name()                                                             => My::manageUrl(),
            (empty(self::$row->cat_id) ? __('New category') : __('Edit category')) => '',
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

        if (self::$row->cat_id) {
            $links = (int) $utils->getLinks(['cat_id' => self::$row->cat_id], true)->f(0);
            echo (new Note())
                ->class('info')
                ->text(
                    empty($links) ?
                    __('No link uses this category.') :
                    sprintf(__('A link uses this category.', '%s links use this category.', $links), $links)
                )
                ->render();
        }

        echo (new Form('newcatform'))
            ->method('post')
            ->action(My::manageUrl())
            ->fields([
                (new Para())
                    ->items([
                        (new Label(__('Title:'), Label::OUTSIDE_LABEL_BEFORE))
                            ->for('cat_title'),
                        (new Input('cat_title'))
                            ->size(65)
                            ->maxlength(64)
                            ->value(Html::escapeHTML(self::$row->cat_title)),
                    ]),
                (new Para())
                    ->items([
                        (new Label(__('Description:'), Label::OUTSIDE_LABEL_BEFORE))
                            ->for('cat_desc'),
                        (new Input('cat_desc'))
                            ->size(65)
                            ->maxlength(64)
                            ->value(Html::escapeHTML(self::$row->cat_desc)),
                    ]),
                (new Para())
                    ->class('border-top')
                    ->separator(' ')
                    ->items([
                        (new Submit('save'))
                            ->value(__('Save') . ' (s)')
                            ->accesskey('s'),
                        (new Link())
                            ->class('button')
                            ->href(My::manageUrl(['part' => 'links']))
                            ->title(__('Cancel'))
                            ->text(__('Cancel') . ' (c)')
                            ->accesskey('c'),
                        (new Submit('delete'))
                            ->class('delete')
                            ->value(__('Delete') . ' (d)')
                            ->accesskey('d'),
                        ... My::hiddenFields([
                            'cat_id' => self::$row->cat_id,
                            'part'   => 'cat',
                            'redir'  => self::$module_redir,
                        ]),
                    ]),
            ])
            ->render();

        Page::helpBlock(My::id());

        Page::closeModule();
    }
}
