<?php defined("IN_GOMA") OR die();

/**
 * Form-Field to select a specific time.
 *
 * @package        Goma\Core\Model
 * @version        1.1.1
 */
class TimeField extends FormField
{
    /**
     * regional.
     *
     * @var string
     */
    protected $regional;

    /**
     * @name __construct
     * @param string -name
     * @param string - title
     * @param string - value
     * @param array - between: key 0 for start and key 1 for end
     * @param object - form
     */
    public function __construct($name = null, $title = null, $value = null, $between = null, $form = null)
    {
        $this->between = $between;
        parent::__construct($name, $title, $value, $form);
    }

    /**
     * creates the field
     *
     * @return HTMLNode
     */
    public function createNode()
    {
        $node = parent::createNode();
        $node->type = "text";
        $node->addClass("timepicker");
        return $node;
    }

    /**
     * validate
     *
     * @name validate
     * @return bool|mixed|string
     */
    public function validate($value)
    {
        if (($timestamp = strtotime($value)) === false) {
            return lang("no_valid_time", "No valid timestamp!");
        } else {
            if ($this->between && is_array($this->between)) {
                $between = array_values($this->between);

                if (!preg_match("/^[0-9]+$/", trim($between[0]))) {
                    $start = strtotime($between[0]);
                } else {
                    $start = $between[0];
                }

                if (!preg_match("/^[0-9]+$/", trim($between[1]))) {
                    $end = strtotime($between[1]);
                } else {
                    $end = $between[1];
                }

                if ($start < $timestamp && $timestamp < $end) {
                    return true;
                } else {
                    $err = lang("time_not_in_range", "The given time is not between the range \$start and \$end.");
                    $err = str_replace('$start', date(DATE_FORMAT_TIME, $start), $err);
                    $err = str_replace('$end', date(DATE_FORMAT_TIME, $end), $err);
                    return $err;
                }
            }
            $this->value = date("H:i:s", $timestamp);
            return true;
        }
    }

    /**
     * @param FormFieldRenderData $info
     * @param bool $notifyField
     */
    public function addRenderData($info, $notifyField = true)
    {
        $info->addJSFile("system/libs/thirdparty/ui-timepicker/jquery.ui.timepicker.js");
        $info->addCSSFile("system/libs/thirdparty/ui-timepicker/jquery.ui.timepicker.css");

        $this->regional = "";
        foreach (i18n::getLangCodes(Core::$lang) as $code) {
            if (file_exists("system/libs/thirdparty/ui-timepicker/i18n/jquery.ui.timepicker-" . $code . ".js")) {
                $info->addJSFile("system/libs/thirdparty/ui-timepicker/i18n/jquery.ui.timepicker-" . $code . ".js");
                $this->regional = $code;
                break;
            }
        }

        parent::addRenderData($info, $notifyField);
    }

    /**
     * render JavaScript
     */
    public function JS()
    {
        return '$(function(){$("#' . $this->ID() . '").timepicker({regional: ' . var_export($this->regional, true) . '});});';
    }
}
