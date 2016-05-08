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
		$file = Uploads::addFile("1" . $this->filename, $this->testfile, "FormUpload", null, true);

		// file1: Tests Deletable and some basics
		$this->assertEqual($file->deletable, "1");
		$file->deletable = false;
		$file->writeToDB(false, true);

		$this->assertEqual($file->deletable, "0");
		$this->assertEqual($file->filename, "1" . $this->filename);
		$this->assertEqual($file->classname, "imageuploads");
		$this->assertTrue(file_exists($file->realfile));
		$this->assertEqual(md5_file($file->realfile), md5_file($this->testfile));
		$this->assertEqual(md5_file($this->testfile), $file->md5);
		$this->assertEqual($file->collection->filename, "FormUpload");


		// file2 test: Tests deletable for same file and some tests with same file/collection
		// check for second file, which should be stored.
		$file2 = Uploads::addFile($this->filename . ".jpg", $this->testfile, "FormUpload", null, true);

		// should be 0, cause its same like file and should not be deletable.
		$this->assertEqual($file2->deletable, "1");

		$this->assertTrue(file_exists($file2->realfile));
		$this->assertEqual($file->realfile, $file2->realfile);
		$this->assertNotEqual($file->filename, $file2->filename);
		$this->assertEqual($file->md5, $file2->md5);

		// file3: tests stuff with different collection but same file
		$file3 = Uploads::addFile($this->filename, $this->testfile, "TestUpload");
		$this->assertTrue(file_exists($file3->realfile));
		$this->assertNotEqual($file->realfile, $file3->realfile);
		$this->assertEqual($file->filename, "1" . $file3->filename);
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

		// test deletes#
		// $img is now $file here!!
		if($img = Uploads::getFile($path)) {
			$this->assertEqual($img->md5, $file->md5);
			$this->assertEqual($img->md5, md5_file($this->testfile));

			FileSystem::requireDir($img->path);
			$this->assertTrue(file_exists($this->getFileWithoutBase($img->path)));

			$realfile = $img->realfile;
			$img->remove(true);

			$this->assertFalse(file_exists($this->getFileWithoutBase($img->path)));
			$this->assertFalse($img->bool());
			$this->assertFalse(file_exists($realfile));
		} else {
			$this->assertTrue(false);
		}

        $textfile = Uploads::getFile($this->textfile->fieldGet("path"));
        if(isset($textfile)) {
			$this->assertEqual($textfile->md5, md5_file($this->testTextFile));
            $textfile->remove(true);
            $this->assertFalse(file_exists($textfile->realfile));
        }

		$file3->remove(true);
	}

	/**
	 * gets file without base.
	 *
	 * @param string $file
	 * @return string
	 */
	protected function getFileWithoutBase($file) {
		if(substr($file, 0, strlen(BASE_SCRIPT)) == BASE_SCRIPT) {
			return substr($file, strlen(BASE_SCRIPT));
		}

		return $file;
	}

	/**
	 * checks for hash-method.
	 */
	public function testNoDBInterface() {
		/** @var Uploads $file */
		$file = new Uploads(array(
			"filename" 		=> "test.txt",
			"type"			=> "file",
			"realfile"		=> $this->testTextFile,
			"path"			=> "",
			"collectionid" 	=> 0,
			"deletable"		=> true,
			"md5"			=> null
		));

		$this->assertTrue(file_exists($file->realfile));
		$this->assertFalse($file->collection);
		$this->assertEqual($file->hash(), $file->realfile, "hash()-method should return md5 of filename.");
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

	/**
	 * tests collections.
	 */
	public function testCollection() {
		$collection = "test.c.t.b.a.d.t.d.e.d";
		$file = Uploads::addFile($this->filename, $this->testfile, $collection);

		$this->assertEqual($file->collection->collection->collection->collection->filename, "t");
		$this->assertEqual($file->collection->collection->collection->collection->collection->filename, "d");
		$this->assertEqual($file->collection->collection->collection->collection->getSubCollection("d")->filename, "d");

		$file->remove(true);

		$this->assertNull(Uploads::getCollection($collection, true, false));
	}

	/**
	 * tests for filenames which are not normal.
	 */
	public function testStrangeFilenames() {

		$collection1 = "FormUpload";
		$collection2 = "FormUpload.Blub";
		$collection3 = "t.b.a.d.t.d.e.d";

		$this->assertPattern(
			"/^Uploads\/".preg_quote(md5($collection1), "/")."\/[a-zA-Z0-9]+\/file_123_.jpg$/",
			$this->unitTestStrangeFilename("file+123 .jpg", $this->testfile, $collection1)
		);

		$this->assertPattern(
			"/^Uploads\/".preg_quote(md5($collection2), "/")."\/[a-zA-Z0-9]+\/file_123_.jpg$/",
			$this->unitTestStrangeFilename("file+123 .jpg", $this->testfile, $collection2)
		);

		$this->assertPattern(
			"/^Uploads\/".preg_quote(md5($collection2), "/")."\/[a-zA-Z0-9]+\/file-123_.jpg$/",
			$this->unitTestStrangeFilename("file-123 .jpg", $this->testfile, $collection2)
		);
		$this->assertPattern(
			"/^Uploads\/".preg_quote(md5($collection3), "/")."\/[a-zA-Z0-9]+\/file-123_.jpg$/",
			$this->unitTestStrangeFilename("file-123 .jpg", $this->testfile, $collection3)
		);
	}

	public function unitTestStrangeFilename($filename, $testfile, $collection) {
		// store first file.
		$file = Uploads::addFile($filename, $testfile, $collection);
		$path = $file->path;

		$file->remove(true);

		return $path;
	}
}
