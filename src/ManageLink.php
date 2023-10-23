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
    Div,
    Form,
    Input,
    Label,
    Link,
    Note,
    Number,
    Para,
    Select,
    Submit
};
use Dotclear\Helper\Html\Html;
use Dotclear\Helper\Network\Http;
use Exception;

/**
 * @brief       cinecturlink2 manage link class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ManageLink extends Process
{
    private static string $module_redir = '';
    private static RecordLinksRow $row;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE) && ($_REQUEST['part'] ?? 'links') == 'link');
    }

    public static function process(): bool
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'link') {
            return false;
        }

        self::$module_redir = $_REQUEST['redir'] ?? '';
        self::$row          = new RecordLinksRow();
        $utils              = new Utils();

        if (!empty($_POST['save'])) {
            try {
                Utils::makePublicDir(
                    App::config()->dotclearRoot() . '/' . App::blog()->settings()->system->get('public_path'),
                    My::settings()->folder
                );
                if (empty(self::$row->link_title)) {
                    throw new Exception(__('You must provide a title.'));
                }
                if (empty(self::$row->link_author)) {
                    throw new Exception(__('You must provide an author.'));
                }
                if (!preg_match('/https?:\/\/.+/', self::$row->link_img)) {
                    //throw new Exception(__('You must provide a link to an image.'));
                }

                // create a link
                if (!self::$row->link_id) {
                    $exists = $utils->getLinks(['link_title' => self::$row->link_title], true)->f(0);
                    if ($exists) {
                        throw new Exception(__('Link with same name already exists.'));
                    }
                    $link_id = $utils->addLink(self::$row->getCursor());

                    Notices::addSuccessNotice(
                        __('Link successfully created.')
                    );
                    // update a link
                } else {
                    $exists = $utils->getLinks(['link_id' => self::$row->link_id], true)->f(0);
                    if (!$exists) {
                        throw new Exception(__('Unknown link.'));
                    }
                    $link_id = $utils->updLink(self::$row->link_id, self::$row->getCursor());

                    Notices::addSuccessNotice(
                        __('Link successfully updated.')
                    );
                }
                My::redirect(
                    [
                        'part'    => 'link',
                        'link_id' => $link_id,
                        'redir'   => self::$module_redir,
                    ]
                );
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        if (!empty($_POST['delete']) && self::$row->link_id) {
            try {
                $utils->delLink(self::$row->link_id);

                Notices::addSuccessNotice(
                    __('Link successfully deleted.')
                );
                if (!empty($_POST['redir'])) {
                    Http::redirect(self::$module_redir);
                } else {
                    My::redirect(['part' => 'links']);
                }
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        if (self::$row->link_id) {
            self::$row = new RecordLinksRow(
                $utils->getLinks(['link_id' => self::$row->link_id])
            );
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'link') {
            return;
        }

        $mc = Combo::mediaCombo();

        Page::openModule(
            My::name(),
            Page::jsVars(['dotclear.c2_lang' => App::auth()->getInfo('user_lang')]) .
            My::jsLoad('c2link')
        );

        echo
        Page::breadcrumb([
            __('Plugins')                                                   => '',
            My::name()                                                      => My::manageUrl(),
            (empty(self::$row->link_id) ? __('New link') : __('Edit link')) => '',
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

        echo (new Div())
            ->items([
                (new Form('newlinkform'))
                    ->action(My::manageUrl())
                    ->method('post')
                    ->fields([
                        (new Div())
                            ->class('two-cols clearfix')
                            ->items([
                                (new Div())
                                    ->class('col70')
                                    ->items([
                                        (new Para())
                                            ->items([
                                                (new Label(__('Title:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('link_title'),
                                                (new Input('link_title'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$row->link_title)),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Description:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('link_desc'),
                                                (new Input('link_desc'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$row->link_desc)),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Author:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('link_author'),
                                                (new Input('link_author'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$row->link_author)),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Details URL:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('link_url'),
                                                (new Input('link_url'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$row->link_url)),
                                                (new Link('newlinksearch'))
                                                    ->class('modal hidden-if-no-js')
                                                    ->href('http://google.com')
                                                    ->title(__('Search with Google'))
                                                    ->text(__('Search with Google')),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Image URL:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('link_img'),
                                                (new Input('link_img'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$row->link_img)),
                                                (new Link('newimagesearch'))
                                                    ->class('modal hidden-if-no-js')
                                                    ->href('http://amazon.com')
                                                    ->title(__('Search with Amazon'))
                                                    ->text(__('Search with Amazon')),
                                            ]),
                                        ...(
                                            empty($mc) ?
                                            [(new Note())
                                                ->class('form-note')
                                                ->text(__('There is no image in cinecturlink media path.'))] :
                                            [(new Para())
                                                ->items([
                                                    (new Label(__('or select from repository:'), Label::OUTSIDE_LABEL_BEFORE))
                                                        ->for('newimageselect'),
                                                    (new Select('newimageselect'))
                                                        ->items($mc)
                                                        ->default(''),
                                                ]),
                                                (new Para())
                                                    ->class('form-note')
                                                    ->items([
                                                        (new Link())
                                                            ->class('modal hidden-if-no-js')
                                                            ->href(App::backend()->url()->get('admin.media', ['d' => (string) My::settings()->folder]))
                                                            ->title(__('Media manager'))
                                                            ->text(__('Go to media manager to add image to cinecturlink path.')),
                                                    ]),
                                            ]
                                        ),
                                    ]),
                                (new Div())
                                    ->class('col30')
                                    ->items([
                                        (new Para())
                                            ->items([
                                                (new Label(__('Category:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('cat_id'),
                                                (new Select('cat_id'))
                                                    ->items(Combo::categoriesCombo())
                                                    ->default((string) self::$row->cat_id),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Lang:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('link_lang'),
                                                (new Select('link_lang'))
                                                    ->items(Combo::langsCombo())
                                                    ->default(self::$row->link_lang),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Rating:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('link_note'),
                                                (new Number('link_note'))
                                                    ->min(0)
                                                    ->max(20)
                                                    ->value(self::$row->link_note),
                                            ]),
                                    ]),
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
                                    'link_id' => self::$row->link_id,
                                    'part'    => 'link',
                                    'redir'   => self::$module_redir,
                                ]),
                            ]),
                    ]),
            ])
            ->render();

        Page::helpBlock(My::id());

        Page::closeModule();
    }
}
