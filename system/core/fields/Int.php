<?php defined("IN_GOMA") OR die();

/**
 * Every value of an field can used as object if you call doObject($offset) for Int-fields
 * This Object has some very cool methods to convert the field
 */
class intSQLField extends Varchar
{
    /**
     * generatesa a numeric field
     *@name formfield
     *@access public
     *@param string - title
     */
    public function formfield($title = null)
    {
        return new NumberField($this->name, $title);
    }
}
