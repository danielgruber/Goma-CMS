<?php defined("IN_GOMA") OR die();

/**
 * Class for parsing HTML for inline Scripts and styles and updates all links to correct values.
 *
 * @package     Goma\HTML-Processing
 *
 * @license     GNU Lesser General Public License, version 3; see "LICENSE.txt"
 * @author      Goma-Team
 *
 * @version     2.4
 */
class HTMLParser extends Object
{

    /**
     * allowed prefixes for links in goma that never are checked for BASE_SCRIPT.
     */
    static $allowedPrefixes = array(
        "ftp:", "http:", "https:", "javascript:", "mailto:"
    );


    /**
     * parses HTML-code for links and scripts to get all inline scripts
     * into files and all links working.
     *
     * @name    parseHTML
     * @return    string
     */
    public static function parseHTML($html, $parseLinksAndScripts = true)
    {
        if (PROFILE) Profiler::mark("HTMLParser::parseHTML");

        if ($parseLinksAndScripts) {
            $html = self::findScripts($html);
            $html = self::process_links($html);
        }

        if (!Core::is_ajax()) {

            // we have everything a normal HTML Page should have.
            if (strpos($html, "</title>") && strpos($html, "</body>")) {

                // replace css resources
                $view = new ViewAccessableData();
                if (strpos($html, "<base") === false) {
                    $view->base_uri = BASE_URI;
                }
                $view->resources = resources::get(true, false);
                $html = str_replace('</title>', $view->renderWith("framework/resources-header.html"), $html);

                // replace js resources
                $html = str_replace('</body>', "\n" . resources::get(false, true) . "\n	</body>", $html);
            } else {
                if (strpos($html, "<base")) {
                    $html = '<meta charset="utf-8" />' . resources::get() . $html;
                } else {
                    $html = '<!DOCTYPE html><html><head><meta charset="utf-8" /><title></title><base href="' . BASE_URI . '" />' . "\n" . resources::get(true, false) . "\n</head><body>" . $html . "\n" . resources::get(false, true) . "</body></html>";
                }
            }
        }

        if (PROFILE) Profiler::unmark("HTMLParser::parseHTML");

        return $html;
    }

    /**
     * finds all javascripts and packs them into its own files.
     *
     * @name findScripts
     * @return mixed
     */
    public static function findScripts($html)
    {
        preg_match_all('/\<\!\-\-(.*)\-\-\>/Usi', $html, $comments);
        foreach ($comments[1] as $k => $v) {
            $html = str_replace($comments[0][$k], "<!--comment_" . $k . "-->", $html);
        }

        // find inline scripts.
        preg_match_all('/\<script[^\>]*type\=\"text\/javascript\"[^\>]*\>(.*)\<\/script\s*\>/Usi', $html, $no_tags);
        foreach ($no_tags[1] as $key => $js) {
            if (!empty($js)) {
                $html = str_replace($no_tags[0][$key], self::js($js), $html);
            }
        }

        // find scripts with src.
        preg_match_all('/\<script[^\>]*src="(.+)"[^>]*\>(.*)\<\/script\s*\>/Usi', $html, $no_tags);
        foreach ($no_tags[1] as $key => $js) {
            if (!empty($js) && file_exists(ROOT . $js)) {
                $html = str_replace($no_tags[0][$key], self::jsFile(ROOT . $js), $html);
            }
        }

        foreach ($comments[1] as $k => $v) {
            $html = str_replace("<!--comment_" . $k . "-->", $comments[0][$k], $html);
        }

        return $html;
    }

    /**
     * processes links to work also on servers where Mod Rewrite is not enabled.
     * it adds the BASE_SCRIPT before all links. BASE_SCRIPT normally contains index.php/.
     *
     * @name    process_links
     * @access public
     * @param    string - html
     * @return mixed
     */
    public static function process_links($html, $base = BASE_SCRIPT, $root = ROOT_PATH, $prependBase = "")
    {
        if (PROFILE) Profiler::mark("HTMLParser::process_links");
        preg_match_all('/<a([^>]+)href="([^">]+)"([^>]*)>/Usi', $html, $links);
        foreach ($links[2] as $key => $href) {
            $newlink = self::parseLink($href, '<a' . $links[1][$key] . 'href=', $links[3][$key] . '>', $base, $root, $prependBase);

            if ($newlink) {
                $html = str_replace($links[0][$key], $newlink, $html);
            }
        }

        preg_match_all('/<iframe([^>]+)src="([^">]+)"([^>]*)>/Usi', $html, $frames);
        foreach ($frames[2] as $key => $href) {
            $newlink = self::parseLink($href, '<iframe' . $frames[1][$key] . 'src=', $frames[3][$key] . '>', $base, $root, $prependBase);

            if ($newlink) {
                $html = str_replace($frames[0][$key], $newlink, $html);
            }
        }

        preg_match_all('/<img([^>]+)src="([^">]+)"([^>]*)>/Usi', $html, $images);
        foreach ($images[2] as $key => $href) {
            if (strtolower(substr($href, 0, 17)) == "images/resampled/") {
                $href = BASE_SCRIPT . $href;
            }

            $href = $prependBase . $href;
            $newframes = '<img' . $images[1][$key] . 'src="' . $href . '"' . $images[3][$key] . ' />';
            $html = str_replace($images[0][$key], $newframes, $html);
        }

        if (PROFILE) Profiler::unmark("HTMLParser::process_links");

        return $html;
    }

