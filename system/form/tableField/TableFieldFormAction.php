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

    protected $result;
    protected $tableField;
    protected $actionName;
    protected $args;

    /**
     *
     * @param TableField $tableField
     * @param string $name
     * @param string $title
     * @param string $actionName
     * @param array $args
     * @param null|string $js
     */
    public function __construct($tableField = null, $name = null, $title = null, $actionName = null, $args = null, $js = null) {
        if(!is_object($tableField))
            return;

        $this->tableField = $tableField;
        $this->actionName = $actionName;
        $this->args = $args;

        $this->useHtml = true;

        parent::__construct($this->tableField->name . "_" . $name, $title);

        if(isset($js)) {
            $this->input->onclick = $js;
        }
        $this->setForm($tableField->Form());
    }

    /**
     * @return Closure
     */
    public function getSubmit()
    {
        $result = $this->result;
        return function() use($result) {
            return $result;
        };
    }

    public function __getSubmit()
    {
        return $this->getSubmit();
    }

    /**
     * returns false, because a tableField-action never triggers the form to submit
     * but we hook into it.
     *
     * @name canSubmit
     * @param array $data
     * @return bool
     */
    public function canSubmit($data) {
        $this->tableField->form()->activateRestore();
        $this->result = $this->tableField->_handleAction($this->actionName, $this->args, $data);
        return !!$this->result;
    }
}
