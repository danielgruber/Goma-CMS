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
interface iDataBaseWriter {
    /**
     * sets Writer-Object.
     *
     * @param ModelWriter $writer
     */
    public function setWriter($writer);

    /**
     * writes data to Database.
     *
     * @param array $data field-value pairs for fields
     */
    public function write($data);

    /**
     * validates.
     */
    public function validate();

    /**
     * tries to find recordid in versions of state-table.
     *
     * @param int $recordid
     * @return Tuple<publishedid, stateid>
     */
    public function findStateRow($recordid);
}