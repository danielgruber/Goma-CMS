<?php defined('IN_GOMA') OR die();
/**
 * A tuple is a object for two values, that are combined to each other.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.0
 */

class Tuple {
    /**
     * first value
     */
    protected $first;

    /**
     * second value
     */
    protected $second;

    /**
     * constructor.
     * @param mixed $first
     * @param mixed $second
     */
    public function __construct($first, $second) {
        $this->first = $first;
        $this->second = $second;
    }

    /**
     * returns first value
     *
     * @return mixed
     */
    public function getFirst() {
        return $this->first;
    }

    /**
     * returns second value
     *
     * @return mixed
     */
    public function getSecond() {
        return $this->second;
    }

    /**
     * returns a new Tuple with second value and new first value.
     *
     * @param mixed $first new value
     * @return Tuple
     */
    public function updateFirst($first) {
        return new Tuple($first, $this->second);
    }

    /**
     * returns a new Tuple with first value and new second value.
     *
     * @param mixed $second new value
     * @return Tuple
     */
    public function updateSecond($second) {
        return new Tuple($this->first, $second);
    }

    /**
     * copies the tuple.
     */
    public function copy() {
        $c = get_class($this);
        return new $c($this->first, $this->second);
    }

    public function isEqual(Tuple $otherTuple) {
        return $this->first == $otherTuple->first && $this->second == $otherTuple->second;
    }
}