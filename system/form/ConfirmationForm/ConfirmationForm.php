<?php
defined("IN_GOMA") OR die();

/**
 * Confirmation-Form.
 *
 * @package     Goma\Form
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     1.0
 */
class ConfirmationForm extends Form {
    /**
     * @param RequestHandler $controller
     * @param Request $request
     * @return string|void
     */
    public function initWithRequest($controller, $request)
    {
        parent::initWithRequest($controller, $request);

        foreach($this->getRequest()->post_params as $key => $value) {
            if(!$this->hasField($key)) {
                $this->add(new HiddenField($key, $value));
            }
        }
    }
}
