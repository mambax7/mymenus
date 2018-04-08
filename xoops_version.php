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
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU Public License
 * @package         Mymenus
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>, bleekk <bleekk@outlook.com>
 */

defined('XOOPS_ROOT_PATH') || die('Restricted access');

include __DIR__ . '/preloads/autoloader.php';

$moduleDirName = basename(__DIR__);

// ------------------- Informations ------------------- //
$modversion = [
    'version'             => 1.53,
    'module_status'       => 'Beta 1',
    'release_date'        => '2017/07/20', // YYYY/mm/dd
    'name'                => _MI_MYMENUS_MD_NAME,
    'description'         => _MI_MYMENUS_MD_DESC,
    'author'              => 'Trabis (www.xuups.com), contributors: Mamba, Bleek, Zyspec, Luciorota',
    //    'author_mail'         => " ",
    'author_website_url'  => 'http://xuups.com',
    'author_website_name' => 'XUUPS',
    'credits'             => 'XOOPS Development Team',
    'license'             => 'GPL 2.0 or later',
    'license_url'         => 'www.gnu.org/licenses/gpl-2.0.html/',
    'help'                => 'page=help',
    //
    'release_info'        => 'release_info',
    'release'             => '2017-07-20',
    'release_file'        => XOOPS_URL . "/modules/{$moduleDirName}/docs/release_info file",

    //
    'manual'              => 'link to manual file',
    'manual_file'         => XOOPS_URL . "/modules/{$moduleDirName}/docs/install.txt",
    'min_php'             => '5.5',
    'min_xoops'           => '2.5.9',
    'min_admin'           => '1.2',
    'min_db'              => ['mysql' => '5.5'],
    'image'               => 'assets/images/logoModule.png', // Path and name of the module’s logo
    'official'            => 1, //1 indicates supported by XOOPS Dev Team, 0 means 3rd party supported
    'dirname'             => $moduleDirName,
    // Local icons paths
    'modicons16'          => 'assets/images/icons/16',
    'modicons32'          => 'assets/images/icons/32',
    'demo_site_url'       => 'https://xoops.org',
    'demo_site_name'      => 'XOOPS Demo Site',
    'support_url'         => 'https://xoops.org/modules/newbb',
    'support_name'        => 'Support Forum',
    'module_website_url'  => 'www.xoops.org',
    'module_website_name' => 'XOOPS Project',
    // Admin things
    'hasAdmin'            => 1,
    'system_menu'         => 1,
    'adminindex'          => 'admin/index.php',
    'adminmenu'           => 'admin/menu.php',
    // Main Menu
    'hasMain'             => 0,
    // Install/Update
    //'onInstall'           => "include/oninstall.php",
    //'onUninstall'         => "include/onuninstall.php",
    'onUpdate'            => 'include/onupdate.php',
    // Search
    'hasSearch'           => 0,
    // Comments
    'hasComments'         => 0,
    // Notification
    'hasNotification'     => 0
];

require_once XOOPS_ROOT_PATH . '/modules/' . $modversion['dirname'] . '/include/constants.php';

// ------------------- Help files ------------------- //
$modversion['helpsection'] = [
    ['name' => _MI_MYMENUS_HELP_OVERVIEW, 'link' => 'page=help'],
    ['name' => _MI_MYMENUS_HELP_SKINS, 'link' => 'page=skins'],
    ['name' => _MI_MYMENUS_HELP_USAGE, 'link' => 'page=usage'],
    ['name' => _MI_MYMENUS_DISCLAIMER, 'link' => 'page=disclaimer'],
    ['name' => _MI_MYMENUS_LICENSE, 'link' => 'page=license'],
    ['name' => _MI_MYMENUS_SUPPORT, 'link' => 'page=support'],
];

// ------------------- Mysql ------------------- //
// Sql file (must contain sql generated by phpMyAdmin or phpPgAdmin)
// All tables should not have any prefix!
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';

// Tables created by sql file (without prefix!)
$modversion['tables'] = [
    $moduleDirName . '_links',
    $moduleDirName . '_menus'
];

// ------------------- Blocks ------------------- //
$modversion['blocks'][] = [
    'file'        => $moduleDirName . '_block.php',
    'name'        => _MI_MYMENUS_BLK,
    'description' => _MI_MYMENUS_BLK_DSC,
    'show_func'   => $moduleDirName . '_block_show',
    'edit_func'   => $moduleDirName . '_block_edit',
    'options'     => '0|default|0|block||', // mid|moduleSkin|useThemeSkin|displayMethod|unique_id|themeSkin
    'template'    => $moduleDirName . '_block.tpl'
];

// ------------------- Templates ------------------- //

// ------------------- Config ------------------- //
$modversion['config'][] = [
    'name'        => 'admin_perpage',
    'title'       => '_MI_MYMENUS_CONF_ADMINPERPAGE',
    'description' => '_MI_MYMENUS_CONF_ADMINPERPAGE_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => '10',
    'options'     => ['5' => 5, '10' => 10, '15' => 15, '20' => 20, '25' => 25, '30' => 30, '50' => 50]
];

$modversion['config'][] = [
    'name'        => 'assign_method',
    'title'       => '_MI_MYMENUS_CONF_ASSIGN_METHOD',
    'description' => '_MI_MYMENUS_CONF_ASSIGN_METHOD_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'text',
    'default'     => 'xotheme',
    'options'     => [
        _MI_MYMENUS_CONF_ASSIGN_METHOD_XOOPSTPL => 'xoopstpl',
        _MI_MYMENUS_CONF_ASSIGN_METHOD_XOTHEME  => 'xotheme'
    ]
];
$modversion['config'][] = [
    'name'        => 'unique_id_prefix',
    'title'       => '_MI_MYMENUS_CONF_UNIQUE_ID_PREFIX',
    'description' => '_MI_MYMENUS_CONF_UNIQUE_ID_PREFIX_DSC',
    'formtype'    => 'text',
    'valuetype'   => 'text',
    'default'     => 'xoops_menu_'
];
