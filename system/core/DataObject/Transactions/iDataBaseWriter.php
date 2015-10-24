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
     * writes data of Writer to Database.
     */
    public function write();

    /**
     * publish.
     */
    public function publish();

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