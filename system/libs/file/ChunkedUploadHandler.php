<?php
defined("IN_GOMA") OR die();

/**
 * Describe your class
 *
 * @package Goma
 *
 * @author Goma-Team
 * @copyright 2016 Goma-Team
 * @license GNU Lesser General Public License, version 3; see "LICENSE.txt"
 *
 * @version 1.0
 */
class ChunkedUploadHandler {
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $key;

    /**
     * status.
     *
     * @var int
     */
    protected $status;

    /**
     * chunked upload.
     * @param Request $request
     * @param string $name
     * @param string $key
     * @throws BadRequestException
     */
    public function __construct($request, $name, $key) {
        $this->request = $request;
        $this->name = $name;
        $this->key;

        if(!isset($this->request->post_params[$name])) {
            throw new BadRequestException("Upload not found");
        }

        foreach(array("x-file-name", "x-file-size", "content-range") as $header) {
            if(!$request->getHeader($header)) {
                throw new BadRequestException("Header " . $header . " required");
            }
        }

        $this->resolveContent();
    }

    public function getStatus() {
        if(!file_exists($this->statusFile())) {
            FileSystem::write($this->statusFile(), 0);
        }

        $this->status = file_get_contents($this->statusFile());
        return $this->status;
    }

    protected function resolveContent() {
        if(preg_match("/([0-9]+)\s*\-\s*([0-9]+)/", $this->request->getHeader("content-range"), $matches)) {
            $rangeStart = $matches[1];
            $rangeEnd = $matches[2];

            if($rangeStart == 0) {
                $this->delete();
            }

            if($fp = fopen($this->tmpFile(), "c")) {
                $retries = 0;
                $max_retries = 100;

                do {
                    if ($retries > 0) {
                        usleep(rand(1, 10000));
                    }
                    $retries += 1;
                } while (!flock($fp, LOCK_EX) and $retries <= $max_retries);

                fseek($fp, $rangeStart);
                if(fwrite($fp, file_get_contents($this->request->post_params[$this->name]["tmp_name"]), $rangeEnd - $rangeStart) === false) {
                    throw new FileException("Could not write data");
                }

                flock($fp, LOCK_UN);
                fclose($fp);

                FileSystem::write($this->statusFile(), $this->getStatus() + $rangeEnd - $rangeStart);
            } else {
                throw new FileException("Could not open file");
            }
        } else {
            throw new BadRequestException("Range Missing");
        }
    }

    public function delete() {
        FileSystem::delete($this->tmpFile());
        FileSystem::delete($this->statusFile());
    }

    public function statusFile() {
        return ROOT . "system/temp/upload." . $this->key . "status.goma";
    }

    public function tmpFile() {
        return ROOT . "system/temp/upload." . $this->key . ".goma";
    }

    public function isFinished() {
        return $this->getStatus() == $this->request->getHeader("x-file-size");
    }

    public function getFileArray() {
        return $this->isFinished() ? array(
            "name"      => $this->request->getHeader("x-file-name"),
            "size"      => $this->request->getHeader("x-file-size"),
            "error"     => UPLOAD_ERR_OK,
            "type"      => $this->request->post_params[$this->name]["type"],
            "tmp_name"  => $this->tmpFile()
        ) : null;
    }
}
