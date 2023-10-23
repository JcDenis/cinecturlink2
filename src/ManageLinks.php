<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Core\Backend\Action\Actions;
use Dotclear\Core\Backend\Filter\{
    Filters,
    FiltersLibrary
};
use Dotclear\Core\Backend\{
    Notices,
    Page
};
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Form\{
    Div,
    Form,
    Hidden,
    Label,
    Link,
    Para,
    Select,
    Submit,
    Text
};
use Exception;

/**
 * @brief       cinecturlink2 manage links class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ManageLinks extends Process
{
    private static Actions $module_action;
    private static Filters $module_filter;
    private static BackendListingLinks $module_listing;
    private static int $module_counter    = 0;
    private static ?bool $module_rendered = null;

    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE) && ($_REQUEST['part'] ?? 'links') == 'links');
    }

    public static function process(): bool
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'links') {
            return false;
        }

        self::$module_action = new BackendActionsLinks(My::manageUrl(['part' => 'links'], '&'));
        if (self::$module_action->process()) {
            self::$module_rendered = true;

            return true;
        }

        self::$module_filter = new Filters(My::id());
        self::$module_filter->add('part', 'links');
        self::$module_filter->add(FiltersLibrary::getPageFilter());
        self::$module_filter->add(FiltersLibrary::getSearchFilter());
        self::$module_filter->add(FiltersLibrary::getSelectFilter(
            'cat_id',
            __('Category:'),
            Combo::categoriesCombo(),
            'cat_id'
        ));

        $params               = self::$module_filter->params();
        $params['link_type']  = 'cinecturlink';
        $params['no_content'] = true;

        try {
            $utils                = new Utils();
            $links                = $utils->getLinks($params);
            self::$module_counter = (int) $utils->getLinks($params, true)->f(0);
            self::$module_listing = new BackendListingLinks($links, self::$module_counter);
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }

    public static function render(): void
    {
        if (!self::status() || ($_REQUEST['part'] ?? 'links') != 'links') {
            return;
        }

        if (self::$module_rendered) {
            self::$module_action->render();

            return;
        }

        $from_redir = $_REQUEST['redir'] ?? '';
        $this_redir = My::manageUrl(self::$module_filter->values());

        Page::openModule(
            My::name(),
            self::$module_filter->js(My::manageUrl(['part' => 'links'])) .
            //Page::jsFilterControl(self::$module_filter->show()) .
            My::jsLoad('c2links')
        );

        echo
        Page::breadcrumb([
            __('Plugins')      => '',
            My::name()         => '',
            __('Manage links') => '',
        ]) .
        Notices::getNotices();

        if (!empty($from_redir)) {
            echo (new Para())
                ->items([
                    (new Link())
                        ->class('back')
                        ->href($from_redir)
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
                    ->href(My::manageUrl(['part' => 'link', 'redir' => $this_redir]))
                    ->text(__('New Link')),
                (new Link())
                    ->class('button add')
                    ->href(My::manageUrl(['part' => 'cats', 'redir' => $this_redir]))
                    ->text(__('Edit categories')),
            ])
            ->render();

        if (self::$module_counter) {
            self::$module_filter->display(
                'admin.plugin.' . My::id(),
                (new Hidden('p', My::id()))->render() . (new Hidden('part', 'links'))->render()
            );
        }

        self::$module_listing->display(
            self::$module_filter,
            (new Form('form-entries'))
                ->action(My::manageUrl())
                ->method('post')
                ->fields([
                    (new Text('', '%s')),
                    (new Div())
                        ->class('two-cols')
                        ->items([
                            (new Para())
                                ->class('col checkboxes-helpers'),
                            (new Para())
                                ->class('col right')
                                ->separator('&nbsp;')
                                ->items([
                                    (new Label(__('Selected links action:'), Label::OUTSIDE_LABEL_BEFORE))
                                        ->for('action'),
                                    (new Select('action'))
                                        ->items(self::$module_action->getCombo() ?? []),
                                    (new Submit('do-action'))
                                        ->value(__('ok')),
                                    ... My::hiddenFields(self::$module_filter->values(true)),
                                ]),
                        ]),
                ])
                ->render(),
            $this_redir
        );

        Page::helpBlock(My::id());

        Page::closeModule();
    }
}
