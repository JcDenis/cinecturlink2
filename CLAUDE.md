# cinecturlink2

Notes for AI assistants working on the Dotclear plugin cinecturlink2.

**Read first** the shared conventions of the author's Dotclear modules:
https://raw.githubusercontent.com/JcDenis/gh2da/master/CONVENTIONS.md
(if a web tool refuses the address, use `curl -s` on it from the shell).
The rules below take precedence for this module.

## Module

Widgets and pages about books, musics, films, blogs you are interested in It is hightly based on cinecturlink for Dotclear 1 by Tigroux and maintain by brol. A user guide with screenshots is available in French: [guide d'utilisation](https://github.com/JcDenis/cinecturlink2/blob/master/docs/fr/utilisation.md).

Admin page. Public side: behaviors.

Required Dotclear and PHP versions: see `_define.php`.

## Structure

* `src/ActivityReportAction.php`: plugin activityReport class.
* `src/Backend.php`: admin side: menu entry, favorites, behaviors registration.
* `src/BackendActionsLinks.php`: links action class.
* `src/BackendActionsLinksDefault.php`: links default action class.
* `src/BackendListingLinks.php`: links list class.
* `src/Combo.php`: combo class.
* `src/Frontend.php`: public side: behaviors, template tags and URL handlers registration.
* `src/FrontendContext.php`: frontend contxt class.
* `src/FrontendTemplate.php`: template tags.
* `src/FrontendUrl.php`: public URL handler.
* `src/Install.php`: install and update, database structure (a change needs a migration here).
* `src/Manage.php`: admin page of the module.
* `src/ManageCat.php`: admin page (cat).
* `src/ManageCats.php`: admin page (cats).
* `src/ManageLink.php`: admin page (link).
* `src/ManageLinks.php`: admin page (links).
* `src/My.php`: module helpers (`My::id()`, settings, permissions).
* `src/PluginSitemaps.php`: add Cinecturlink public main page and categories pages to plugin sitemap.
* `src/Prepend.php`: loaded on every request: autoload, URL handlers, permission types.
* `src/RecordCatsRow.php`: record categories row class.
* `src/RecordLinksRow.php`: record links row class.
* `src/Uninstall.php`: cleaners for the plugin Uninstaller.
* `src/Utils.php`: helpers.
* `src/WidgetCatsDescriptor.php`: widget categories descriptor class.
* `src/WidgetLinksDescriptor.php`: widget links descriptor class.
* `src/Widgets.php`: widgets definition and rendering.
* `js/`: `c2cats.js`, `c2link.js`, `c2links.js`.
* `default-templates/`: public templates.
* Database tables `cinecturlink2`, `cinecturlink2_cat`.
* Behaviors used: `initWidgets`, `adminColumnsListsV2`, `adminFiltersListsV3`, `adminDashboardFavoritesV2`, `adminBlogPreferencesFormV2`, `adminBeforeBlogSettingsUpdate`, `sitemapsDefineParts`, `sitemapsURLsCollect`.

## Pitfalls

* Links are also published in sitemaps (plugin sitemaps of franck-paul, `src/PluginSitemaps.php`): settings names and types must match what sitemaps expects.
* Table structure changes need a migration in `src/Install.php` (version compare), and must work on MySQL, PostgreSQL and SQLite.

## Testing

* Check the admin page, the admin help, in English and French.
* CI browses the common admin and public pages; extra pages can be listed in `.github/dotclear-test.txt`.
* The user guide `docs/fr/utilisation.md` describes the features: update it, and its screenshots, when the interface changes.
