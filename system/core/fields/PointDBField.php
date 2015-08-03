<?php defined("IN_GOMA") OR die();

class PointSQLField extends DBField {

    /**
     * for db.
     */
    public function forDB() {
        return "GeomFromText('POINT(".$this->value.")')";
    }
}