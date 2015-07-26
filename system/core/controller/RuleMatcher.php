<?php defined("IN_GOMA") OR die();

/**
 * Matches rules.
 *
 * @package		Goma\System\Core
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version		1.0
 */
class RuleMatcher {
    /**
     * rules to match.
     */
    protected $rules;

    /**
     * request.
     *
     * @var Request
     */
    protected $request;

    /**
     * current position.
     */
    protected $position = 0;

    /**
     * position in multi-array.
     */
    protected $positionInMulti = 0;

    /**
     * current request object.
     *
     * @var Request
     */
    protected $currentRequest;

    /**
     * generates rule-matcher.
     */
    public static function initWithRulesAndRequest($rules, $request) {
        return new RuleMatcher($rules, $request);
    }

    protected function __construct($rules, $request) {
        $this->setRequest($request);
        $this->setRules($rules);
    }

    /**
     * matches next.
     */
    public function matchNext() {
        if(count($this->rules) == 0) {
            return null;
        }

        if($this->isMultiArray()) {
            $priorities = array_keys($this->rules);

            for($i = $this->positionInMulti; $i < count($priorities); $i++, $this->position = 0) {
                $controller = $this->matchNextInArray($this->rules[$priorities[$i]]);

                if($controller != null) {
                    $this->positionInMulti = $i;
                    return $controller;
                }
            }

            $this->positionInMulti = $i;
            return null;
        } else {
            return $this->matchNextInArray($this->rules);
        }
    }

    /**
     * iterates through rules and matches.
     *
     * @param $rules
     * @return string|null
     */
    protected function matchNextInArray($rules)
    {
        $controllers = array_values($rules);
        $ruleKeys = array_keys($rules);

        for($i = $this->position; $i < count($ruleKeys); $i++) {
            $request = clone $this->request;
            if($args = $request->match($ruleKeys[$i], true)) {

                $this->currentRequest = $request;
                $controller = $controllers[$i];
                if($request->getParam("controller")) {
                    $controller = $request->getParam("controller");
                }

                $this->position = $i + 1;
                return $controller;
            }
        }

        $this->position = $i;
        return null;
    }
    /**
     * @return Request
     */
    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }


    /**
     * returns if it is multidimensional.
     */
    protected function isMultiArray() {
        $arr = array_values($this->rules);

        return is_array($arr[0]);
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param mixed $rules
     */
    public function setRules($rules)
    {
        if(!is_array($rules)) {
            throw new InvalidArgumentException("Rules must be an array of at least one element.");
        }

        $this->rules = $rules;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request)
    {
        if(!is_a($request, "Request")) {
            throw new InvalidArgumentException("Request must be a request.");
        }

        $this->request = $request;
    }


}