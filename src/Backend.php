<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Process;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\Html\Form\{ Checkbox, Fieldset, Img, Input, Label, Legend, Note, Number, Para, Select, Text };
use Dotclear\Interface\Core\BlogSettingsInterface;

/**
 * @brief       cinecturlink2 backend class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Backend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        My::addBackendMenuItem();

        App::behavior()->addBehaviors([
            'initWidgets'         => Widgets::init(...),
            'adminColumnsListsV2' => function (ArrayObject $cols) {
                $cols[My::id()] = [
                    My::name(),
                    [
                        'date'   => [true, __('Date')],
                        'cat'    => [true, __('Category')],
                        'author' => [true, __('Author')],
                        'desc'   => [false, __('Description')],
                        'link'   => [true, __('Links')],
                        'note'   => [true, __('Rating')],
                    ],
                ];
            },

            'adminFiltersListsV2' => function (ArrayObject $sorts) {
                $sorts[My::id()] = [
                    My::name(),
                    [
                        __('Date')        => 'link_upddt',
                        __('Title')       => 'link_title',
                        __('Category')    => 'cat_id',
                        __('Author')      => 'link_author',
                        __('Description') => 'link_desc',
                        __('Link')        => 'link_url',
                        __('Rating')      => 'link_note',
                    ],
                    'link_upddt',
                    'desc',
                    [__('Links per page'), 30],
                ];
            },

            'adminDashboardFavoritesV2' => function (Favorites $favs) {
                $favs->register(My::id(), [
                    'title'       => My::name(),
                    'url'         => My::manageUrl() . '#links',
                    'small-icon'  => My::icons(),
                    'large-icon'  => My::icons(),
                    'permissions' => App::auth()->makePermissions([App::auth()::PERMISSION_CONTENT_ADMIN]),
                ]);
            },

            'adminBlogPreferencesFormV2' => function (BlogSettingsInterface $blog_settings): void {
                $s            = $blog_settings->get(My::id());
                $url          = App::blog()->url() . App::url()->getBase(My::id());
                $public_nbrpp = (int) $s->get('public_nbrpp');
                if ($public_nbrpp < 1) {
                    $public_nbrpp = 10;
                }

                echo (new Fieldset(My::id() . '_params'))
                    ->legend(new Legend((new Img(My::icons()[0]))->class('icon-small')->render() . ' ' . My::name()))
                    ->items([
                        (new Text('h5', __('General'))),
                        (new Para())
                            ->items([
                                (new Checkbox(My::id() . 'active', (bool) $s->get('avtive')))
                                    ->value(1),
                                (new Label(__('Enable plugin'), Label::OUTSIDE_LABEL_AFTER))
                                    ->class('classic')
                                    ->for(My::id() . 'active'),
                            ]),
                        (new Para())
                            ->items([
                                (new Label(__('Public folder of images (under public folder of blog):'), Label::OUTSIDE_LABEL_BEFORE))
                                    ->for(My::id() . 'folder'),
                                (new Select(My::id() . 'folder'))
                                    ->items(Utils::getPublicDirs())
                                    ->default((string) $s->get('folder')),
                            ]),
                        (new Para())
                            ->items([
                                (new Label(__('Or create a new public folder of images:'), Label::OUTSIDE_LABEL_BEFORE))
                                    ->for(My::id() . 'newdir'),
                                (new Input(My::id() . 'newdir'))
                                    ->size(65)
                                    ->maxlength(255)
                                    ->value(''),
                            ]),
                        (new Para())
                            ->items([
                                (new Label(__('Maximum width of images (in pixel):'), Label::OUTSIDE_LABEL_BEFORE))
                                    ->for(My::id() . 'widthmax')
                                    ->class('classic'),
                                (new Number(My::id() . 'widthmax'))
                                    ->min(10)
                                    ->max(512)
                                    ->value((string) abs((int) $s->get('widthmax'))),
                            ]),

                        (new Text('hr')),
                        (new Text('h5', __('Widget'))),
                        (new Para())
                            ->items([
                                (new Checkbox(My::id() . 'triggeronrandom', (bool) $s->get('triggeronrandom')))
                                    ->value(1),
                                (new Label(__('Update cache when use "Random" or "Number of view" order on widget (Need reload of widgets on change)'), Label::OUTSIDE_LABEL_AFTER))
                                    ->class('classic')
                                    ->for(My::id() . 'triggeronrandom'),
                            ]),
                        (new Note())
                            ->text(__('This increases the random effect, but updates the cache of the blog whenever the widget is displayed, which reduces the perfomances of your blog.'))
                            ->class('form-note'),

                        (new Text('hr')),
                        (new Text('h5', __('Public page'))),
                        (new Para())
                            ->items([
                                (new Checkbox(My::id() . 'public_active', (bool) $s->get('public_active')))
                                    ->value(1),
                                (new Label(__('Enable public page'), Label::OUTSIDE_LABEL_AFTER))
                                    ->class('classic')
                                    ->for(My::id() . 'public_active'),
                            ]),
                        (new Note())
                            ->text(sprintf(__('Public page has url: %s'), '<a href="' . $url . '" title="public page">' . $url . '</a>'))
                            ->class('form-note'),
                        (new Para())
                            ->items([
                                (new Label(__('Title of the public page:'), Label::OUTSIDE_LABEL_BEFORE))
                                    ->for(My::id() . 'public_title'),
                                (new Input(My::id() . 'public_title'))
                                    ->size(65)
                                    ->maxlength(255)
                                    ->value((string) $s->get('public_title')),
                            ]),
                        (new Para())
                            ->items([
                                (new Label(__('Description of the public page:'), Label::OUTSIDE_LABEL_BEFORE))
                                    ->for(My::id() . 'public_description'),
                                (new Input(My::id() . 'public_description'))
                                    ->size(65)
                                    ->maxlength(255)
                                    ->value((string) $s->get('public_description')),
                            ]),
                        (new Para())
                            ->items([
                                (new Label(__('Limit number of entries per page on pulic page to:'), Label::OUTSIDE_LABEL_BEFORE))
                                    ->for(My::id() . 'public_nbrpp')
                                    ->class('classic'),
                                (new Number(My::id() . 'public_nbrpp'))
                                    ->min(1)
                                    ->max(256)
                                    ->value($public_nbrpp),
                            ]),
                    ])
                    ->render();
            },

            'adminBeforeBlogSettingsUpdate' => function (BlogSettingsInterface $blog_settings): void {
                $s                  = $blog_settings->get(My::id());
                $active             = !empty($_POST[My::id() . 'active']);
                $widthmax           = abs((int) $_POST[My::id() . 'widthmax']);
                $newdir             = (string) Files::tidyFileName($_POST[My::id() . 'newdir']);
                $folder             = empty($newdir) ? (string) Files::tidyFileName($_POST[My::id() . 'folder']) : $newdir;
                $triggeronrandom    = !empty($_POST[My::id() . 'triggeronrandom']);
                $public_active      = !empty($_POST[My::id() . 'public_active']);
                $public_title       = (string) $_POST[My::id() . 'public_title'];
                $public_description = (string) $_POST[My::id() . 'public_description'];
                $public_nbrpp       = (int) $_POST[My::id() . 'public_nbrpp'];

                if ($public_nbrpp < 1) {
                    $public_nbrpp = 10;
                }
                if (empty($folder)) {
                    App::error()->add(__('You must provide a specific folder for images.'));

                    return;
                }
                Utils::makePublicDir(
                    App::config()->dotclearRoot() . '/' . App::blog()->settings()->get('system')->get('public_path'),
                    $folder,
                    true
                );

                $s->put('avtive', $active);
                $s->put('public_active', $public_active);
                $s->put('public_title', $public_title);
                $s->put('public_description', $public_description);
                $s->put('public_nbrpp', $public_nbrpp);
                $s->put('widthmax', $widthmax);
                $s->put('folder', $folder);
                $s->put('triggeronrandom', $triggeronrandom);
            },
        ]);

        return true;
    }
}
