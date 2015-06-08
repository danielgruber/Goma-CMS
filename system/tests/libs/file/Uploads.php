<?php defined("IN_GOMA") OR die();
/**
 * Unit-Tests for Uploads-Class.
 *
 * @package		Goma\Test
 *
 * @author		Goma-Team
 * @license		GNU Lesser General Public License, version 3; see "LICENSE.txt"
 */


class UploadsTest extends GomaUnitTest {

	/**
	 * area
	*/
	static $area = "Files";

	/**
	 * internal name.
	*/
	public $name = "Uploads";

	/**
	 * setup.
	*/
	public function setUp() {
		$this->testfile = "./system/tests/resources/IMG_2008.jpg";
		$this->filename = "uploads_testimg.jpg";

		// force no old versions of file.
		$data = DataObject::get("uploads", array("md5" => md5_file($this->testfile)));

		if($data->count() > 0) {
			foreach($data as $record) {
				$record->remove(true);
			}
		}

		$this->testTextFile = FRAMEWORK_ROOT . "temp/test.txt";
		file_put_contents($this->testTextFile, randomString(100));
	}

	public function tearDown() {
		@unlink($this->testTextFile);
	}

	public function testAddExistsAndRemove() {

		// store first file.
		$file = Uploads::addFile($this->filename, $this->testfile, "FormUpload");


		$this->assertEqual($file->filename, $this->filename);
		$this->assertEqual($file->classname, "imageuploads");
		$this->assertTrue(file_exists($file->realfile));
		$this->assertEqual(md5_file($file->realfile), md5_file($this->testfile));
		$this->assertEqual(md5_file($this->testfile), $file->md5);
		$this->assertEqual($file->collection->filename, "FormUpload");


		// check for second file, which should be stored.
		$file2 = Uploads::addFile($this->filename . ".jpg", $this->testfile, "FormUpload");
		$this->assertTrue(file_exists($file2->realfile));
		$this->assertEqual($file->realfile, $file2->realfile);
		$this->assertNotEqual($file->filename, $file2->filename);
		$this->assertEqual($file->md5, $file2->md5);

		$file3 = Uploads::addFile($this->filename, $this->testfile, "TestUpload");
		$this->assertTrue(file_exists($file3->realfile));
		$this->assertNotEqual($file->realfile, $file3->realfile);
		$this->assertEqual($file->filename, $file3->filename);
		$this->assertEqual($file->md5, $file3->md5);

		$this->assertTrue($file->bool());

        $this->assertNotNull($file2);

        if(isset($file2)) {
            $this->assertTrue($file2->bool());

            // check for file if we delete one.
            $file2->remove(true);
            $this->assertFalse($file2->bool());
        }

		$this->assertTrue(file_exists($file->realfile));

		$this->textFileTests();

		// try to get file.
		$path = $file->path;

		$this->match($path, $file);
		$this->match(BASE_URI . $path, $file);
		$this->match(BASE_URI . $path . "/orgSetSize/20/20/", $file);
		$this->match("./" . $path . "/orgSetSize/20/20/", $file);
		$this->match("./" . $path, $file);

		if($img = Uploads::getFile($path)) {
			$img->remove(true);

			$this->assertFalse($img->bool());
			$this->assertFalse(file_exists($file->realfile));
		} else {
			$this->assertTrue(false);
		}

        $textfile = Uploads::getFile($this->textfile->fieldGet("path"));
        if(isset($textfile)) {

            $textfile->remove(true);
            $this->assertFalse(file_exists($textfile->realfile));

        }


	}

	public function testNoDBInterface() {
		$f = new Uploads(array(
			"filename" 		=> "test.txt",
			"type"			=> "file",
			"realfile"		=> $this->testTextFile,
			"path"			=> "",
			"collectionid" 	=> 0,
			"deletable"		=> true,
			"md5"			=> null
		));

		$this->assertTrue(file_exists($f->realfile));
		$this->assertFalse($f->collection);
		$this->assertEqual($f->hash(), $f->realfile, "hash()-method should return md5 of filename.");
	}

	public function textFileTests() {
		$textfilename = basename($this->testTextFile);
		$textfile = Uploads::addFile(basename($this->testTextFile), $this->testTextFile, "FormUpload");
		$this->assertEqual($textfile->filename, $textfilename);
		$this->assertEqual($textfile->classname, "uploads");
		$this->assertTrue(file_exists($textfile->realfile));
		$this->assertEqual(md5_file($textfile->realfile), md5_file($this->testTextFile));
		$this->assertEqual(md5_file($this->testTextFile), $textfile->md5);
		$this->assertEqual($textfile->collection->filename, "FormUpload");

		$this->textfile = $textfile;
	}



	public function match($path, $file) {
		$match = Uploads::getFile($path);
		$this->assertEqual($match->md5, $file->md5);
		$this->assertEqual($match->filename, $file->filename);
	}
}