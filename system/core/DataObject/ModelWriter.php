<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Writing Models to DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 */
class ModelWriter extends Object {

    const WRITE_TYPE_AUTOSAVE = 0;
    const WRITE_TYPE_SAVE = 1;
    const WRITE_TYPE_PUBLISH = 2;

    /**
     * DataObject to write.
     */
    protected $model;

    /**
     * type of write.
     */
    protected $writeType = self::WRITE_TYPE_PUBLISH;

    /**
     * set of data which can be written to DataBase.
     */
    private $data;

    /**
     * creates write.
     */
    public function __construct($model) {
        parent::__construct();

        $this->model = $model;
    }

    /**
     * writes generated data to DataBase.
     */
    public function write() {

    }
}