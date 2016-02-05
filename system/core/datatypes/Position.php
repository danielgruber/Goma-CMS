<?php defined('IN_GOMA') OR die();
/**
 * a special tuple with naming of vars x and y.
 * first value is width and second height.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.0
 */
class Position extends Tuple
{

    /**
     * constructor.
     * @param mixed $x
     * @param mixed $y
     */
    public function __construct($x, $y)
    {
        parent::__construct($x, $y);
    }

    /**
     * returns first value
     *
     * @return mixed
     */
    public function getX()
    {
        return $this->first;
    }

    /**
     * returns second value
     *
     * @return mixed
     */
    public function getY()
    {
        return $this->second;
    }

    /**
     * returns a new Tuple with second value and new first value.
     *
     * @param int $x new value
     * @return Position
     */
    public function updateX($x)
    {
        return new Position($x, $this->getY());
    }

    /**
     * returns a new Tuple with first value and new second value.
     *
     * @param int $y new value
     * @return Position
     */
    public function updateY($y)
    {
        return new Position($this->getX(), $y);
    }
}
