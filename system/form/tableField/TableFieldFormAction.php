<?php defined('IN_GOMA') OR die();

/**
 * Extends basic button to be usable in TableField.
 *
 * Inspiration by Silverstripe 3.0 GridField
 * http://silverstripe.org
 *
 * @package     Goma\Form\TableField
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.1.1
 */
class TableField_FormAction extends FormAction {
    /**
     *
     * @param TableField $tableField
     * @param string $name
     * @param string $title
     * @param string $actionName
     * @param array $args
     */
    public function __construct($tableField = null, $name = null, $title = null, $actionName = null, $args = null) {
        if(!is_object($tableField))
            return ;
        $this->tableField = $tableField;
        $this->actionName = $actionName;
        $this->args = $args;
        parent::__construct($this->tableField->name . "_" . $name, $title);

        $this->setForm($tableField->Form());
    }

    /**
     * returns false, because a tableField-action never triggers the form to submit
     * but we hook into
     *
     * @name canSubmit
     * @param array $data
     * @return bool
     */
    public function canSubmit($data) {
        $this->tableField->form()->activateRestore();
        $this->tableField->_handleAction($this->actionName, $this->args, $data);
        return false;
    }
}
