<?php defined("IN_GOMA") OR die();

/**
 * provides APIs for Stats.
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @package		Goma\Framework
 * @version		1.1.1
 */
class StatController extends Controller {
    /**
     * url-handlers
     *
     *@name url_handlers
     */
    public $url_handlers = array(
        "lastWeek/\$page"		=> "lastWeek",
        "lastMonth/\$page"		=> "lastMonth",
        "lastYear/\$page"		=> "lastYear",
        "yesterday/\$page"		=> "yesterday",
        "\$start!/\$end/\$max"	=> "handleStats"
    );

    /**
     * allow actions
     */
    public $allowed_actions = array(
        "handleStats"	=> "ADMIN",
        "lastMonth"		=> "ADMIN",
        "lastWeek"		=> "ADMIN",
        "lastYear"		=> "ADMIN",
        "yesterday"		=> "ADMIN"
    );

    /**
     * @param string|gObject|array $content
     */
    public function __output($content) {
        HTTPResponse::setHeader("content-type", "text/x-json");
        HTTPResponse::sendHeader();
        echo json_encode($content);
        exit;
    }

    /**
     * handles stats.
     */
    public function handleStats() {
        $start = $this->getParam("start");
        $end = $this->getParam("end") ? $this->getParam("end") : $start + (60 * 60 * 24 * 7);
        $max = $this->getParam("max") ? $this->getParam("max") : 32;

        $data = LiveCounter::statisticsData($start, $end, $max);

        return $data;
    }

    /**
     * handles stats for last week
     */
    public function lastMonth() {
        $page = $this->getParam("page") ? $this->getParam("page") : 1;

        $last30Days = mktime(0, 0, 0, date("n"), date("d"), date("Y")) - 1 - (60 * 60 * 24 * 30) * $page;
        // get last month
        $month = date("n", $last30Days);
        $year = date("Y", $last30Days);
        $day = date("d", $last30Days);

        $start = mktime(0, 0, 0, $month, $day, $year); // get 1st of last month 00:00:00

        $endTime = $start + (60 * 60 * 24 * 30);
        $end = mktime(23, 59, 59, date("m", $endTime), date("d", $endTime), date("Y", $endTime));
        $max = 30;

        $data = LiveCounter::statisticsData($start, $end, $max);

        $data["timeFormat"] = "%d.%m";
        $data["timePositionMiddle"] = false;

        return $data;
    }

    /**
     * last week-data.
     */
    public function lastWeek() {
        $page = $this->getParam("page") ? $this->getParam("page") : 1;

        $last7Days = mktime(0, 0, 0, date("n"), date("d"), date("Y")) - 1 - (60 * 60 * 24 * 7) * $page;
        // get last month
        $month = date("n", $last7Days);
        $year = date("Y", $last7Days);
        $day = date("d", $last7Days);

        $start = mktime(0, 0, 0, $month, $day, $year); // get 1st of last month 00:00:00

        $endTime = $start + (60 * 60 * 24 * 7);
        $end = mktime(23, 59, 59, date("m", $endTime), date("d", $endTime), date("Y", $endTime));
        $max = 7;

        $data = LiveCounter::statisticsData($start, $end, $max);

        $data["timeFormat"] = "%d.%m";
        $data["timePositionMiddle"] = false;
        $data["minTickSize"] = array(24, "hour");

        return $data;
    }

    /**
     * last year-data.
     */
    public function lastYear() {
        $page = $this->getParam("page") ? $this->getParam("page") : 1;

        $lastYear = NOW - (60 * 60 * 24 * 365) * $page;
        // get last month
        $month = date("n", $lastYear);
        $year = date("Y", $lastYear);
        $day = date("d", $lastYear);

        $start = mktime(0, 0, 0, $month, $day, $year); // get 1st of last month 00:00:00

        $endTime = $start + (60 * 60 * 24 * 365);
        $end = mktime(23, 59, 59, date("m", $endTime), date("d", $endTime), date("Y", $endTime));
        $max = 36;

        $data = LiveCounter::statisticsData($start, $end, $max);

        $data["timeFormat"] = "%d.%m";
        $data["timePositionMiddle"] = false;

        return $data;
    }

    /**
     * last day-data.
     */
    public function yesterday() {
        $page = $this->getParam("page") ? $this->getParam("page") : 1;
        $showcount = 1;

        $yesterday = NOW - (60 * 60 * 24) * $page;
        // get last month
        $month = date("n", $yesterday);
        $year = date("Y", $yesterday);
        $day = date("d", $yesterday);

        $start = mktime(0, 0, 0, $month, $day, $year); // get 1st of last month 00:00:00

        $endTime = $start + (60 * 60 * 24);
        $end = mktime(date("H", $endTime), 0, 0, date("m", $endTime), date("d", $endTime), date("Y", $endTime));
        $max = 24;

        $data = LiveCounter::statisticsData($start, $end, $max);

        $data["timeFormat"] = "%H:%M";

        $data["title"] = goma_date(DATE_FORMAT_DATE . " (l)", $start);

        return $data;
    }
}
