<?php defined("IN_GOMA") OR die();

/**
 * @package goma framework
 * @link http://goma-cms.org
 * @license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author Goma-Team
 * @version 1.3.2
 *
 * last modified: 04.08.2015
 */
class HTMLNode extends gObject
{
    /**
     * tag
     *
     * @name tag
     * @access protected
     */
    protected $tag;

    /**
     * attributes
     *
     * @name attr
     * @access protected
     */
    protected $attr;

    /**
     * content
     * this can be an array of arrays or objects or strings or can be a simple string
     *
     * @name content
     * @access public
     * @var array
     */
    public $content;

    /**
     * css
     *
     * @name css
     * @access protected
     */
    protected $css;

    /**
     * if a parent node exists this is a link to it
     *
     * @name parentNode
     * @access public
     */
    public $parentNode;

    /**
     * this array contains all non-closing-tags
     *
     * @name non_closing_tags
     * @access public
     */
    public static $non_closing_tags = array('input', 'img', 'embed', 'br');

    /**
     * these tags are inline-tags, so they don't need whitespace between the content
     *
     * @name inline_tags
     * @access public
     * @var array
     */
    public static $inline_tags = array("span", "label", "pre", "textarea", "a", "legend");

    /**
     * @name __construct
     * @access public
     * @param string - tag
     * @param array - attributes
     * @param null|array|string|object - content
     */
    public function __construct($tag, $attr = array(), $content = null)
    {
        $this->tag = trim(strtolower($tag));

        if (is_string($attr)) {
            throwError(6, 'PHP-Error', "HTMLNode::__construct: Second argument must be Array");
        }

        if (isset($attr["style"])) {
            $style = $attr["style"];
            $this->parseCSS($style);
            unset($attr["style"]);
            $this->attr = $attr;
        } else {
            $this->attr = $attr;
        }

        if (is_array($content)) {
            $this->content = $content;
        } else if ($content !== null) {
            $this->content = array($content);
        } else {
            $this->content = array();
        }
    }

    /**
     * gets the content of the node as HTML
     * if you give a argument, you can set html
     *
     * @name HTML
     * @access public
     * @param html - if you want to set it
     * @param optional - string - whitespace
     */
    public function html($new = null, $whitespace = null)
    {

        if (PROFILE) Profiler::mark("HTMLNode::html");
        if ($new !== null) {
            if (is_array($new)) {
                $this->content = $new;
            } else {
                $this->content = array($new);
            }
        }
        $content = "";
        if (is_array($this->content)) {
            foreach ($this->content as $node) {
                if (is_object($node) && gObject::method_exists($node, "render")) {
                    if ($whitespace !== null) {
                        $content .= $whitespace . $node->render($whitespace . '          ') . "\n\n";
                    } else {
                        $content .= $node->render();
                    }
                } else if (is_object($node)) {
                    $content .= $node->__toString();
                } else {
                    if ($whitespace !== null) {
                        $content .= $whitespace . $node;
                    } else {
                        $content .= $node;
                    }
                }
            }
        } else {
            $content = $this->content;
        }
        if (PROFILE) Profiler::unmark("HTMLNode::html");

        return $content;
    }

    /**
     * gets the content of the node as text
     *
     * if you give a argument, you can set text
     *
     * @name text
     * @access public
     * @param string $new if you want to set it.
     * @return string
     */
    public function text($new = null)
    {

        if ($new !== null) {
            if (!is_string($new)) {
                throw new InvalidArgumentException("New content must be a string for HTMLNode::text.");
            }
            $this->content = array(convert::raw2xml($new));

            return convert::raw2xml($new);
        }

        $content = "";
        foreach ($this->content as $node) {
            $content .= convert::raw2xml((string)$node);
        }

        return $content;
    }

    /**
     * if this field is an textarea or an input you can set or get the value
     *
     * @name val
     * @access public
     * @return array|null|string
     */
    public function val($value = null)
    {
        if ($value !== null) {
            if ($this->tag == "input") {
                return $this->value = convert::raw2xml($value);
            } else {
                return $this->html(convert::raw2xml($value));
            }
        }

        if ($this->tag == "input") {
            return $this->value;
        } else {
            return $this->html();
        }
    }

    /**
     * gets the parent node
     *
     * @name parent
     * @access public
     * @return HTMLNode|null
     */
    public function parent()
    {
        return isset($this->parentNode) ? $this->parentNode : null;
    }

    /**
     * next silbing
     *
     * @name next
     * @access public
     * @return HTMLNode|null
     */
    public function next()
    {
        if ($parent = $this->parent()) {
            $children = $parent->Children();
            $key = array_search($this, $children);
            $key++;

            return isset($children[$key]) ? $children[$key] : null;
        } else {
            return null;
        }
    }

    /**
     * gets a note by its index
     *
     * @name getNode
     * @access public
     * @param int $index
     * @return string|HTMLNode|null
     */
    public function getNode($index)
    {
        if (isset($this->content[$index])) {
            return $this->content[$index];
        }

        return null;
    }

    /**
     * sets the parent Node
     *
     * @name setParentNode
     * @access public
     */
    public function setParentNode($node)
    {
        $this->parentNode = $node;
    }

    /**
     * gets the children of a node
     *
     * @name children
     * @access public
     * @return array
     */
    public function Children()
    {
        return $this->content;
    }

    /**
     * sets or gets an attrbute
     * Please just edit the attributes on the Object instead of using this
     *
     * @name attr
     * @access public
     * @param name
     * @param value - optional
     * @return null|string
     */
    public function attr($name, $value = null)
    {
        if ($value !== null) {
            $this->__set($name, $value);
            return $this;
        }

        return $this->__get($name);
    }

