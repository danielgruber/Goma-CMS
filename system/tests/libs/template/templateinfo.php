<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Template-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class TemplateInfoTest extends GomaUnitTest {
    /**
     * area
     */
    static $area = "Template";

    /**
     * internal name.
     */
    public $name = "TemplateInfo";

    public function testFilterTemplates() {

        $this->assertEqual(TemplateInfo::get_available_templates("thisappdoesnotexist", "1.0", "1.0"), array());
        $this->assertEqual(TemplateInfo::get_available_templates("gomacms", "100.0-10000", "0"), array());
        $this->assertEqual(TemplateInfo::get_available_templates("gomacms", "0", "100.0-10000"), array());
        $this->assertEqual(count(TemplateInfo::get_available_templates(null, null, "100.0-10000")), count(TemplateInfo::getTemplates()));
        $this->assertTrue((count(TemplateInfo::get_available_templates("gomacms", "100.0-10000", "100.0-10000")) > 0));
    }

    public function initDir($author) {
        $this->random = randomString(10);
        $this->dir = "tpl/" . $this->random;
        while(file_exists($this->dir)) {
            $this->random = randomString(10);
            $this->dir = "tpl/" . $this->random;
        }

        FileSystem::requireDir($this->dir);
        FileSystem::requireDir($this->dir . "/resources");
        touch($this->dir . "/resources/image.png");
        $this->createPlist($this->dir . "/info.plist", "2.1", "resources/image.png", $author);
    }

    public function removeDir() {
        FileSystem::delete($this->dir);
    }

    public function testScreenshotAndPlist() {
        $count = count(TemplateInfo::get_available_templates(ClassInfo::$appENV["app"]["name"], ClassInfo::appVersion(), GOMA_VERSION . "-" . BUILD_VERSION));

        $this->author = randomString(10);
        $this->initDir($this->author);


        $ncount = count(TemplateInfo::get_available_templates(ClassInfo::$appENV["app"]["name"], ClassInfo::appVersion(), GOMA_VERSION . "-" . BUILD_VERSION));
        $this->assertEqual($count + 1, $ncount);

        $array = TemplateInfo::get_plist_contents($this->random);
        $this->assertEqual($array["author"], $this->author);
        $this->assertEqual($array["screenshot"], "tpl/".$this->random."/resources/image.png");
        $this->assertEqual($array["requireAppVersion"], ClassInfo::appVersion());

        $this->assertEqual(TemplateInfo::get_key($this->random, "author"), $this->author);
        $this->assertEqual(TemplateInfo::get_key($this->random, "screenshot"), "tpl/".$this->random."/resources/image.png");
        $this->assertEqual(TemplateInfo::get_key($this->random, "notExisting"), null);

        $this->removeDir();

        $n2count = count(TemplateInfo::get_available_templates(ClassInfo::$appENV["app"]["name"], ClassInfo::appVersion(), GOMA_VERSION . "-" . BUILD_VERSION));

        $this->assertEqual($n2count + 1, $ncount);
        $this->assertEqual($n2count, $count);
    }

    public function createPlist($file, $version, $screenshot, $author) {

        /*
         * create a new CFPropertyList instance without loading any content
         */
        $plist = new CFPropertyList();
        /*
         * Manuall Create the sample.xml.plist
         */
        // the Root element of the PList is a Dictionary
        $plist->add( $dict = new CFDictionary() );

        $dict->add( 'type', new CFString( "template" ) );

        $dict->add( 'author', new CFString($author) );

        $dict->add( 'version', new CFString($version) );

        $dict->add( 'requireApp', new CFString(ClassInfo::$appENV["app"]["name"]) );

        $dict->add( 'requireAppVersion', new CFString( ClassInfo::appVersion() ) );

        $dict->add( 'requireFrameworkVersion', new CFString( GOMA_VERSION . "-" . BUILD_VERSION ) );

        $dict->add( 'screenshot', new CFString($screenshot) );

        /*
         * Save PList as XML
         */
        $plist->saveXML( $file );
    }
}