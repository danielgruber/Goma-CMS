<?php defined("IN_GOMA") OR die();
/**
 * Extends the TemplateCaller with some new methods to get content of pages.
 *
 * @package     Goma-CMS\Pages
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.0
 */
class ContentTPLExtension extends Extension {
    /**
     * prepended content
     *
     *@name prependedContent
     *@access public
     */
    public static $prependedContent = array();

    /**
     * appended content
     *
     *@name appendedContent
     *@access public
     */
    public static $appendedContent = array();

    /**
     * active mainbar cache
     *
     *@name active_mainbar
     *@access protected
     */
    protected static $active_mainbar;

    /**
     * methods
     */
    public static $extra_methods = array(
        "level",
        "mainbar",
        "active_mainbar_title",
        "mainbarByID",
        "prendedContent",
        "appendedContent",
        "active_mainbar_url",
        "pageByID",
        "pageByPath",
        "active_mainbar",
        "active_page"
    );

    /**
     * appends content
     *
     * @name appendContent
     * @param string|object|array - content
     * @access public
     */
    public static function appendContent($content) {
        if(is_array($content)) {
            self::$appendedContent = array_merge(self::$appendedContent, $content);
        } else {
            self::$appendedContent[] = $content;
        }
    }

    /**
     * prepends content
     *
     * @name prependContent
     * @param string|object|array - content
     */
    public static function prependContent($content) {
        if(is_array($content)) {
            self::$prependedContent = array_merge(self::$prependedContent, $content);
        } else {
            self::$prependedContent[] = $content;
        }
    }

    /**
     * returns if a mainbar should exist on this level.
     *
     * @param $level
     * @return bool
     */
    public function level($level)
    {
        if($level == 1)
        {
            return true;
        }

        if(!isset(contentController::$activeids[$level - 2]))
        {
            return false;
        }
        $id = contentController::$activeids[$level - 2];
        return (DataObject::count("pages", array("parentid" => $id, "mainbar" => 1)) > 0);

    }

    /**
     * gets data for mainbar
     * @param int $level
     * @return bool|DataObjectSet
     */
    public function mainbar($level = 1)
    {
        if($level == 1)
        {
            return DataObject::get("pages", array("parentid"	=> 0,"mainbar"	=> 1));
        } else
        {
            if(!isset(contentController::$activeids[$level - 2]))
            {
                return false;
            }
            $id = contentController::$activeids[$level - 2];
            return DataObject::get("pages", array("parentid"	=> $id, "mainbar"	=> 1));
        }
    }

    /**
     * gets mainbar items by parentid of page
     *
     * @name mainbarByID
     * @access public
     * @param int page-id of parent page.
     * @return DataObjectSet
     */
    public function mainbarByID($id) {
        return DataObject::get("pages", array("parentid"	=> $id, "mainbar"	=> 1));
    }

    /**
     * returns a page-object by id
     *
     * @name pageByID
     * @access public
     * @param int id
     * @return Pages|false
     */
    public function pageByID($id) {
        return DataObject::get_by_id("pages", $id);
    }

    /**
     * returns a page-object by path
     *
     * @name pageByPath
     * @access public
     * @param string path
     * @return Pages|false
     */
    public function pageByPath($path) {
        return DataObject::get_one("pages", array("path" => array("LIKE" => $path)));
    }

    /**
     * gets the title of the active mainbar
     * @name active_mainbar_title
     * @param  int level
     * @return string|null
     */
    public function active_mainbar_title($level = 2)
    {
        return ($this->active_mainbar($level)) ? $this->active_mainbar($level)->mainbartitle : "";
    }

    /**
     * gets the url of the active mainbar
     * @name active_mainbar_title
     * @param int level
     * @return string|null
     */
    public function active_mainbar_url($level = 2)
    {
        return ($this->active_mainbar($level)) ? $this->active_mainbar($level)->url : null;
    }

    /**
     * returns the active-mainbar-object
     *
     * @name active_mainbar
     * @access public
     * @param int level
     * @return bool|DataObject
     */
    public function active_mainbar($level = 2)
    {

        if(!isset(contentController::$activeids[$level - 2]))
        {
            return false;
        }
        $id = contentController::$activeids[$level - 2];
        if($level == 2 && isset(self::$active_mainbar)) {
            $data = self::$active_mainbar;
        } else {
            $data = DataObject::get_one("pages", array("id"	=> $id));
            if($level == 2) {
                self::$active_mainbar = $data;
            }
        }
        return $data;
    }

    /**
     * returns the active page
     *
     * @name active_page
     * @access public
     * @return bool|DataObject
     */
    public function active_page()
    {

        return $this->active_mainbar(2);
    }

    /**
     * returns the prepended content
     *
     * @access public
     * @return string
     */
    public static function prependedContent() {
        $div = new HTMLNode('div', array(), self::$prependedContent);

        return $div->html();
    }

    /**
     * returns the appended content
     *
     *  @access public
     * @return string
     */
    public static function appendedContent() {
        $div = new HTMLNode('div', array(), self::$appendedContent);

        return $div->html();
    }
}
gObject::extend("tplCaller", "ContentTPLExtension");
