<?php declare(strict_types=1);

namespace XoopsModules\Mymenus\Plugins\Mymenus;

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
 * @license         https://www.gnu.org/licenses/gpl-2.0.html GNU Public License
 * @since           1.0
 * @author          trabis <lusopoemas@gmail.com>
 */

use Xmf\Request;
use XoopsModules\Mymenus\{
    Registry
};


/**
 * Class PluginItem
 */
class PluginItem extends \XoopsModules\Mymenus\PluginItem
{
    public static function eventBoot(): void
    {
        $registry = Registry::getInstance();
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');

        $user = ($GLOBALS['xoopsUser'] instanceof \XoopsUser) ? $GLOBALS['xoopsUser'] : null;
        if (!$user) {
            $user = $memberHandler->createUser();
            $user->setVar('uid', 0);
            $user->setVar('uname', $GLOBALS['xoopsConfig']['anonymous']);
        }

        $ownerid = Request::getInt('uid', null, 'GET');
        $owner   = $memberHandler->getUser($ownerid);
        //if uid > 0 but user does not exists
        if (!($owner instanceof \XoopsUser)) {
            //create new user
            $owner = $memberHandler->createUser();
        }
        if ($owner->isNew()) {
            $owner->setVar('uid', 0);
            $owner->setVar('uname', $GLOBALS['xoopsConfig']['anonymous']);
        }
        $registry->setEntry('user', $user->getValues());
        $registry->setEntry('owner', $owner->getValues());
        $registry->setEntry('user_groups', ($GLOBALS['xoopsUser'] instanceof \XoopsUser) ? $GLOBALS['xoopsUser']->getGroups() : [XOOPS_GROUP_ANONYMOUS]);
        $registry->setEntry('user_uid', ($GLOBALS['xoopsUser'] instanceof \XoopsUser) ? $GLOBALS['xoopsUser']->getVar('uid') : 0);
        $registry->setEntry('get_uid', Request::getInt('uid', 0, 'GET'));
    }

    public static function eventLinkDecoration(): void
    {
        $registry          = Registry::getInstance();
        $linkArray         = $registry->getEntry('link_array');
        $linkArray['link'] = self::doDecoration($linkArray['link']);
        //if (!eregi('mailto:', $linkArray['link']) && !eregi('://', $linkArray['link'])) {
        if (!\preg_match('/mailto:/i', $linkArray['link']) && !\preg_match('#://#i', $linkArray['link'])) {
            $linkArray['link'] = XOOPS_URL . '/' . $linkArray['link'];  //Do not do this in other decorators
        }
        $registry->setEntry('link_array', $linkArray);
    }

    public static function eventImageDecoration(): void
    {
        $registry  = Registry::getInstance();
        $linkArray = $registry->getEntry('link_array');

        if ($linkArray['image']) {
            // Check if the image value starts with a known icon class prefix
            if (preg_match('/^(fa|fas|far|fab|bi|material-icons)/i', $linkArray['image'])) {
                // If it's an icon class, don't modify the path
                $linkArray['image'] = self::doDecoration($linkArray['image']);
            } elseif (!\filter_var($linkArray['image'], \FILTER_VALIDATE_URL)) {
                // If it's not a full URL and not an icon class, prepend XOOPS_URL
            $linkArray['image'] = XOOPS_URL . '/' . $linkArray['image'];
            $linkArray['image'] = self::doDecoration($linkArray['image']);
            } else {
                // It's a full URL, apply decoration if needed
                $linkArray['image'] = self::doDecoration($linkArray['image']);
            }
            $registry->setEntry('link_array', $linkArray);
        }
    }

    public static function eventTitleDecoration(): void
    {
        $registry           = Registry::getInstance();
        $linkArray          = $registry->getEntry('link_array');
        $linkArray['title'] = self::doDecoration($linkArray['title']);
        $registry->setEntry('link_array', $linkArray);
    }

    public static function eventAltTitleDecoration(): void
    {
        $registry  = Registry::getInstance();
        $linkArray = $registry->getEntry('link_array');
        if (!$linkArray['alt_title']) {
            $linkArray['alt_title'] = $linkArray['title'];
        }
        $linkArray['alt_title'] = self::doDecoration($linkArray['alt_title']);
        $registry->setEntry('link_array', $linkArray);
    }

