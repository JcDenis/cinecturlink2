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
    private static int $linkid          = 0;
    private static string $linktitle    = '';
    private static string $linkdesc     = '';
    private static string $linkauthor   = '';
    private static string $linkurl      = '';
    private static ?string $linkcat     = '';
    private static string $linklang     = '';
    private static string $linkimage    = '';
    private static string $linknote     = '';

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE) && ($_REQUEST['part'] ?? 'links') == 'link');
    }

    public static function process(): bool
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'link') {
            return false;
        }

        $utils = new Utils();

        self::$module_redir = $_REQUEST['redir'] ?? '';
        self::$linkid       = (int) ($_REQUEST['linkid'] ?? 0);
        self::$linktitle    = $_POST['linktitle']  ?? '';
        self::$linkdesc     = $_POST['linkdesc']   ?? '';
        self::$linkauthor   = $_POST['linkauthor'] ?? '';
        self::$linkurl      = $_POST['linkurl']    ?? '';
        self::$linkcat      = $_POST['linkcat']    ?? null;
        self::$linklang     = $_POST['linklang']   ?? App::auth()->getInfo('user_lang');
        self::$linkimage    = $_POST['linkimage']  ?? '';
        self::$linknote     = $_POST['linknote']   ?? '';

        if (!empty($_POST['save'])) {
            try {
                Utils::makePublicDir(
                    App::config()->dotclearRoot() . '/' . App::blog()->settings()->system->get('public_path'),
                    My::settings()->folder
                );
                if (empty(self::$linktitle)) {
                    throw new Exception(__('You must provide a title.'));
                }
                if (empty(self::$linkauthor)) {
                    throw new Exception(__('You must provide an author.'));
                }
                if (!preg_match('/https?:\/\/.+/', self::$linkimage)) {
                    //throw new Exception(__('You must provide a link to an image.'));
                }

                $cur = App::con()->openCursor($utils->table);
                $cur->setField('link_title', self::$linktitle);
                $cur->setField('link_desc', self::$linkdesc);
                $cur->setField('link_author', self::$linkauthor);
                $cur->setField('link_url', self::$linkurl);
                $cur->setField('cat_id', self::$linkcat == '' ? null : self::$linkcat);
                $cur->setField('link_lang', self::$linklang);
                $cur->setField('link_img', self::$linkimage);
                $cur->setField('link_note', self::$linknote);

                // create a link
                if (empty(self::$linkid)) {
                    $exists = $utils->getLinks(['link_title' => self::$linktitle], true)->f(0);
                    if ($exists) {
                        throw new Exception(__('Link with same name already exists.'));
                    }
                    self::$linkid = $utils->addLink($cur);

                    Notices::addSuccessNotice(
                        __('Link successfully created.')
                    );
                    // update a link
                } else {
                    $exists = $utils->getLinks(['link_id' => self::$linkid], true)->f(0);
                    if (!$exists) {
                        throw new Exception(__('Unknown link.'));
                    }
                    $utils->updLink(self::$linkid, $cur);

                    Notices::addSuccessNotice(
                        __('Link successfully updated.')
                    );
                }
                My::redirect(
                    [
                        'part'   => 'link',
                        'linkid' => self::$linkid,
                        'redir'  => self::$module_redir,
                    ]
                );
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        }

        if (!empty($_POST['delete']) && !empty(self::$linkid)) {
            try {
                $utils->delLink(self::$linkid);

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

        if (!empty(self::$linkid)) {
            $link = $utils->getLinks(['link_id' => self::$linkid]);
            if (!$link->isEmpty()) {
                self::$linktitle  = (string) $link->f('link_title');
                self::$linkdesc   = (string) $link->f('link_desc');
                self::$linkauthor = (string) $link->f('link_author');
                self::$linkurl    = (string) $link->f('link_url');
                self::$linkcat    = (string) $link->f('cat_id');
                self::$linklang   = (string) $link->f('link_lang');
                self::$linkimage  = (string) $link->f('link_img');
                self::$linknote   = (string) $link->f('link_note');
            }
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
            __('Plugins')                                             => '',
            My::name()                                                => My::manageUrl(),
            (empty(self::$linkid) ? __('New link') : __('Edit link')) => '',
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
                                                    ->for('linktitle'),
                                                (new Input('linktitle'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$linktitle)),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Description:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('linkdesc'),
                                                (new Input('linkdesc'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$linkdesc)),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Author:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('linkauthor'),
                                                (new Input('linkauthor'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$linkauthor)),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Details URL:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('linkurl'),
                                                (new Input('linkurl'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$linkurl)),
                                                (new Link('newlinksearch'))
                                                    ->class('modal hidden-if-no-js')
                                                    ->href('http://google.com')
                                                    ->title(__('Search with Google'))
                                                    ->text(__('Search with Google')),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Image URL:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('linkimage'),
                                                (new Input('linkimage'))
                                                    ->size(65)
                                                    ->maxlength(255)
                                                    ->value(Html::escapeHTML(self::$linkimage)),
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
                                                    ->for('linkcat'),
                                                (new Select('linkcat'))
                                                    ->items(Combo::categoriesCombo())
                                                    ->default(self::$linkcat),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Lang:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('linklang'),
                                                (new Select('linklang'))
                                                    ->items(Combo::langsCombo())
                                                    ->default(self::$linklang),
                                            ]),
                                        (new Para())
                                            ->items([
                                                (new Label(__('Rating:'), Label::OUTSIDE_LABEL_BEFORE))
                                                    ->for('linknote'),
                                                (new Number('linknote'))
                                                    ->min(0)
                                                    ->max(20)
                                                    ->value(self::$linknote),
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
                                    'linkid' => self::$linkid,
                                    'part'   => 'link',
                                    'redir'  => self::$module_redir,
                                ]),
                            ]),
                    ]),
            ])
            ->render();

        Page::helpBlock(My::id());

        Page::closeModule();
    }
}
