<?php declare(strict_types=1);

namespace XoopsModules\Mymenus;

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

/**
 * Class Builder
 */
class Builder
{
    public $parents = [];
    public $output  = [];

    /**
     * @param $array
     */
    public function __construct($array)
    {
        $this->addMenu($array);
    }

    /**
     * @param $array
     */
    public function addMenu($array): void
    {
        foreach ($array as $item) {
            $this->add($item);
        }
    }

    /**
     * @param $item
     */
    public function add($item): void
    {
        $this->parents[$item['pid']][] = $item;
    }

    /**
     * @param int $pid
     */
    public function buildMenus($pid = 0): void
    {
        static $idx = -1;
        static $level = -1;
        ++$level;
        $first = true;

        foreach ($this->parents[$pid] as $item) {
            ++$idx;

            $this->output[$idx]['oul']    = false;
            $this->output[$idx]['oli']    = false;
            $this->output[$idx]['close']  = '';
            $this->output[$idx]['cul']    = false;
            $this->output[$idx]['cli']    = false;
            $this->output[$idx]['hassub'] = false;
            $this->output[$idx]['level']  = $level;

            if ($first) {
                $this->output[$idx]['oul'] = true;
                $first                     = false;
            }

            $this->output[$idx]['oli'] = true;

//            $this->output[$idx] = \array_merge($item, $this->output[$idx]);
            // Directly assign values from $item to $this->output[$idx]
            foreach ($item as $key => $value) {
                $this->output[$idx][$key] = $value;
            }


            if (isset($this->parents[$item['id']])) {
                $this->output[$idx]['hassub'] = true;
                $this->buildMenus($item['id']);
            }

            $this->output[$idx]['cli']   = true;
            $this->output[$idx]['close'] .= "</li>\n";
        }
        $this->output[$idx]['cul']   = true;
        $this->output[$idx]['close'] .= "</ul>\n";
        --$level;
    }

    /**
     * @param int $pid
     */
    public function buildUpDown($pid = 0): void
    {
        static $idx = -1;
        $prevWeight = null;
        $up         = 0;
        $down       = 1;
        $counter    = 0;
        $count      = \count($this->parents[$pid]);

        foreach ($this->parents[$pid] as $item) {
            ++$idx;
            $counter++;
            if ($counter == $count) {
                $down = 0;
            } // turn off down link for last entry

            if ($up) {
                $this->output[$idx]['up_weight'] = $prevWeight;
            }
            if ($down) {
                $this->output[$idx]['down_weight'] = ($this->output[$idx]['weight'] ?? 0) + 2;
            }

            $prevWeight = $this->output[$idx]['weight'] ?? 0;
            $up         = 1; // turn on up link for all entries after first one

            if (isset($this->parents[$item['id']])) {
                $this->buildUpDown($item['id']);
            }
        }
    }

    public function buildSelected(): void
    {
        //get the currentpage
        $sel = [];
        //        $queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
        $queryString = Request::getString('QUERY_STRING', '', 'SERVER') ? '?' . Request::getString('QUERY_STRING', '', 'SERVER') : '';
        //        $self         = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . $queryString;
        $self = 'https://' . Request::getString('HTTP_HOST', '', 'SERVER') . Request::getString('SCRIPT_NAME', '', 'SERVER') . $queryString;

        //set a default page in case we don't get matches
        $default = XOOPS_URL . '/index.php';

        //get all matching links
        foreach ($this->output as $idx => $menu) {
            $selected = 0;
            if (!empty($menu['link'])) {
                $selected = (false !== mb_stristr($self, $menu['link'])) ? 1 : $selected;
            }
            $selected = (isset($menu['link']) && $menu['link'] == $self) ? 1 : $selected;
            $selected = (isset($menu['link']) && $menu['link'] == $default) ? 1 : $selected;
            if ($selected) {
                $sel[$idx] = $menu;
            }
        }

        //From those links get only the longer one
        $longlink = '';
        $longidx  = 0;
        foreach ($sel as $idx => $menu) {
            if (mb_strlen($menu['link']) > mb_strlen($longlink)) {
                $longidx  = $idx;
                $longlink = $menu['link'];
            }
        }

        /*
         * When visiting site.com when XOOPS_URL is set to www.site.com
         * longidx is not detected, this IF will prevent blank page
         */
        if (isset($this->output[$longidx])) {
            $this->output[$longidx]['selected']    = true;
            $this->output[$longidx]['topselected'] = true;

            //Now turn all this menu parents to selected
            $this->addSelectedParents($this->output[$longidx]['pid']);
        }
    }

    /**
     * @param $pid
     */
    public function addSelectedParents($pid): void
    {
        foreach ($this->output as $idx => $menu) {
            if (isset($menu['id']) && $menu['id'] == $pid) {
                $this->output[$idx]['selected'] = true;
                $this->addSelectedParents($menu['pid']);
            }
        }
    }

    /**
     * @return array
     */
    public function render()
    {
        $this->buildMenus();
        $this->buildUpDown();
        $this->buildSelected();

        return $this->output;
    }
}