    /**
     * @param $string
     *
     * @return mixed
     */
    protected static function doDecoration($string)
    {
        $registry = Registry::getInstance();
        //if (!eregi("{(.*\|.*)}", $string, $reg)) {
        if (!\preg_match('/{(.*\|.*)}/i', $string, $reg)) {
            return $string;
        }

        $expression = $reg[0];
        [$validator, $value] = \array_map('\strtolower', \explode('|', $reg[1]));

        //just to prevent any bad admin to get easy passwords
        if ('pass' === $value) {
            return $string;
        }

        if ('user' === $validator) {
            $user   = $registry->getEntry('user');
            $value  = $user[$value] ?? static::getExtraValue('user', $value);
            $string = \str_replace($expression, $value, $string);
        }

        if ('uri' === $validator) {
            $value  = Request::getString($value, 0, 'GET');
            $string = \str_replace($expression, $value, $string);
        }

        if ('owner' === $validator) {
            $owner  = $registry->getEntry('owner');
            $value  = $owner[$value] ?? static::getExtraValue('owner', $value);
            $string = \str_replace($expression, $value, $string);
        }

        return $string;
    }

    public static function eventFormLinkDescription(): void
    {
        $registry    = Registry::getInstance();
        $description = $registry->getEntry('form_link_description');
    }

    public static function eventHasAccess(): void
    {
        $registry = Registry::getInstance();
        $menu     = $registry->getEntry('menu');
        $groups   = $registry->getEntry('user_groups');
        if (0 == $menu['visible'] || !\array_intersect($menu['groups'], $groups)) {
            $registry->setEntry('has_access', 'no');

            return;
        }
        $hooks = \array_intersect($menu['hooks'], \get_class_methods(__CLASS__));

        foreach ($hooks as $method) {
            if (!self::$method()) {
                $registry->setEntry('has_access', 'no');

                return;
            }
        }
    }

    public static function eventAccessFilter(): void
    {
        static::loadLanguage('mymenus');
        $registry                               = Registry::getInstance();
        $accessFilter                           = $registry->getEntry('accessFilter');
        $accessFilter['is_owner']['name']       = \_PL_MYMENUS_MYMENUS_ISOWNER;
        $accessFilter['is_owner']['method']     = 'isOwner';
        $accessFilter['is_not_owner']['name']   = \_PL_MYMENUS_MYMENUS_ISNOTOWNER;
        $accessFilter['is_not_owner']['method'] = 'isNotOwner';
        $registry->setEntry('accessFilter', $accessFilter);
    }

    /**
     * @return bool
     */
    public function isOwner()
    {
        $registry = Registry::getInstance();

        return (0 != $registry->getEntry('user_uid')
                && $registry->getEntry('user_uid') == $registry->getEntry('get_uid'));
    }

    /**
     * @return bool
     */
    public function isNotOwner()
    {
        return !$this->isOwner();
    }

    /**
     * @param string $type
     * @param        $value
     *
     * @return int
     */
    public static function getExtraValue($type, $value)
    {
        $registry = Registry::getInstance();
        $ret      = 0;
        $values   = ['pm_new', 'pm_readed', 'pm_total'];
        if (!\in_array($value, $values, true)) {
            return $ret;
        }

        $entry = $registry->getEntry($type);
        if (!$entry) {
            return $ret;
        }

        $pmHandler = \xoops_getHandler('privmessage');

        if ('pm_new' === $value) {
            $criteria = new \CriteriaCompo(new \Criteria('read_msg', 0));
            $criteria->add(new \Criteria('to_userid', $entry['uid']));
        }

        if ('pm_readed' === $value) {
            $criteria = new \CriteriaCompo(new \Criteria('read_msg', 1));
            $criteria->add(new \Criteria('to_userid', $entry['uid']));
        }

        if ('pm_total' === $value) {
            $criteria = new \Criteria('to_userid', $entry['uid']);
        }

        $entry[$value] = $pmHandler->getCount($criteria);

        $registry->setEntry($type, $entry);

        unset($criteria);

        return $entry[$value];
    }
}
