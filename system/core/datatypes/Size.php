<?php defined('IN_GOMA') OR die();
/**
 * a special tuple with naming of vars width and height.
 * first value is width and second height.
 *
 * @package	goma framework
 * @link 	http://goma-cms.org
 * @license LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
 * @author 	Goma-Team
 * @version 1.0
 */
class Size extends Tuple
{

    /**
     * constructor.
     * @param mixed $width
     * @param mixed $height
     */
    public function __construct($width, $height)
    {
        parent::__construct($width, $height);
    }

    /**
     * returns first value
     *
     * @return mixed
     */
    public function getWidth()
    {
        return $this->first;
    }

    /**
     * returns second value
     *
     * @return mixed
     */
    public function getHeight()
    {
        return $this->second;
    }

    /**
     * returns a new Tuple with second value and new first value.
     *
     * @param mixed $width new value
     * @return Size
     */
    public function updateWidth($width)
    {
        return new Size($width, $this->getHeight());
    }

    /**
     * returns a new Tuple with first value and new second value.
     *
     * @param mixed $height new value
     * @return Size
     */
    public function updateHeight($height)
    {
        return new Size($this->getWidth(), $height);
    }
}