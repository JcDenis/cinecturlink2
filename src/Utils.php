<?php

declare(strict_types=1);

namespace Dotclear\Plugin\cinecturlink2;

use Dotclear\App;
use Dotclear\Database\{
    AbstractHandler,
    Cursor,
    MetaRecord
};
use Dotclear\Database\Statement\{
    DeleteStatement,
    JoinStatement,
    SelectStatement,
    UpdateStatement
};
use Dotclear\Interface\Core\ConnectionInterface;
use Dotclear\Helper\File\Files;
use Dotclear\Helper\Text;
use Exception;

/**
 * @brief       cinecturlink2 utils class.
 * @ingroup     cinecturlink2
 *
 * @author      Jean-Christian Denis (author)
 * @copyright   GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
class Utils
{
    /**
     * Connection instance.
     *
     * @var     ConnectionInterface     $con
     */
    public $con;

    /**
     * Cinecturlink table name (with prefix).
     *
     * @var     string  $table
     */
    public $table;

    /**
     * Cinecturlink category table name (with prefix)
     *
     * @var     string  $cat_table
     */
    public $cat_table;

    /**
     * Current blog ID.
     *
     * @var     string  $blog
     */
    public $blog;

    /**
     * Contructor.
     */
    public function __construct()
    {
        $this->con       = App::con();
        $this->table     = App::con()->prefix() . My::CINECTURLINK_TABLE_NAME;
        $this->cat_table = App::con()->prefix() . My::CATEGORY_TABLE_NAME;
        $this->blog      = App::blog()->id();
    }

    /**
     * Get links.
     *
     * @param   array<string, mixed>    $params         Query params
     * @param   bool                    $count_only     Count only result
     *
     * @return  MetaRecord  MetaRecord instance
     */
    public function getLinks(array $params = [], bool $count_only = false): MetaRecord
    {
        $sql = new SelectStatement();

        if ($count_only) {
            $sql->column($sql->count($sql->unique('L.link_id')));
        } else {
            if (!empty($params['columns']) && is_array($params['columns'])) {
                $sql->columns($params['columns']);
            }
            $sql->columns([
                'L.link_id',
                'L.blog_id',
                'L.cat_id',
                'L.user_id',
                'L.link_type',
                'L.link_creadt',
                'L.link_upddt',
                'L.link_note',
                'L.link_count',
                'L.link_title',
                'L.link_desc',
                'L.link_author',
                'L.link_lang',
                'L.link_url',
                'L.link_img',
                'U.user_name',
                'U.user_firstname',
                'U.user_displayname',
                'U.user_email',
                'U.user_url',
                'C.cat_title',
                'C.cat_desc',
            ]);
        }

        $sql
            ->from($sql->as($this->table, 'L'), false, true)
            ->join(
                (new JoinStatement())
                    ->inner()
                    ->from($sql->as(App::con()->prefix() . App::auth()::USER_TABLE_NAME, 'U'))
                    ->on('U.user_id = L.user_id')
                    ->statement()
            )
            ->join(
                (new JoinStatement())
                    ->left()
                    ->from($sql->as($this->cat_table, 'C'))
                    ->on('L.cat_id = C.cat_id')
                    ->statement()
            );

        if (!empty($params['join'])) {
            $sql->join($params['join']);
        }

        if (!empty($params['from'])) {
            $sql->from($params['from']);
        }

        $sql->where('L.blog_id = ' . $sql->quote($this->blog));

        if (isset($params['link_type'])) {
            if (is_array($params['link_type']) && !empty($params['link_type'])) {
                $sql->and('L.link_type' . $sql->in($params['link_type']));
            } elseif ($params['link_type'] != '') {
                $sql->and('L.link_type = ' . $sql->quote($params['link_type']));
            }
        } else {
            $sql->and('L.link_type = ' . $sql->quote('cinecturlink'));
        }

        if (!empty($params['link_id'])) {
            if (is_array($params['link_id'])) {
                array_walk($params['link_id'], function (&$v, $k) { if ($v !== null) { $v = (int) $v;}});
            } else {
                $params['link_id'] = [(int) $params['link_id']];
            }
            $sql->and('L.link_id' . $sql->in($params['link_id']));
        }

        if (!empty($params['cat_id'])) {
            if (is_array($params['cat_id'])) {
                array_walk($params['cat_id'], function (&$v, $k) {
                    if ($v !== null) {
                        $v = (int) $v;
                    }
                });
            } else {
                $params['cat_id'] = [(int) $params['cat_id']];
            }
            $sql->and('L.cat_id' . $sql->in($params['cat_id']));
        }
        if (!empty($params['cat_title'])) {
            $sql->and('C.cat_title = ' . $sql->quote($params['cat_title']));
        }

        if (!empty($params['link_title'])) {
            $sql->and('L.link_title = ' . $sql->quote($params['link_title']));
        }

        if (!empty($params['link_lang'])) {
            $sql->and('L.link_lang = ' . $sql->quote($params['link_lang']));
        }

        if (!empty($params['q']) && is_string($params['q'])) {
            $params['q'] = (string) str_replace('*', '%', strtolower($params['q']));
            $words       = Text::splitWords($params['q']);

            if (!empty($words)) {
                foreach ($words as $i => $w) {
                    $words[$i] = $sql->like('LOWER(L.link_title)', '%' . $sql->escape($w) . '%');
                }
                $sql->and($words);
            }
        }

        if (!empty($params['sql'])) {
            $sql->sql($params['sql']);
        }

        if (!$count_only) {
            if (!empty($params['order'])) {
                $sql->order($sql->escape($params['order']));
            } else {
                $sql->order('L.link_upddt DESC');
            }
        }

        if (!$count_only && !empty($params['limit'])) {
            $sql->limit($params['limit']);
        }

        return $sql->select() ?? MetaRecord::newFromArray([]);
    }

    /**
     * Add link.
     *
     * @param   Cursor  $cur    Cursor instance
     */
    public function addLink(Cursor $cur): int
    {
        $this->con->writeLock($this->table);

        try {
            if ($cur->link_title == '') {
                throw new Exception(__('No link title'));
            }
            if ($cur->link_desc == '') {
                throw new Exception(__('No link description'));
            }
            if ('' == $cur->link_note) {
                $cur->link_note = 10;
            }
            if (0 > $cur->link_note || $cur->link_note > 20) {
                $cur->link_note = 10;
            }

            $cur->link_id     = $this->getNextLinkId();
            $cur->blog_id     = $this->blog;
            $cur->user_id     = App::auth()->userID();
            $cur->link_creadt = date('Y-m-d H:i:s');
            $cur->link_upddt  = date('Y-m-d H:i:s');
            $cur->link_pos    = 0;
            $cur->link_count  = 0;
            $cur->insert();
            $this->con->unlock();
        } catch (Exception $e) {
            $this->con->unlock();

            throw $e;
        }
        $this->trigger();

        # --BEHAVIOR-- cinecturlink2AfterAddLink
        App::behavior()->callBehavior('cinecturlink2AfterAddLink', $cur);

        return (int) $cur->link_id;
    }

    /**
     * Update link.
     *
     * @param   int     $id         Link ID
     * @param   Cursor  $cur        Cursor instance
     * @param   bool    $behavior   Call related behaviors
     *
     * @return  int     The link ID
     */
    public function updLink(int $id, Cursor $cur, bool $behavior = true): int
    {
        if (empty($id)) {
            throw new Exception(__('No such link ID'));
        }

        $cur->link_upddt = date('Y-m-d H:i:s');

        $sql = new UpdateStatement();
        $sql
            ->where('blog_id = ' . $sql->quote($this->blog))
            ->and('link_id = ' . $id)
            ->update($cur);

        if ($behavior) {
            # --BEHAVIOR-- cinecturlink2AfterUpdLink
            App::behavior()->callBehavior('cinecturlink2AfterUpdLink', $cur, $id);
        }

        return $id;
    }

    /**
     * Delete link.
     *
     * @param   int     $id     Link ID
     */
    public function delLink(int $id): void
    {
        if (empty($id)) {
            throw new Exception(__('No such link ID'));
        }

        # --BEHAVIOR-- cinecturlink2BeforeDelLink
        App::behavior()->callBehavior('cinecturlink2BeforeDelLink', $id);

        $sql = new DeleteStatement();
        $sql
            ->from($this->table)
            ->where('blog_id = ' . $sql->quote($this->blog))
            ->and('link_id = ' . $id)
            ->delete();

        $this->trigger();
    }

    /**
     * Get next link ID.
     *
     * @return  int     Next link ID
     */
    private function getNextLinkId(): int
    {
        $sql = new SelectStatement();

        $rs = $sql
            ->column($sql->max('link_id'))
            ->from($this->table)
            ->select();

        return is_null($rs) ? 1 : (int) $rs->f(0) + 1;
    }

    /**
     * Get categories.
     *
     * @param   array<string, mixed>    $params         Query params
     * @param   bool                    $count_only     Count only result
     *
     * @return  MetaRecord  Record instance
     */
    public function getCategories(array $params = [], bool $count_only = false): MetaRecord
    {
        $sql = new SelectStatement();

        if ($count_only) {
            $sql->column($sql->count($sql->unique('C.cat_id')));
        } else {
            if (!empty($params['columns']) && is_array($params['columns'])) {
                $sql->columns($params['columns']);
            }
            $sql->columns([
                'C.cat_id',
                'C.blog_id',
                'C.cat_title',
                'C.cat_desc',
                'C.cat_pos',
                'C.cat_creadt',
                'C.cat_upddt',
            ]);
        }

        $sql->from($sql->as($this->cat_table, 'C'));

        if (!empty($params['from'])) {
            $sql->from($params['from']);
        }

        $sql->where('C.blog_id = ' . $sql->quote($this->blog));

        if (!empty($params['cat_id'])) {
            if (is_array($params['cat_id'])) {
                array_walk($params['cat_id'], function (&$v, $k) {
                    if ($v !== null) {
                        $v = (int) $v;
                    }
                });
            } else {
                $params['cat_id'] = [(int) $params['cat_id']];
            }
            $sql->and('C.cat_id ' . $sql->in($params['cat_id']));
        }

        if (isset($params['exclude_cat_id']) && $params['exclude_cat_id'] !== '') {
            if (is_array($params['exclude_cat_id'])) {
                array_walk($params['exclude_cat_id'], function (&$v, $k) {
                    if ($v !== null) {
                        $v = (int) $v;
                    }
                });
            } else {
                $params['exclude_cat_id'] = [(int) $params['exclude_cat_id']];
            }
            $sql->and('C.cat_id NOT ' . $sql->in($params['exclude_cat_id']));
        }

        if (!empty($params['cat_title'])) {
            $sql->and('C.cat_title = ' . $sql->quote($params['cat_title']));
        }

        if (!empty($params['sql'])) {
            $sql->sql($params['sql']);
        }

        if (!$count_only) {
            if (!empty($params['order'])) {
                $sql->order($sql->escape($params['order']));
            } else {
                $sql->order('cat_pos ASC');
            }
        }

        if (!$count_only && !empty($params['limit'])) {
            $sql->limit($params['limit']);
        }

        return $sql->select() ?? MetaRecord::newFromArray([]);
    }

    /**
     * Add category.
     *
     * @param   Cursor  $cur    Cursor instance
     *
     * @return  int     New category ID
     */
    public function addCategory(Cursor $cur): int
    {
        $this->con->writeLock($this->cat_table);

        try {
            if ($cur->cat_title == '') {
                throw new Exception(__('No category title'));
            }
            if ($cur->cat_desc == '') {
                throw new Exception(__('No category description'));
            }

            $cur->cat_id     = $this->getNextCatId();
            $cur->cat_pos    = $this->getNextCatPos();
            $cur->blog_id    = $this->blog;
            $cur->cat_creadt = date('Y-m-d H:i:s');
            $cur->cat_upddt  = date('Y-m-d H:i:s');
            $cur->insert();
            $this->con->unlock();
        } catch (Exception $e) {
            $this->con->unlock();

            throw $e;
        }
        $this->trigger();

        return (int) $cur->cat_id;
    }

    /**
     * Update category.
     *
     * @param   int     $id     Category ID
     * @param   Cursor  $cur    Cursor instance
     *
     * @return  int     The category ID
     */
    public function updCategory(int $id, Cursor $cur): int
    {
        if (empty($id)) {
            throw new Exception(__('No such category ID'));
        }

        $cur->cat_upddt = date('Y-m-d H:i:s');

        $sql = new UpdateStatement();
        $sql
            ->where('blog_id = ' . $sql->quote($this->blog))
            ->and('cat_id = ' . $id)
            ->update($cur);

        $this->trigger();

        return $id;
    }

    /**
     * Delete category.
     *
     * @param   int     $id     Category ID
     */
    public function delCategory(int $id): void
    {
        if (empty($id)) {
            throw new Exception(__('No such category ID'));
        }

        $sql = new DeleteStatement();
        $sql
            ->from($this->cat_table)
            ->where('blog_id = ' . $sql->quote($this->blog))
            ->and('cat_id = ' . $id)
            ->delete();

        # Update link cat to NULL
        $cur = $this->con->openCursor($this->table);
        $cur->setField('cat_id', null);
        $cur->setField('link_upddt', date('Y-m-d H:i:s'));

        $sql = new UpdateStatement();
        $sql
            ->where('blog_id = ' . $sql->quote($this->blog))
            ->and('cat_id = ' . $id)
            ->update($cur);

        $this->trigger();
    }

    /**
     * Get next category ID.
     *
     * @return  int     Next category ID
     */
    private function getNextCatId(): int
    {
        $sql = new SelectStatement();

        $rs = $sql
            ->column($sql->max('cat_id'))
            ->from($this->cat_table)
            ->select();

        return is_null($rs) ? 1 : (int) $rs->f(0) + 1;
    }

    /**
     * Get next category position.
     *
     * @return  int     Next category position
     */
    private function getNextCatPos(): int
    {
        $sql = new SelectStatement();

        $rs = $sql
            ->column($sql->max('cat_pos'))
            ->from($this->cat_table)
            ->where('blog_id = ' . $sql->quote($this->blog))
            ->select();

        return is_null($rs) ? 1 : (int) $rs->f(0) + 1;
    }

    /**
     * Trigger event.
     */
    private function trigger(): void
    {
        App::blog()->triggerBlog();
    }

    /**
     * Check if a directory exists and is writable.
     *
     * @param   string  $root   Root
     * @param   string  $folder Folder to create into root folder
     * @param   bool    $throw  Throw exception or not
     *
     * @return  bool    True if exists and writable
     */
    public static function makePublicDir(string $root, string $folder, bool $throw = false): bool
    {
        if (!is_dir($root . '/' . $folder)) {
            if (!is_dir($root) || !is_writable($root) || !mkdir($root . '/' . $folder)) {
                if ($throw) {
                    throw new Exception(__('Failed to create public folder for images.'));
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Get list of public directories.
     *
     * @return  array<string, string>    Directories
     */
    public static function getPublicDirs(): array
    {
        $dirs = [];
        $all  = Files::getDirList(App::blog()->publicPath());
        if (empty($all['dirs'])) {
            return $dirs;
        }
        foreach ($all['dirs'] as $dir) {
            $dir        = substr($dir, strlen(App::blog()->publicPath()) + 1);
            $dirs[$dir] = $dir;
        }

        return $dirs;
    }
}