    /**
     * removes an attribute
     *
     * @name removeAttr
     * @param string - name
     * @return $this
     */
    public function removeAttr($name)
    {
        $this->__unset($name);
        return $this;
    }

    /**
     * sets or gets a css-attribute
     *
     * @name css
     * @access public
     * @param string - name of the attrbiute
     * @param string - if you want to set this value
     * @return null|string
     */
    public function css($name, $value = null)
    {
        if ($value !== null) {
            $this->css[$name] = $value;

            return $value;
        } else if (isset($this->css[$name])) {
            return $this->css[$name];
        } else {
            return null;
        }
    }

    /**
     * appends a node to the children
     *
     * @param $node
     * @return $this
     */
    public function append($node)
    {
        if (is_array($node)) {
            foreach ($node as $_node) {
                $this->content[] = $_node;

                if (is_object($_node)) {
                    $this->content[count($this->content) - 1]->setParentNode($this);
                }
                unset($_node);

            }
        } else {
            $this->content[] = $node;
            if (is_object($node)) {
                $this->content[count($this->content) - 1]->setParentNode($this);
            }
            unset($node);
        }
        return $this;
    }

    /**
     * prepends a node before the children
     * @param $node
     * @return $this
     */
    public function prepend($node)
    {
        if (is_array($node)) {
            foreach ($node as $key => $_node) {
                if (is_object($_node)) {
                    $node[$key]->setParentNode($this);
                }
                unset($_node);

            }

            $this->content = array_merge($node, $this->content);
        } else {
            if (is_object($node)) {
                $node->setParentNode($this);
            }
            $this->content = array_merge(array($node), $this->content);

            unset($node);
        }
        return $this;
    }

    /**
     * adds a class to this node
     *
     * @return null
     */
    public function addClass($class)
    {
        return $this->attr("class", $this->attr("class") . " " . $class);
    }

    /**
     * removes a class
     *
     * @name removeClass
     * @access public
     * @return null
     */
    public function removeClass($class)
    {
        $classes = $this->attr("class");
        $classes = str_replace($class, "", $classes);

        return $this->attr("class", $classes);
    }

    /**
     * checks if a class exists on this node
     *
     * @name hasClass
     * @access public
     * @return bool
     */
    public function hasClass($class)
    {
        $classes = $this->attr("class");
        $items = explode(" ", $classes);
        if (in_array($class, $items)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * parses CSS or an array of key-value pairs
     *
     * @name parseCSS
     * @access public
     * @param string|array - css
     */
    public function parseCSS($css)
    {
        if (is_array($css)) {
            $this->css = $css;
        } else {
            $items = explode(';', $css);
            $this->css = array();

            foreach ($items as $item) {
                if (empty($item)) {
                    continue;
                }

                if (preg_match('/^\s*([a-zA-Z0-9\-_]+)\s*:\s*(.*)$/', trim($item), $match)) {
                    $this->css[$match[1]] = trim($match[2]);
                }
            }
        }
    }

    /**
     * renders the attributes
     *
     * @name    renderAttributes
     * @access    protected
     * @return string
     */
    protected function renderAttributes()
    {
        $attr = " ";
        if ($this->css) {
            $style = "";
            foreach ($this->css as $key => $value) {
                $style .= "" . $key . ":" . $value . ";";
            }
            $this->attr["style"] = $style;
        }
        foreach ($this->attr as $name => $value) {
            if (RegexpUtil::isNumber($name)) {
                $attr .= $value . "=\"" . $value . "\" ";
            } else {
                $attr .= $name . "=\"" . $value . "\" ";
            }
        }

        return $attr;
    }

    /**
     * renders the object as html
     *
     * @param  string|null $whitespace to add whitespace to rendering.
     * @return string
     */
    public function render($whitespace = null)
    {
        // first render the content
        if ($whitespace !== null && !in_array($this->tag, self::$inline_tags)) {
            $content = $this->html(null, $whitespace . "         ");
        } else {
            $content = $this->html();
        }

        if (!in_array($this->tag, self::$non_closing_tags)) {
            if ($whitespace !== null && !in_array($this->tag, self::$inline_tags)) { // we don't want any \n for inline tags
                return "\n" . $whitespace . "<" . $this->tag . $this->renderAttributes() . ">\n" . $content . "\n" . $whitespace . "</" . $this->tag . ">";
            } else if ($whitespace !== null) { // but we want whitespace for them
                return "\n" . $whitespace . "<" . $this->tag . $this->renderAttributes() . ">" . $content . "</" . $this->tag . ">";
            } else {
                return "<" . $this->tag . $this->renderAttributes() . ">" . $content . "</" . $this->tag . ">";
            }
        } else {
            if ($whitespace) {
                return "\n" . $whitespace . "<" . $this->tag . $this->renderAttributes() . "/>\n";
            } else {
                return "<" . $this->tag . $this->renderAttributes() . "/>";
            }
        }
    }

    /**
     * gets the current tag-name
     *
     * @name getTag
     * @access public
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * sets the current tag
     *
     * @name setTag
     * @access public
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = trim(strtolower($tag));
    }

    /**
     * attributes with overloading
     */

    public function __get($name)
    {
        return isset($this->attr[$name]) ? $this->attr[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->attr[$name] = $value;

        return true;
    }

    public function __unset($name)
    {
        if (isset($this->attr[$name]))
            unset($this->attr[$name]);
    }

    public function __isset($name)
    {
        return isset($this->attr[$name]);
    }

    /**
     * to get this object as string, too
     *
     * @name __toString
     * @access public
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * to get this object as string for template
     *
     * @name forTemplate
     * @access public
     * @return string
     */
    public function forTemplate()
    {
        return $this->render();
    }
}
