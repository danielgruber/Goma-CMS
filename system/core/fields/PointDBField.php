<?php defined("IN_GOMA") OR die();

class PointSQLField extends DBField {

    /**
     * for db.
     */
    public function forDBQuery() {
        return "GeomFromText('POINT(".$this->value.")')";
    }
}