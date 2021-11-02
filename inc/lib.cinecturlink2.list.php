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
if (!defined('DC_CONTEXT_ADMIN')) {
    return null;
}

class adminlistCinecturlink2
{
    public $redir = '';

    protected $core;
    protected $rs;
    protected $rs_count;
    protected $html_prev;
    protected $html_next;

    public function __construct(dcCore $core, $rs, $rs_count)
    {
        $this->core      = &$core;
        $this->rs        = &$rs;
        $this->rs_count  = $rs_count;
        $this->html_prev = __('&#171; prev.');
        $this->html_next = __('next &#187;');
    }

    public function userColumns($type, $cols)
    {
        $cols_user = @$this->core->auth->user_prefs->interface->cols;
        if (is_array($cols_user) || $cols_user instanceof ArrayObject) {
            if (isset($cols_user[$type])) {
                foreach ($cols_user[$type] as $cn => $cd) {
                    if (!$cd && isset($cols[$cn])) {
                        unset($cols[$cn]);
                    }
                }
            }
        }
    }

    public function display($page, $nb_per_page, $enclose_block = '', $filter = false, $redir = '')
    {
        $this->redir = $redir;
        if ($this->rs->isEmpty()) {
            if ($filter) {
                echo '<p><strong>' . __('No link matches the filter') . '</strong></p>';
            } else {
                echo '<p><strong>' . __('No link') . '</strong></p>';
            }
        } else {
            $pager = new dcPager($page, $this->rs_count, $nb_per_page, 10);
            $links = [];
            if (isset($_REQUEST['links'])) {
                foreach ($_REQUEST['links'] as $v) {
                    $links[(int) $v] = true;
                }
            }

            $cols = [
                'title'  => '<th colspan="2" class="first">' . __('Title') . '</th>',
                'author' => '<th scope="col">' . __('Author') . '</th>',
                'desc'   => '<th scope="col">' . __('Description') . '</th>',
                'link'   => '<th scope="col">' . __('Links') . '</th>',
                'cat'    => '<th scope="col">' . __('Category') . '</th>',
                'note'   => '<th scope="col">' . __('Rating') . '</th>',
                'date'   => '<th scope="col">' . __('Date') . '</th>'
            ];
            $cols = new ArrayObject($cols);
            $this->userColumns('c2link', $cols);

            $html_block = '<div class="table-outer">' .
            '<table>' .
            '<caption>' . (
                $filter ?
                sprintf(__('List of %s links matching the filter.'), $this->rs_count) :
                sprintf(__('List of links (%s)'), $this->rs_count)
            ) . '</caption>' .
            '<thead>' .
            '<tr>' . implode(iterator_to_array($cols)) . '</tr>' .
            '</thead>' .
            '<tbody>%s</tbody>' .
            '</table>' .
            '%s</div>';

            if ($enclose_block) {
                $html_block = sprintf($enclose_block, $html_block);
            }
            $blocks = explode('%s', $html_block);

            echo $pager->getLinks() . $blocks[0];

            while ($this->rs->fetch()) {
                echo $this->linkLine(isset($links[$this->rs->link_id]));
            }

            echo $blocks[1] . $blocks[2] . $pager->getLinks();
        }
    }

    private function linkLine($checked)
    {
        $cols = [
            'check' => '<td class="nowrap minimal">' .
                form::checkbox(['entries[]'], $this->rs->link_id, ['checked' => $checked]) .
                '</td>',
            'title' => '<td class="nowrap" scope="row">' .
                '<a href="' . $this->core->adminurl->get(
                    'admin.plugin.cinecturlink2',
                    ['part' => 'link', 'linkid' => $this->rs->link_id, 'redir' => $this->redir]
                ) . '" title="' . __('Edit') . '">' .
                html::escapeHTML($this->rs->link_title) . '</a>' .
                '</td>',
            'author' => '<td class="nowrap">' .
                html::escapeHTML($this->rs->link_author) .
                '</td>',
            'desc' => '<td class="maximal">' .
                html::escapeHTML($this->rs->link_desc) .
                '</td>',
            'link' => '<td class="nowrap">' .
                '<a href="' . $this->rs->link_url . '" title="' .
                    html::escapeHTML($this->rs->link_url) .
                '">' . __('URL') . '</a> ' .
                '<a href="' . $this->rs->link_img . '" title="' .
                    html::escapeHTML($this->rs->link_img) .
                '">' . __('image') . '</a> ' .
                '</td>',
            'cat' => '<td class="nowrap minimal">' .
                '<a href="' . $this->core->adminurl->get(
                    'admin.plugin.cinecturlink2',
                    ['part' => 'cat', 'catid' => $this->rs->cat_id, 'redir' => $this->redir]
                ) . '" title="' . __('Edit') . '">' .
                html::escapeHTML($this->rs->cat_title) . '</a>' .
                '</td>',
            'note' => '</td>' .
                '<td class="nowrap count minimal">' .
                html::escapeHTML($this->rs->link_note) . '/20' .
                '</td>',
            'date' => '<td class="nowrap count minimal">' .
                dt::dt2str(
                    $this->core->blog->settings->system->date_format . ', ' . $this->core->blog->settings->system->time_format,
                    $this->rs->link_upddt,
                    $this->core->auth->getInfo('user_tz')
                ) .
                '</td>'
        ];

        $cols = new ArrayObject($cols);
        $this->userColumns('c2link', $cols);

        return '<tr class="line">' . implode(iterator_to_array($cols)) . '</tr>' . "\n";
    }
}
