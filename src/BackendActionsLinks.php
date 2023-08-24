<?php
/**
 * @brief cinecturlink2, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugin
 *
 * @author Jean-Christian Denis and Contributors
 *
 * @copyright Jean-Christian Denis
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use ArrayObject;
use dcCore;
use Dotclear\Core\Backend\Action\Actions;
use Dotclear\Core\Backend\Page;
use Dotclear\Database\MetaRecord;
use Dotclear\Helper\Html\Form\{
    Link,
    Para
};
use Exception;

class BackendActionsLinks extends Actions
{
    protected $use_render = true;
    public Utils $utils;

    public function __construct(string $uri, array $redirect_args = [])
    {
        $this->utils = new Utils();

        parent::__construct($uri, $redirect_args);

        $this->redirect_fields = [
            'sortby', 'order', 'page', 'nb',
        ];
        $this->field_entries = 'entries';
        $this->caller_title  = __('links');
        $this->loadDefaults();
    }

    protected function loadDefaults(): void
    {
        BackendActionsLinksDefault::addDefaultLinksActions($this);

        # --BEHAVIOR-- cinecturlink2AddDefaultLinksActions - BackendActionsLinks
        dcCore::app()->callBehavior('cinecturlink2AddDefaultLinksActions', $this);
    }

    public function beginPage(string $breadcrumb = '', string $head = ''): void
    {
        Page::openModule(
            My::name(),
            $head
        );
        echo
        $breadcrumb .
        (new Para())->items([
            (new Link())
                ->class('back')
                ->href($this->getRedirection(true))
                ->text(__('Back to links list')),
        ])->render();
    }

    public function endPage(): void
    {
        Page::closeModule();
    }

    public function error(Exception $e): void
    {
        dcCore::app()->error->add($e->getMessage());
        $this->beginPage(
            Page::breadcrumb([
                __('Plugins')           => '',
                $this->getCallerTitle() => $this->getRedirection(true),
                __('Links actions')     => '',
            ])
        );
        $this->endPage();
    }

    protected function fetchEntries(ArrayObject $from): void
    {
        if (!empty($from['entries']) && is_array($from['entries'])) {
            $params = [
                'link_id' => $from['entries'],
            ];

            $rs = $this->utils->getLinks($params);
            while ($rs->fetch()) {
                $this->entries[$rs->f('link_id')] = $rs->f('link_title');
            }
            $this->rs = $rs;
        } else {
            $this->rs = MetaRecord::newFromArray([]);
        }
    }
}
