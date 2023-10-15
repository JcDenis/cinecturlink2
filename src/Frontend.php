<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Core\Process;

/**
 * @brief       cinecturlink2 frontend class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Frontend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::FRONTEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehavior('initWidgets', Widgets::init(...));

        $values = [
            'c2PageFeedID',
            'c2PageFeedURL',
            'c2PageURL',
            'c2PageTitle',
            'c2PageDescription',

            'c2EntryIfOdd',
            'c2EntryIfFirst',
            'c2EntryFeedID',
            'c2EntryID',
            'c2EntryTitle',
            'c2EntryDescription',
            'c2EntryFromAuthor',
            'c2EntryAuthorCommonName',
            'c2EntryAuthorDisplayName',
            'c2EntryAuthorEmail',
            'c2EntryAuthorID',
            'c2EntryAuthorLink',
            'c2EntryAuthorURL',
            'c2EntryLang',
            'c2EntryURL',
            'c2EntryCategory',
            'c2EntryCategoryID',
            'c2EntryCategoryURL',
            'c2EntryImg',
            'c2EntryDate',
            'c2EntryTime',

            'c2PaginationCounter',
            'c2PaginationCurrent',
            'c2PaginationURL',

            'c2CategoryFeedID',
            'c2CategoryFeedURL',
            'c2CategoryID',
            'c2CategoryTitle',
            'c2CategoryDescription',
            'c2CategoryURL',
        ];

        $blocks = [
            'c2If',

            'c2Entries',
            'c2EntriesHeader',
            'c2EntriesFooter',
            'c2EntryIf',

            'c2Pagination',
            'c2PaginationIf',

            'c2Categories',
            'c2CategoriesHeader',
            'c2CategoriesFooter',
            'c2CategoryIf',
        ];

        if (My::settings()->active) {
            foreach ($blocks as $v) {
                App::frontend()->template()->addBlock($v, [FrontendTemplate::class, $v]);
            }
            foreach ($values as $v) {
                App::frontend()->template()->addValue($v, [FrontendTemplate::class, $v]);
            }
        } else {
            foreach (array_merge($blocks, $values) as $v) {
                pdump($v);
                App::frontend()->template()->addBlock($v, FrontendTemplate::disable(...));
            }
        }

        return true;
    }
}
