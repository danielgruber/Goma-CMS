<?php defined("IN_GOMA") OR die();

/**
 * DataObject to store ratings.
 *
 * @property string rators
 * @property int rates
 * @property int rating
 * @property string name
 * @property string stars
 *
 * @author	Goma-Team
 * @license	GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package	Goma\Framework
 * @version 1.1
 */
class Rating extends DataObject
{
    /**
     * db-fields
     *
     * @name db
     */
    static $db = array(
        "name"   => "varchar(200)",
        "rates"  => "int(10)",
        "rating" => "int(11)",
        "rators" => "text"
    );

    /**
     * index
     */
    static $index = array(
        "name" => "INDEX"
    );

    /**
     * default.
     */
    static $default = array(
        "rates"  => 0,
        "rating" => 0,
        "rators" => ""
    );

    static $search_fields = false;

    /**
     * checks if user already voted
     *
     * @param string $voter string to search for
     * @return bool
     */
    public function hasAlreadyVoted($voter)
    {
        $voters = unserialize($this->rators);
        if (is_array($voters))
            return in_array($voter, $voters);

        return false;
    }


    public function providePermissions()
    {
        return array(
            "RATING_ALL"    => array(
                "default"    => 7,
                "hide"       => true,
                "implements" => array(
                    "RATING_DELETE"
                )
            ),
            "RATING_DELETE" => array(
                "title"   => '{$_lang_exp_gomacms_rating.perms_delete}',
                "default" => 7
            )
        );
    }

    /**
     * calculates the stars on special percision
     * just 5 or 0 behind the commar are allowed
     *
     * @name GetCalculated
     * @access public
     * @return float|int|string
     */
    public function GetCalculated()
    {
        if ($this->rates == 0) {
            return 0;
        }

        return round($this->rating / $this->rates, 1);
    }

    /**
     * gets the rendered stars
     *
     * @name getStars
     * @access public
     * @return string
     */
    public function getStars()
    {
        $starcount = $this->getCalculated();

        $count = round($starcount);
        $half = ($starcount != $count);

        $output = "<div class=\"stars\" title=\"" . $this->name . "\">";
        $canWrite = !$this->hasAlreadyVoted($_SERVER["REMOTE_ADDR"]);

        for ($i = 0; $i < 5; $i++) {
            $star = $i + 1;
            if ($canWrite) {
                if ($i < $count)
                    $output .= '<a id="star_' . $this->name . '_' . $star . '" class="star_yellow star" href="rate/' . $this->name . '/' . $star . '" rel="ajaxfy" title="' . $star . '""><img src="images/star_yellow.png" alt="' . $star . '" /></a>';
                else if ($i == $count && $half)
                    $output .= '<a id="star_' . $this->name . '_' . $star . '" class="star_half star" href="rate/' . $this->name . '/' . $star . '" rel="ajaxfy" title="' . $star . '""><img src="images/star_half.png" alt="' . $star . '" /></a>';
                else
                    $output .= '<a id="star_' . $this->name . '_' . $star . '" class="star_grey star" href="rate/' . $this->name . '/' . $star . '" rel="ajaxfy" title="' . $star . '""><img src="images/star_grey.png" alt="' . $star . '" /></a>';
            } else {
                if ($i < $count)
                    $output .= '<img src="images/star_yellow.png" alt="' . $star . '" />';
                else if ($i == $count && $half)
                    $output .= '<img src="images/star_half.png" alt="' . $star . '" />';
                else
                    $output .= '<img src="images/star_grey.png" alt="' . $star . '" />';

            }
        }
        $output .= "</div>";

        if ($this->rates == 1) {
            $output .= "1 " . lang("exp_gomacms_rating.vote", "vote") . "";
        } else if ($this->rates == 0) {
            $output .= "0 " . lang("exp_gomacms_rating.votes", "votes");
        } else {
            $output .= "" . $this->rates . " " . lang("exp_gomacms_rating.votes", "votes");
        }

        return $output;
    }

    /**
     * draws the rating
     *
     * @param $name
     * @return string
     */
    public static function draw($name)
    {
        $name = strtolower($name);
        $data = self::getRatingByName($name);

        gloader::load("rating");

        return '<div id="rating_' . $name . '">' . $data->stars . '</div><div class="message">' . self::getRatingMessage($name) . '</div>';

    }

    /**
     * gets rating-message.
     */
    protected function getRatingMessage($name) {
        if($message = GlobalSessionManager::globalSession()->get("rating_message." . $name)) {
            GlobalSessionManager::globalSession()->remove("rating_message." . $name);
            return $message;
        }

        return "";
    }


    /**
     * forces existence of rating.
     *
     * @param string $name
     * @return Rating
     */
    public static function getRatingByName($name) {
        if($model = DataObject::get_one("rating", array("name" => strtolower($name)))) {
            return $model;
        }

        return new Rating(array(
            "name" => strtolower($name)
        ));

    }
}

gloader::addLoadAble("rating", ExpansionManager::getExpansionFolder("gomacms_rating") . "classes/rating.js");
