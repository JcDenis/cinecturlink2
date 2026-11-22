<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2\ClaudetteMcp;

use Dotclear\App;
use Dotclear\Plugin\ClaudetteMcp\McpToolException;
use Dotclear\Plugin\cinecturlink2\My;
use Dotclear\Plugin\cinecturlink2\Utils;
use Exception;

/**
 * @brief       cinecturlink2 tool for the plugin ClaudetteMcp: add or update a link.
 * @ingroup     cinecturlink2
 *
 * Same checks as the link form of the admin. No deletion.
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class SetCinecturlink extends LinkTool
{
    public function name(): string
    {
        return 'set_cinecturlink';
    }

    public function title(): string
    {
        return 'Add or update a book, film or music link';
    }

    public function description(): string
    {
        return 'Add a link to the plugin cinecturlink2 (a book, film, music, blog... with a note out of 20), or update one (with its ID from list_cinecturlinks). ' .
            'On update, only given arguments are changed. Links can not be deleted here. Changes are visible on the blog at once.';
    }

    public function properties(): array
    {
        return [
            'id' => [
                'type'        => 'integer',
                'description' => 'The ID of the link to update (see list_cinecturlinks), omit to add a new link.',
            ],
            'title' => [
                'type'        => 'string',
                'description' => 'The title of the book, film, music..., unique, required for a new link.',
            ],
            'description' => [
                'type'        => 'string',
                'description' => 'A short description (255 characters at most), required for a new link.',
            ],
            'author' => [
                'type'        => 'string',
                'description' => 'The author (writer, director, band...), required for a new link.',
            ],
            'url' => [
                'type'        => 'string',
                'description' => 'An address about the work (http or https), empty for none.',
            ],
            'image' => [
                'type'        => 'string',
                'description' => 'The address of an image of the work (http or https, ex: from list_media), empty for none.',
            ],
            'lang' => [
                'type'        => 'string',
                'description' => 'The language code of the work (ex: fr). Default for a new link: the language of the user.',
            ],
            'note' => [
                'type'        => 'integer',
                'description' => 'The note out of 20. Default for a new link: 10.',
            ],
            'category_id' => [
                'type'        => 'integer',
                'description' => 'The category ID (see the categories of list_cinecturlinks), 0 for none.',
            ],
        ];
    }

    public function readOnly(): bool
    {
        return false;
    }

    public function call(array $args): array
    {
        $utils   = new Utils();
        $id      = $this->int($args, 'id');
        $current = [];

        if ($id !== 0) {
            $rs = $utils->getLinks(['link_id' => $id]);
            if ($rs->isEmpty()) {
                throw new McpToolException('Unknown link, see list_cinecturlinks');
            }
            $current = $this->describeLink($rs);
        }

        $value = function (string $name, string $default = '') use ($args, $current): string {
            if ($this->has($args, $name)) {
                return $this->str($args, $name);
            }

            return is_string($v = $current[$name] ?? null) ? $v : $default;
        };

        $title  = $value('title');
        $desc   = $value('description');
        $author = $value('author');
        $url    = $value('url');
        $image  = $value('image');
        $lang   = $value('lang', is_string($l = App::auth()->getInfo('user_lang')) ? $l : 'en');
        $note   = $this->has($args, 'note') ? $this->int($args, 'note', 10, 0, 20) : (is_int($n = $current['note'] ?? null) ? $n : 10);
        $cat_id = $this->has($args, 'category_id') ? $this->int($args, 'category_id') : (is_array($c = $current['category'] ?? null) && is_int($c['id'] ?? null) ? $c['id'] : 0);

        if ($title === '') {
            throw new McpToolException('A link needs a title');
        }
        if ($desc === '') {
            throw new McpToolException('A link needs a description');
        }
        if ($author === '') {
            throw new McpToolException('A link needs an author');
        }
        foreach (['url' => $url, 'image' => $image] as $name => $address) {
            if ($address !== '' && !preg_match('#^https?://.+#i', $address)) {
                throw new McpToolException('The ' . $name . ' must be an http or https address');
            }
        }
        if ($cat_id > 0 && $utils->getCategories(['cat_id' => $cat_id], true)->intField(0) === 0) {
            throw new McpToolException('Unknown category, see list_cinecturlinks');
        }
        $same = $utils->getLinks(['link_title' => $title]);
        while ($same->fetch()) {
            if ($same->intField('link_id') !== $id) {
                throw new McpToolException('A link with the same title already exists, see list_cinecturlinks');
            }
        }

        $cur = App::db()->con()->openCursor(App::db()->con()->prefix() . My::CINECTURLINK_TABLE_NAME);
        $cur->setStrField('link_title', $title);
        $cur->setStrField('link_desc', mb_substr($desc, 0, 255));
        $cur->setStrField('link_author', $author);
        $cur->setStrField('link_url', $url);
        $cur->setStrField('link_img', $image);
        $cur->setStrField('link_lang', mb_substr($lang, 0, 5));
        $cur->setIntField('link_note', $note);
        $cur->setField('cat_id', $cat_id > 0 ? $cat_id : null);

        try {
            $id = $id === 0 ? $utils->addLink($cur) : $utils->updLink($id, $cur);
        } catch (Exception $e) {
            throw new McpToolException($e->getMessage());
        }

        $rs = $utils->getLinks(['link_id' => $id]);
        if ($rs->isEmpty()) {
            throw new McpToolException('Link not saved');
        }

        return $this->describeLink($rs);
    }
}
