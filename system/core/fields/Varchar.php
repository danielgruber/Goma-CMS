<?php defined("IN_GOMA") OR die();
/**
 * Every value of an field can used as object if you call doObject($offset) for varchar-fields
 * This Object has some very cool methods to convert the field
 */
class Varchar extends DBField
{
    /**
     * strips all tags of the value
     *@name striptags
     *@access public
     */
    public function strtiptags()
    {
        return striptags($this->value);
    }

    /**
     * makes a substring of this value
     *@name substr
     *@access public
     */
    public function substr($start, $length = null)
    {
        if($length === null)
        {
            return substr($this->value, $start);
        } else
        {
            return substr($this->value, $start, $length);
        }
    }
    /**
     * this returns the length of the string
     *@name length
     *@access public
     */
    public function length()
    {
        return strlen($this->value);
    }

    /**
     * generates a special dynamic form-field
     *@name formfield
     *@access public
     *@param string - title
     */
    public function formfield($title = null)
    {

        if(strpos($this->value, "\n"))
        {
            return new TextArea($this->name, $title);
        } else
        {
            return parent::formfield($title);
        }
    }

    /**
     * renders text as BBcode
     *@name bbcode
     *@access public
     */
    public function bbcode()
    {
        $text = new Text($this->value);
        return $text->bbcode();
    }

    /**
     * converts this with date
     * @name date
     * @access public
     * @return string
     */
    public function date($format = DATE_FORMAT)
    {
        return goma_date($format, $this->value);
    }

    /**
     * for template
     *
     * @name forTemplate
     * @return string
     */
    public function forTemplate() {
        return $this->text();
    }
}