<?php defined("IN_GOMA") OR die();

/**
 * Resolved which classes are allowed to be parent of another class.
 *
 * @package     Goma-CMS\Pages
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */

class ParentResolver {

    /**
     * cache for allowed_parents
     *
     * @var array
     */
    protected static $cache_parent = array();

    /**
     * class-name.
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $baseClassForScan;

    /**
     * @var string
     */
    protected $allowChildVar = "allow_children";

    /**
     * @var string
     */
    protected $allowParentVar = "allow_parent";

    /**
     * given class-name.
     *
     * @param string $className
     * @param string $baseClassForScan base-class where to start scanning for pages.
     */
    public function __construct($className, $baseClassForScan) {
        $this->className = $className;
        $this->baseClassForScan = $baseClassForScan;
    }

    /**
     * returns hash for caching.
     */
    protected function getCacheHash() {
        return $this->className . "_" . $this->baseClassForScan;
    }

    /**
     * returns the result.
     */
    public function getAllowedParents() {
        $cacher = new Cacher("cache_parentsv2");
        if($cacher->checkValid()) {
            self::$cache_parent = $cacher->getData();
        }

        // for performance reason we cache this part
        if(!isset(self::$cache_parent[$this->getCacheHash()]) || self::$cache_parent[$this->getCacheHash()] == array()) {

            $allowed_parents = $this->getAllowedParentsByChildrenVar();

            $allowed_parents = $this->filterParents($allowed_parents);

            self::$cache_parent[$this->getCacheHash()] = $allowed_parents;

            if(PROFILE) Profiler::unmark("pages::allowed_parents", "pages::allowed_parents generate");

            $cacher->write(self::$cache_parent, 86400);

            return $allowed_parents;
        } else {
            if(PROFILE) Profiler::unmark("pages::allowed_parents");
            return self::$cache_parent[$this->getCacheHash()];
        }
    }

    /**
     * gets all allowed parents by the allow_children variable.
     *
     * @return array
     */
    protected function getAllowedParentsByChildrenVar() {
        $allowed_parents = array();

        // first check all pages
        $allPages = array_merge((array) array($this->baseClassForScan), ClassInfo::getChildren($this->baseClassForScan));
        foreach($allPages as $child) {

            // get allowed children for this page
            $allowed = StaticsManager::getStatic($child, $this->allowChildVar);

            if($allowed === null) {
                throw new LogicException("Every Child-Class of Base-Class '".$this->baseClassForScan."' needs " .
                "to have a static variable called '".$this->allowChildVar."'.");
            }

            if(is_array($allowed) && count($allowed) > 0) {
                foreach($allowed as $allow) {
                    $allow = strtolower($allow);
                    // if ! these children are absolutely prohibited
                    if(substr($allow, 0, 1) == "!") {
                        if(ClassManifest::isOfType($this->className, substr($allow, 1))) {
                            unset($allowed_parents[$child]);
                            continue 2;
                        }
                    } else {
                        if(ClassManifest::isOfType($this->className, $allow)) {
                            $allowed_parents[$child] = $child;
                        }
                    }
                }
            }
        }

        return $allowed_parents;
    }

    /**
     * filters parents with given local allow_parents array of class.
     *
     * @param array $allowed_parents currently allowed parents
     * @return array
     */
    protected function filterParents($allowed_parents) {

        $allow_parents = StaticsManager::getStatic($this->className, $this->allowParentVar);

        if($allow_parents === null) {
            throw new LogicException("Class '".$this->className."' needs " .
                "to have a static variable called '".$this->allowParentVar."'.");
        }

        // now filter
        if(is_array($allow_parents) && count($allow_parents) > 0) {
            foreach($allowed_parents as $key => $parent) {

                // set found to false
                $found = false;

                // try find the parent
                foreach($allow_parents as $allow) {
                    $allow = strtolower($allow);
                    if(substr($allow, 0, 1) == "!") {
                        if(ClassManifest::isOfType($parent, substr($allow, 1))) {
                            unset($allowed_parents[$parent]);
                            continue 2;
                        }
                    } else {
                        if(ClassManifest::isOfType($parent, $allow)) {
                            $found = true;
                        }
                    }
                }

                // if not found, unset
                if(!$found) {
                    unset($allowed_parents[$key]);
                }
            }
        }

        return array_values($allowed_parents);
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getBaseClassForScan()
    {
        return $this->baseClassForScan;
    }

    /**
     * @param string $baseClassForScan
     */
    public function setBaseClassForScan($baseClassForScan)
    {
        $this->baseClassForScan = $baseClassForScan;
    }

    /**
     * @return string
     */
    public function getAllowChildVar()
    {
        return $this->allowChildVar;
    }

    /**
     * @param string $allowChildVar
     */
    public function setAllowChildVar($allowChildVar)
    {
        $this->allowChildVar = $allowChildVar;
    }

    /**
     * @return string
     */
    public function getAllowParentVar()
    {
        return $this->allowParentVar;
    }

    /**
     * @param string $allowParentVar
     */
    public function setAllowParentVar($allowParentVar)
    {
        $this->allowParentVar = $allowParentVar;
    }
}
