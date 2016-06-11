<?php defined("IN_GOMA") OR die();

/**
 * generates the Editor for HTML-Code.
 *
 * @package        Goma\libs\WYSIWYG
 *
 * @author        Goma-Team
 * @license        GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @version        1.3.2
 */
class HTMLEditor extends Textarea {

    protected function getParams() {
        return array("width" => $this->width, "baseUri" => BASE_URI, "lang" => Core::$lang, "css" => $this->buildEditorCSS());
    }

    /**
     * generates the field
     * @param FormFieldRenderData $info
     * @return HTMLNode
     */
    public function field($info)
    {
        $this->callExtending("beforeField");

        $this->setValue();

        $this->container->append(new HTMLNode("label", array(
            'for' => $this->ID()
        ), $this->title));

        $this->container->append(array(
            new HTMLNode("a", array(
                'href'    => 'javascript:;',
                'onclick' => 'toggleEditor_' . $this->name . '()',
                "style"   => "display: none;",
                "class"   => "editor_toggle",
                "id"      => "editor_toggle_" . $this->name
            ), lang("EDITOR_TOGGLE", "Toggle Editor"))
        ));

        $this->container->append(GomaEditor::get("html")->generateEditor($this->name, "html", $this->getModel(), $this->getParams()));

        $this->callExtending("afterRender");

        return $this->container;
    }

    public function addRenderData($info, $notifyField = true)
    {
        parent::addRenderData($info, $notifyField);

        GomaEditor::get("html")->addEditorInfo($info);
    }

    /**
     * builds editor.css
     */
    public function buildEditorCSS()
    {
        $cache = CACHE_DIRECTORY . "/htmleditor_compare_" . Core::GetTheme() . ".css";
        if (file_exists("tpl/" . Core::getTheme() . "/editor.css")) {
            $css = self::importCSS("system/templates/css/default.css") . "\n" . self::importCSS("tpl/" . Core::getTheme() . "/editor.css");
            $css .= "body {padding: 0.5em;}";

            if (file_exists(ROOT . tpl::$tplpath . Core::getTheme() . "/default.less")) {
                $css = file_get_contents(ROOT . tpl::$tplpath . Core::getTheme() . "/default.less") . $css;
            } else
                if (file_exists(ROOT . APPLICATION . "/templates/default.less")) {
                    $css = file_get_contents(ROOT . APPLICATION . "/templates/default.less") . $css;
                } else {
                    $css = file_get_contents(FRAMEWORK_ROOT . "/templates/css/default.less") . $css;
                }

            try {
                $less = new lessc;
                $css = $less->compile($css);
            } catch (Exception $e) {
                log_exception($e);
            }

            // parse CSS
            //$css = preg_replace_callback('/([\.a-zA-Z0-9_\-,#\>\s\:\[\]\=]+)\s*{/Usi', array("historyController", "interpretCSS"), $css);
            FileSystem::write($cache, $css);

            return BASE_URI . $cache;
        } else {
            return false;
        }
    }

    /**
     * gets a consolidated CSS-File, where imports are merged with original file
     *
     * @return mixed|string
     */
    public static function importCSS($file)
    {
        if (file_exists($file)) {
            $css = file_get_contents($file);
            // import imports
            preg_match_all('/\@import\s*url\(("|\')([^"\']+)("|\')\)\;/Usi', $css, $m);
            foreach ($m[2] as $key => $_file) {
                $css = str_replace($m[0][$key], self::importCSS(dirname($file) . "/" . $_file), $css);
            }

            return $css;
        }

        return "";
    }

    public function js()
    {
        return parent::js() . GomaEditor::get("html")->addEditorJS($this->name, "html", $this->getModel(), $this->getParams());
    }
}
