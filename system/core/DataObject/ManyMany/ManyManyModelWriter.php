<?php defined("IN_GOMA") OR die();

/**
 * Basic Class for Writing Many-Many-Relationships of Models to DataBase.
 *
 * @package     Goma\Model
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version    1.0
 *
 * @method ModelWriter getOwner()
 */
class ManyManyModelWriter extends Extension {
    /**
     * many-many-objects.
     */
    protected $many_many_objects;

    /**
     * many-many-relationships.
     */
    protected $many_many_relationships;

    /**
     * called when data was written so we have new versionid, but transaction is still on stage.
     *
     * @param array $manipulation
     */
    public function onBeforeWriteData(&$manipulation) {
        $data = $this->getOwner()->getData();

        $many_many = $this->getOwner()->getModel()->ManyManyRelationships();

        // many-many
        if ($many_many) {
            /** @var ModelManyManyRelationshipInfo $relationShip */
            foreach($many_many as $name => $relationShip) {

                /** @var ModelManyManyRelationShipInfo $relationShip */
                $relationShip = $this->getOwner()->getModel()->getManyManyInfo($name);

                /** @var ManyMany_DataObjectSet $set */
                if(isset($data[$name]) && is_a($data[$name], "ManyMany_DataObjectSet")) {
                    $set = $data[$name];
                    $set->setRelationENV($relationShip, $this->getOwner()->getModel());
                    $set->commitStaging(false, true, $this->getOwner()->getWriteType(), $this->getOwner()->getRepository(), $this->getOwner()->getOldId());
                } else {
                    $set = $this->getOwner()->getModel()->getManyMany($name);
                    $set->setRelationENV($relationShip, $this->getOwner()->getModel());
                    $set->commitStaging(false, true, $this->getOwner()->getWriteType(), $this->getOwner()->getRepository(), $this->getOwner()->getOldId());
                }
            }
        }

        // add some manipulation to existing many-many-connection, which are not reflected with belongs_many_many
        if ($this->getOwner()->getOldId() != 0) {
            $this->moveManyManyExtra($this->getOwner()->getOldId());
        }

        $this->getOwner()->setData($data);
    }

    /**
     * moves extra many-many-relations.
     *
     * @param int $oldId
     * @return array
     * @throws SQLException
     */
    protected function moveManyManyExtra($oldId) {
        $dataClasses = array_merge(
            array($this->getOwner()->getModel()->BaseClass()),
            ClassInfo::DataClasses($this->getOwner()->getModel()->classname)
        );

        foreach($dataClasses as $dataClass) {
            if (isset(ClassInfo::$class_info[$dataClass]["many_many_relations_extra"])) {
                foreach(ClassInfo::$class_info[$dataClass]["many_many_relations_extra"] as $info) {
                    $this->moveManyManyExtraForRelationShip($oldId, $info);
                }
            }
        }
    }

    /**
     * moves many-many-extra for a specific class.
     *
     * @param int $oldId
     * @param array $info
     * @return array
     */
    protected function moveManyManyExtraForRelationShip($oldId, $info) {
        /** @var ModelManyManyRelationShipInfo $relationShip */
        $relationShip = $this->getOwner()->getModel()->getManyManyInfo($info[1], $info[0])->getInverted();

        $set = new ManyMany_DataObjectSet($relationShip->getTargetClass());
        $set->setVersion($this->getOwner()->getModel()->queryVersion);
        $set->setRelationENV($relationShip, $this->getOwner()->getModel());

        $set->commitStaging(false, true, $this->getOwner()->getWriteType(), $this->getOwner()->getRepository(), $oldId);
    }

    /**
     * delete old versions.
     * @param array $manipulation
     * @param int $oldId
     */
    public function deleteOldVersions(&$manipulation, $oldId) {
        /** @var ModelWriter $owner */
        $owner = $this->getOwner();

        // clean-up-many-many
        /** @var ModelManyManyRelationShipInfo $relationship */
        foreach($owner->getModel()->ManyManyRelationships() as $relationship) {
            $manipulation[$relationship->getTableName()] = array(
                "table_name"=> $relationship->getTableName(),
                "command"	=> "delete",
                "where"		=> array(
                    $relationship->getOwnerField() => $oldId
                )
            );
        }
    }
}

gObject::extend("ModelWriter", "ManyManyModelWriter");
