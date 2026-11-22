<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2\ClaudetteMcp;

use Dotclear\App;
use Dotclear\Database\MetaRecord;
use Dotclear\Plugin\ClaudetteMcp\McpTool;
use Dotclear\Plugin\cinecturlink2\My;

/**
 * @brief       cinecturlink2 tools helper for the plugin ClaudetteMcp.
 * @ingroup     cinecturlink2
 *
 * ClaudetteMcp connects an AI assistant to the blog: these tools let the assistant manage the links
 * (books, films, musics...). Tool names say "cinecturlink": the core ClaudetteMcp already has list_links for the blogroll.
 * These classes are only loaded by ClaudetteMcp, which is not required by cinecturlink2.
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
abstract class LinkTool extends McpTool
{
    /**
     * Family of tools for the keys of ClaudetteMcp (string, not constant: older ClaudetteMcp versions have none).
     */
    public function family(): string
    {
        return 'extras';
    }

    /**
     * Like in backend: the plugin must be active on the blog, its page is for content administrators.
     */
    public function available(): bool
    {
        return My::settings()->getBool('active', false) && $this->can(App::auth()::PERMISSION_CONTENT_ADMIN);
    }

    /**
     * Describe a link.
     *
     * @return  array<string, mixed>
     */
    protected function describeLink(MetaRecord $rs): array
    {
        return [
            'id'          => $rs->intField('link_id'),
            'title'       => $rs->strField('link_title'),
            'description' => $rs->strField('link_desc'),
            'author'      => $rs->strField('link_author'),
            'url'         => $rs->strField('link_url'),
            'image'       => $rs->strField('link_img'),
            'lang'        => $rs->strField('link_lang'),
            'note'        => $rs->intField('link_note'),
            'category'    => $rs->intField('cat_id') === 0 ? null : [
                'id'    => $rs->intField('cat_id'),
                'title' => $rs->strField('cat_title'),
            ],
            'updated'     => $rs->strField('link_upddt'),
        ];
    }
}