    /**
     * parses an url and generates a new string with the link, some code before, then some maybe
     * generated attributes and some code after.
     *
     * @param    href link
     *
     * @param    HTML before link
     * @param    HTML after link
     * @return bool|string
     */
    public static function parseLink($href, $beforeHref, $afterHref, $base = BASE_SCRIPT, $root = ROOT_PATH, $prependBase = "")
    {
        $attrs = "";
        $lowerhref = strtolower($href);

        // check for prefixes.
        foreach (self::$allowedPrefixes as $prefix) {
            if (substr($lowerhref, 0, strlen($prefix)) == $prefix) {
                return false;
            }
        }

        // check anchor
        if (substr($lowerhref, 0, 1) == "#") {
            $attrs = 'data-anchor="' . substr($href, 1) . '"';
            $href = URL . URLEND . $href;
        }

        // check ROOT_PATH
        if (substr($lowerhref, 0, strlen($root)) == strtolower($root)) {
            $href = substr($href, strlen($root));
        }

        // check for existing files.
        if (!preg_match('/\.php\/(.*)/i', $href) && !strpos($href, "?")) {
            if (file_exists(ROOT . $href)) {
                $href = $prependBase . $href;
            }
        } else

            if (substr($lowerhref, 0, strlen($base)) != strtolower($base) &&
                substr($lowerhref, 0, strlen($root . $base)) != strtolower($root . $base) &&
                substr($lowerhref, 0, 2) != "./"
            ) {
                $href = $prependBase . $base . $href;
            }

        $newlink = $beforeHref . trim('"' . $href . '" ' . $attrs) . $afterHref;

        return $newlink;
    }

    /**
     * jshandler.
     *
     * @name    jsFile
     * @return    string
     */
    static function jsFile($file)
    {
        Resources::add($file, "js", "tpl");
    }

    /**
     * jshandler.
     *
     * @name    js
     * @return    string
     */
    static function js($js)
    {
        Resources::addJS($js, "scripts");
    }

    /**
     * csshandler
     *
     * @name    css
     * @return    string
     */
    static function css($css)
    {
        $name = "hash." . md5($css) . ".css";
        $file = CACHE_DIRECTORY . "/" . $name;
        if (file_exists($file)) {
            return '<link rel="stylesheet" href="' . $file . '" type="text/css" />';
        } else {
            if ($h = fopen($file, 'w')) {
                fwrite($h, $css);
                fclose($h);

                return '<link rel="stylesheet" href="' . $file . '" type="text/css" />';
            } else {
                fclose($h);

                return "";
            }
        }
    }

    /**
     * lists all words in a given HTML-Code
     *
     * @name listWords
     * @access public
     * @return array
     */
    static function list_words($orghtml)
    {
        // replace img-tags with alt-attribute
        $html = preg_replace('/\<img[^\/\>]+alt="([^"]+)"[^\/\>]+\/\>/', '$1', $orghtml);

        $text = html_entity_decode(strip_tags($html));

        $words = preg_split("/[\s\W]/", $text, -1, PREG_SPLIT_NO_EMPTY);

        return $words;
    }


    /**
     * lists all words in a given HTML-Code
     *
     * @name listWordsInTag
     * @access public
     */
    static function list_words_in_tag($orghtml, $tag)
    {
        // replace img-tags with alt-attribute
        preg_match_all('/\<' . preg_quote($tag, "/") . '[^\>]*\>(.*)\<\/' . preg_quote($tag, "/") . '\s*\>/Usi', $orghtml, $matches);
        $words = array();
        foreach ($matches[1] as $text) {
            $words += self::list_words($text);
        }

        return $words;
    }

    /**
     * Rates a word in the given context
     * Returnvalues can be between 0 and 3
     *
     * @access public
     * @return int - result
     */

    public function rate_word($word, $context)
    {
        if (empty($word) || empty($context))
            return 0;

        $value = self::count_word($word, $context);
        $div = $value;

        $count = self::count_words_in_important_sentences($word, $context);
        $value += $count[0];
        $value += $count[1];
        $value += self::count_title_word($word, $context);

        return $value / $div;
    }

    /**
     * Count words in context
     *
     * @access public
     * @return int
     * */


    public function count_word($word, $context)
    {
        $count = preg_split($word, $context, -1);
        $count = sizeof($count);

        if (!($count <= 0))
            $count--;

        return $count;
    }

    /**
     * Count words in titles
     *
     * @access public
     * @return int
     * */

    public function count_title_word($word, $context)
    {
        $title = preq_split("\<h(1|2|3|4|5|6)\>", $context, -1);
        if (sizeof($title < 2))
            return 0;
        $count_title = 0;

        foreach ($title as $element) {
            $count_title += count(preg_split($word, $element, -1));
        }

        return $count_title - 1;
    }

    /**
     * Count words in questions and exclamations
     *
     * @access public
     * @return int
     * */

    public function count_words_in_important_sentences($word, $context)
    {
        $questions = preg_split('?', $context, -1);
        $count_excl = 0;
        $count_quest = 0;

        foreach ($questions as $element) {
            if (strpos($element, '.') !== false) {
                $split = preg_split('.', $element, -1);
                $element = $split[sizeof($split) - 1];

                if (strpos($element, '!') !== false) {
                    $split = preg_split('!', $element, -1);
                    $count_excl += sizeof($split) - 1;
                    $element = $split[sizeof($split) - 1];
                }
            }

            $tmp = preg_split($word, $context, -1);
            $count_quest += sizeof($tmp) - 1;
        }

        $count = array();
        $count[] = $count_quest;
        $count[] = $count_excl;

        return $count;
    }
}
