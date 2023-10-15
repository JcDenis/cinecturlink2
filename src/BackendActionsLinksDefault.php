<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Backend\{
    Notices,
    Page
};
use Dotclear\Helper\Html\Form\{
    Form,
    Hidden,
    Label,
    Para,
    Select,
    Submit,
    Text
};
use Exception;

/**
 * @brief       cinecturlink2 links default action class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class BackendActionsLinksDefault
{
    public static function addDefaultLinksActions(BackendActionsLinks $ap): void
    {
        $ap->addAction(
            [__('Delete') => 'dellinks'],
            self::doDeleteLinks(...)
        );
        $ap->addAction(
            [__('Change rating') => 'updlinksnote'],
            self::doChangeNote(...)
        );
        $ap->addAction(
            [__('Change category') => 'updlinkscat'],
            self::doChangeCategory(...)
        );
    }

    public static function doDeleteLinks(BackendActionsLinks $ap, ArrayObject $post): void
    {
        $ids = $ap->getIDs();

        if (empty($ids)) {
            $ap->error(new Exception(__('No links selected')));

            return;
        }

        foreach ($ids as $id) {
            $ap->utils->delLink($id);
        }

        Notices::addSuccessNotice(sprintf(
            __(
                '%d links has been successfully deleted.',
                '%d links have been successfully deleted.',
                count($ids)
            ),
            count($ids)
        ));
        $ap->redirect(true);
    }

    public static function doChangeCategory(BackendActionsLinks $ap, ArrayObject $post): void
    {
        if (isset($post['upd_cat_id'])) {
            $ids = $ap->getIDs();

            if (empty($ids)) {
                $ap->error(new Exception(__('No links selected')));

                return;
            }

            $cat_id = is_numeric($post['upd_cat_id']) ? abs((int) $post['upd_cat_id']) : null;

            $cur = App::con()->openCursor($ap->utils->table);
            foreach ($ids as $id) {
                $cur->clean();
                $cur->setField('cat_id', $cat_id == 0 ? null : $cat_id);
                $ap->utils->updLink($id, $cur);
            }

            Notices::addSuccessNotice(sprintf(
                __('Category of %s link successfully changed.', 'Category of %s links successfully changed.', count($ids)),
                count($ids)
            ));
            $ap->redirect(true);
        } else {
            $ap->beginPage(
                Page::breadcrumb([
                    __('Plugins')                            => '',
                    $ap->getCallerTitle()                    => $ap->getRedirection(true),
                    __('Change category for this selection') => '',
                ])
            );

            echo
            (new Form('form-action'))
                ->method('post')
                ->action($ap->getURI())
                ->fields([
                    (new Text('', $ap->getCheckboxes())),
                    (new Para())
                        ->items(array_merge(
                            $ap->hiddenFields(),
                            [
                                (new Label(__('Category:'), Label::OUTSIDE_LABEL_BEFORE))
                                    ->for('upd_cat_id'),
                                (new Select('upd_cat_id'))
                                    ->items(Combo::categoriesCombo()),
                                (new Submit('do-action'))
                                    ->value(__('Save')),
                                (new Hidden(['action'], 'updlinkscat')),
                                App::nonce()->formNonce(),
                            ]
                        )),

                ])
                ->render();

            $ap->endPage();
        }
    }

    public static function doChangeNote(BackendActionsLinks $ap, ArrayObject $post): void
    {
        if (isset($post['upd_link_note'])) {
            $ids = $ap->getIDs();

            if (empty($ids)) {
                $ap->error(new Exception(__('No links selected')));

                return;
            }

            $link_note = is_numeric($post['upd_link_note']) ? abs((int) $post['upd_link_note']) : 10;
            if ($link_note > 20) {
                $link_note = 10;
            }

            $cur = App::con()->openCursor($ap->utils->table);
            foreach ($ids as $id) {
                $cur->clean();
                $cur->setField('link_note', $link_note);
                $ap->utils->updLink($id, $cur);
            }

            Notices::addSuccessNotice(sprintf(
                __('Note of %s link successfully changed.', 'Note of %s links successfully changed.', count($ids)),
                count($ids)
            ));
            $ap->redirect(true);
        } else {
            $ap->beginPage(
                Page::breadcrumb([
                    __('Plugins')                        => '',
                    $ap->getCallerTitle()                => $ap->getRedirection(true),
                    __('Change note for this selection') => '',
                ])
            );

            echo
            (new Form('form-action'))
                ->method('post')
                ->action($ap->getURI())
                ->fields([
                    (new Text('', $ap->getCheckboxes())),
                    (new Para())
                        ->items(array_merge(
                            $ap->hiddenFields(),
                            [
                                (new Label(__('Note:'), Label::OUTSIDE_LABEL_BEFORE))
                                    ->for('upd_link_note'),
                                (new Select('upd_link_note'))
                                    ->items(Combo::notesCombo()),
                                (new Submit('do-action'))
                                    ->value(__('Save')),
                                (new Hidden(['action'], 'updlinksnote')),
                                App::nonce()->formNonce(),
                            ]
                        )),

                ])
                ->render();

            $ap->endPage();
        }
    }
}
