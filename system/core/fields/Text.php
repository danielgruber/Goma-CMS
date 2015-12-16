<?php defined("IN_GOMA") OR die();

/**
 * Every value of an field can used as object if you call doObject($offset) for text-fields
 * This Object has some very cool methods to convert the field
 */
class TextSQLField extends Varchar
{
    /**
     * converts the text to one line
     *@name oneline
     *@access public
     */
    public function oneline()
    {
        return str_replace(array("\n", "\r"), '', $this->value);
    }
    /**
     * niceHTML
     *@name niceHTML
     *@access public
     *@param string - left
     */
    public function niceHTML($left = "	")
    {
        $value = $this->value;
        $value = str_replace("\n", "\n" . $left, $value);

        return "\n" . $value;
    }
    /**
     * generatesa a textarea
     *@name formfield
     *@access public
     *@param string - title
     */
    public function formfield($title = null)
    {
        return new TextArea($this->name, $title);
    }
}