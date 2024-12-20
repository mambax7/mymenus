<?php declare(strict_types=1);

namespace XoopsModules\Mymenus;

/**
 * Class Updater
 */
class Updater
{
    // =========================================================================================
    // This class updates any existing table of a < 1.50 version to the format used
    // in the release of Mymenus 1.51
    // =========================================================================================

    /**
     * @param $module
     *
     * @return bool
     */
    public static function checkInfoTemplates(\XoopsObject $module)
    {
        $err = true;
        if (!\file_exists(XOOPS_ROOT_PATH . '/modules/' . $module->getInfo('dirname') . '/templates/blocks/' . $module->getInfo('dirname') . '_block.tpl')) {
            $module->setErrors('Template ' . $module->getInfo('dirname') . '_block.tpl not exists!');
            $err = false;
        }

        return $err;
    }

    /**
     * @param $module
     *
     * @return bool
     */
    public static function checkInfoTable(\XoopsObject $module)
    {
        //    global $xoopsDB;
        $err = true;

        $tables_menus = [
            'id'    => 'int(5) NOT NULL auto_increment',
            'title' => "varchar(255) NOT NULL default ''",
            'css'   => "varchar(255) NOT NULL default ''",
        ];

        $tables_links = [
            'id'        => 'int(5) NOT NULL auto_increment',
            'pid'       => "int(5) NOT NULL default '0'",
            'mid'       => "int(5) NOT NULL default '0'",
            'title'     => "varchar(150) NOT NULL default ''",
            'alt_title' => "varchar(255) NOT NULL default ''",
            'visible'   => "tinyint(1) NOT NULL default '0'",
            'link'      => 'varchar(255) default NULL',
            'weight'    => "tinyint(4) NOT NULL default '0'",
            'target'    => 'varchar(10) default NULL',
            'groups'    => 'text default NULL',
            'hooks'     => 'text default NULL',
            'image'     => 'varchar(255) default NULL',
            'css'       => 'varchar(255) default NULL',
        ];

        /*

        // CREATE or ALTER 'mymenus_menus' table
        if (!InfoTableExists($GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . '_menus')) {
            $sql = "CREATE TABLE " . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . "_menus (";
            foreach ($tables_menus as $s => $w) {
                $sql .= " " . $s . " " . $w . ",";
            }
            $sql .= " PRIMARY KEY (id)); ";
            echo $sql;
            $result = $GLOBALS['xoopsDB']->queryF($sql);
            if (!$result) {
                $module->setErrors("Can't create Table " . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . '_menus');
                return false;
            } else {
                $sql    = "INSERT INTO " . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . "_menus (id,title) VALUES (1,'Default')";
                $result = $GLOBALS['xoopsDB']->queryF($sql);
            }
        } else {
            foreach ($tables_menus as $s => $w) {
                if (!InfoColumnExists($GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . '_menus', $s)) {
                    $sql    = "ALTER TABLE " . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . "_menus ADD " . $s . " " . $w . ";";
                    $result = $GLOBALS['xoopsDB']->queryF($sql);
                } else {
                    $sql    = "ALTER TABLE " . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . "_menus CHANGE " . $s . " " . $s . " " . $w . ";";
                    $result = $GLOBALS['xoopsDB']->queryF($sql);
                }
            }
        }
    */

        self::createUpdateTable($tables_menus, '_menus', $module);

        // RENAME TABLE 'mymenus_menu' TO 'mymenus_links'
        if (!InfoTableExists($GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . '_links')) {
            if (InfoTableExists($GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . '_menu')) {
                $sql    = 'RENAME TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . '_menu TO ' . $GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . '_links;';
                $result = $GLOBALS['xoopsDB']->queryF($sql);
                if (!$result) {
                    $module->setErrors("Can't rename Table " . $GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . '_menu');

                    return false;
                }
            }
        }

        /*
      //---------------------------
          // CREATE or ALTER 'mymenus_links' table
          if (!InfoTableExists($GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . "_links")) {
              $sql = "CREATE TABLE " . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . "_links ( ";
              foreach ($tables_links as $c => $w) {
                  $sql .= " " . $c . " " . $w . ",";
              }
              $sql .= "  PRIMARY KEY  (id) ) ;";
              $result = $GLOBALS['xoopsDB']->queryF($sql);
              if (!$result) {
                  $module->setErrors("Can't create Table " . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . "_links");
                  $sql    = 'DROP TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . '_menus';
                  $result = $GLOBALS['xoopsDB']->queryF($sql);
                  return false;
              }
          } else {
              foreach ($tables_links as $s => $w) {
                  if (!InfoColumnExists($GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . '_links', $s)) {
                      $sql    = "ALTER TABLE " . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . "_links ADD " . $s . " " . $w . ";";
                      $result = $GLOBALS['xoopsDB']->queryF($sql);
                  } else {
                      $sql    = "ALTER TABLE " . $GLOBALS['xoopsDB']->prefix($module->getInfo("dirname")) . "_links CHANGE " . $s . " " . $s . " " . $w . ";";
                      $result = $GLOBALS['xoopsDB']->queryF($sql);
                  }
              }
          }

          //--------------------------
      */

        self::createUpdateTable($tables_links, '_links', $module);

        return true;
    }

    /**
     * @param array  $table
     * @param string $tablename
     * @return bool|null
     */
    public static function createUpdateTable($table, $tablename, \XoopsObject $module)
    {
        if (!InfoTableExists($GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . $tablename)) {
            $sql = 'CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . $tablename . ' (';
            foreach ($table as $s => $w) {
                $sql .= ' ' . $s . ' ' . $w . ',';
            }
            $sql .= ' PRIMARY KEY (id)); ';
            //    echo $sql;
            $result = $GLOBALS['xoopsDB']->queryF($sql);
            if (!$result) {
                $module->setErrors("Can't create Table " . $GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . $tablename);

                if ('_menu' === $tablename) {
                    return false;
                }

                if ('_links' === $tablename) {
                    $sql    = 'DROP TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . '_menus';
                    $result = $GLOBALS['xoopsDB']->queryF($sql);

                    return false;
                }
            } else {
                if ('_menu' === $tablename) {
                    $sql    = 'INSERT INTO ' . $GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . $tablename . " (id,title) VALUES (1,'Default')";
                    $result = $GLOBALS['xoopsDB']->queryF($sql);
                }
            }
        } else {
            foreach ($table as $s => $w) {
                if (!InfoColumnExists($GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . $tablename, $s)) {
                    $sql    = 'ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . $tablename . ' ADD ' . $s . ' ' . $w . ';';
                    $result = $GLOBALS['xoopsDB']->queryF($sql);
                } else {
                    $sql    = 'ALTER TABLE ' . $GLOBALS['xoopsDB']->prefix($module->getInfo('dirname')) . $tablename . ' CHANGE ' . $s . ' ' . $s . ' ' . $w . ';';
                    $result = $GLOBALS['xoopsDB']->queryF($sql);
                }
            }
        }

        return null;
    }
}
