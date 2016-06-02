<?php
defined("IN_GOMA") OR die();

/**
 * Base-Class for FormFields and Form, which handles logic of result and model.
 *
 * @package vorort.news
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 *
 * @version 1.0
 */
class AbstractFormComponentWithChildren extends AbstractFormComponent {
    /**
     * @return array|null|string|ViewAccessableData
     */
    public function getModel()
    {
        return isset($this->model) ? (isset($this->parent) ? $this->parent->getModel() : null) : null;
    }
}
