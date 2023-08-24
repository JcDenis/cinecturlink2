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

use dcCore;
use Dotclear\Core\Backend\{
    Notices,
    Page
};
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\{
    Form,
    Input,
    Label,
    Link,
    Note,
    Para,
    Submit
};
use Dotclear\Helper\Html\Html;
use Exception;

class ManageCat extends Process
{
    private static string $module_redir = '';
    private static int $catid           = 0;
    private static string $cattitle     = '';
    private static string $catdesc      = '';

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE) && ($_REQUEST['part'] ?? 'links') == 'cat');
    }

    public static function process(): bool
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'cat') {
            return false;
        }

        $utils = new Utils();

        self::$module_redir = $_REQUEST['redir'] ?? '';
        self::$catid        = (int) ($_REQUEST['catid'] ?? 0);
        self::$cattitle     = $_POST['cattitle'] ?? '';
        self::$catdesc      = $_POST['catdesc']  ?? '';

        try {
            // create category
            if (!empty($_POST['save']) && empty(self::$catid) && !empty(self::$cattitle) && !empty(self::$catdesc)) {
                $exists = $utils->getCategories(['cat_title' => self::$cattitle], true)->f(0);
                if ($exists) {
                    throw new Exception(__('Category with same name already exists.'));
                }
                $cur = dcCore::app()->con->openCursor($utils->cat_table);
                $cur->setField('cat_title', self::$cattitle);
                $cur->setField('cat_desc', self::$catdesc);

                $catid = $utils->addCategory($cur);

                Notices::addSuccessNotice(
                    __('Category successfully created.')
                );
                My::redirect(['part' => 'cats']);
            }
            // update category
            if (!empty($_POST['save']) && !empty(self::$catid) && !empty(self::$cattitle) && !empty(self::$catdesc)) {
                $exists = $utils->getCategories(['cat_title' => self::$cattitle, 'exclude_cat_id' => self::$catid], true)->f(0);
                if ($exists) {
                    throw new Exception(__('Category with same name already exists.'));
                }
                $cur = dcCore::app()->con->openCursor($C2->cat_table);
                $cur->setField('cat_title', self::$cattitle);
                $cur->setField('cat_desc', self::$catdesc);

                $utils->updCategory(self::$catid, $cur);

                Notices::addSuccessNotice(
                    __('Category successfully updated.')
                );
                My::redirect(['part' => 'cats']);
            }
            // delete category
            if (!empty($_POST['delete']) && !empty(self::$catid)) {
                $utils->delCategory(self::$catid);

                Notices::addSuccessNotice(
                    __('Category successfully deleted.')
                );
                My::redirect(['part' => 'cats']);
            }
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'cat') {
            return;
        }

        $utils = new Utils();

        if (!empty(self::$catid)) {
            $category = $utils->getCategories(['cat_id' => self::$catid]);
            if (!$category->isEmpty()) {
                self::$cattitle = (string) $category->f('cat_title');
                self::$catdesc  = (string) $category->f('cat_desc');
            }
        }

        Page::openModule(My::name());

        echo
        Page::breadcrumb([
            __('Plugins')                                                    => '',
            My::name()                                                       => My::manageUrl(),
            (empty(self::$catid) ? __('New category') : __('Edit category')) => '',
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

        if (self::$catid) {
            $links = (int) $utils->getLinks(['cat_id' => self::$catid], true)->f(0);
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
                            ->for('cattitle'),
                        (new Input('cattitle'))
                            ->size(65)
                            ->maxlenght(64)
                            ->value(Html::escapeHTML(self::$cattitle)),
                    ]),
                (new Para())
                    ->items([
                        (new Label(__('Description:'), Label::OUTSIDE_LABEL_BEFORE))
                            ->for('catdesc'),
                        (new Input('catdesc'))
                            ->size(65)
                            ->maxlenght(64)
                            ->value(Html::escapeHTML(self::$catdesc)),
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
                            'catid' => self::$catid,
                            'part'  => 'cat',
                            'redir' => self::$module_redir,
                        ]),
                    ]),
            ])
            ->render();

        Page::helpBlock(My::id());

        Page::closeModule();
    }
}
