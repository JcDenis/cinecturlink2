<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2\ClaudetteMcp;

use Dotclear\Plugin\cinecturlink2\Utils;

/**
 * @brief       cinecturlink2 tool for the plugin ClaudetteMcp: list links.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class ListCinecturlinks extends LinkTool
{
    public function name(): string
    {
        return 'list_cinecturlinks';
    }

    public function title(): string
    {
        return 'List books, films and musics links';
    }

    public function description(): string
    {
        return 'List the links of the plugin cinecturlink2 (books, films, musics, blogs... the user is interested in, with a note out of 20), ' .
            'most recently updated first, and their categories. Not the blogroll (see list_links).';
    }

    public function properties(): array
    {
        return [
            'search' => [
                'type'        => 'string',
                'description' => 'Limit to links whose title contains these words.',
            ],
            'category_id' => [
                'type'        => 'integer',
                'description' => 'Limit to a category ID (see the categories of the answer).',
            ],
            'limit' => [
                'type'        => 'integer',
                'description' => 'Links per page, 1 to 100, default: 50.',
            ],
            'page' => [
                'type'        => 'integer',
                'description' => 'Page number, default: 1.',
            ],
        ];
    }

    public function call(array $args): array
    {
        $utils  = new Utils();
        $limit  = $this->int($args, 'limit', 50, 1, 100);
        $page   = $this->int($args, 'page', 1, 1);
        $params = [];

        if ($this->str($args, 'search') !== '') {
            $params['q'] = $this->str($args, 'search');
        }
        if ($this->int($args, 'category_id') > 0) {
            $params['cat_id'] = $this->int($args, 'category_id');
        }

        $total = $utils->getLinks($params, true)->intField(0);

        $params['limit'] = [($page - 1) * $limit, $limit];

        $rs    = $utils->getLinks($params);
        $links = [];
        while ($rs->fetch()) {
            $links[] = $this->describeLink($rs);
        }

        $rs         = $utils->getCategories(['order' => 'cat_pos ASC']);
        $categories = [];
        while ($rs->fetch()) {
            $categories[] = [
                'id'          => $rs->intField('cat_id'),
                'title'       => $rs->strField('cat_title'),
                'description' => $rs->strField('cat_desc'),
            ];
        }

        return [
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'links'      => $links,
            'categories' => $categories,
        ];
    }
}
