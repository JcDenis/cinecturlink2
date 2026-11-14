<?php

/**
 * @file
 * @brief       The plugin cinecturlink2 help resource
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

\Dotclear\App::backend()->resources()
    ->set('help', 'cinecturlink2-links', __DIR__ . '/help/links.html')
    ->set('help', 'cinecturlink2-link', __DIR__ . '/help/link.html')
    ->set('help', 'cinecturlink2-cats', __DIR__ . '/help/cats.html')
    ->set('help', 'cinecturlink2-cat', __DIR__ . '/help/cat.html');
