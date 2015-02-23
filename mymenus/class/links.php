<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU Public License
 * @package         Mymenus
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 * @version         $Id: links.php 12944 2015-01-23 13:05:09Z beckmi $
 */

defined("XOOPS_ROOT_PATH") or exit("Restricted access");

/**
 * Class MymenusLinks
 */
class MymenusLinks extends XoopsObject
{
    /**
     * constructor
     */
    public function __construct()
    {
        $this->initVar('id', XOBJ_DTYPE_INT);
        $this->initVar('pid', XOBJ_DTYPE_INT);
        $this->initVar('mid', XOBJ_DTYPE_INT);
        $this->initVar('title', XOBJ_DTYPE_TXTBOX);
        $this->initVar('alt_title', XOBJ_DTYPE_TXTBOX);
        $this->initVar('visible', XOBJ_DTYPE_INT, 1);
        $this->initVar('link', XOBJ_DTYPE_TXTBOX);
        $this->initVar('weight', XOBJ_DTYPE_INT, 255);
        $this->initVar('target', XOBJ_DTYPE_TXTBOX);
        $this->initVar('groups', XOBJ_DTYPE_ARRAY, serialize(array(XOOPS_GROUP_ANONYMOUS, XOOPS_GROUP_USERS)));
        $this->initVar('hooks', XOBJ_DTYPE_ARRAY, serialize(array()));
        $this->initVar('image', XOBJ_DTYPE_TXTBOX);
        $this->initVar('css', XOBJ_DTYPE_TXTBOX);
    }

    /**
     * @return bool
     */
    public function checkAccess()
    {
        $hooks              = $this->getHooks();
        $hooks['mymenus'][] = 'checkAccess';
        foreach ($hooks as $hookname => $hook) {
            if (!mymenus_hook($hookname, 'checkAccess', array('links' => $this))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getHooks()
    {
        $ret  = array();
        $data = $this->getVar('hooks', 'n');
        if (!$data) {
            return $ret;
        }
        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            $line   = trim($line);
            $line   = explode('|', $line);
            $hook   = trim($line[0]);
            $method = isset($line[1]) ? trim($line[1]) : '';
            //$info = split(',', trim($line[0]));
            $ret[$hook][] = $method;
        }

        return $ret;
    }
}

/**
 * Class MymenusLinksHandler
 */
class MymenusLinksHandler extends XoopsPersistableObjectHandler
{

    /**
     * @param null|object $db
     */
    public function __construct(&$db)
    {
        parent::__construct($db, 'mymenus_links', 'MymenusLinks', 'id', 'title');
    }

    /**
     * @param $obj
     */
    public function update_weights(XoopsObject &$obj)
    {
        $sql = "UPDATE " . $this->table
               . " SET weight = weight+1"
               . " WHERE weight >= " . $obj->getVar('weight')
               . " AND id <> " . $obj->getVar('id')
               /*. " AND pid = " . $obj->getVar('pid')*/
               . " AND mid = " . $obj->getVar('mid');
        $this->db->queryF($sql);

        $sql    = "SELECT id FROM " . $this->table
                  . " WHERE mid = " . $obj->getVar('mid')
                  /*. " AND pid = " . $obj->getVar('pid')*/
                  . " ORDER BY weight ASC";
        $result = $this->db->query($sql);
        $i      = 1;  //lets start at 1 please!
        while (list($id) = $this->db->fetchrow($result)) {
            $sql = "UPDATE " . $this->table
                   . " SET weight = {$i}"
                   . " WHERE id = {$id}";
            $this->db->queryF($sql);
            ++$i;
        }
    }
}
