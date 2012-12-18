<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2012  Goma-Team
  * last modified: 09.12.2012
*/   

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class imageResize extends RequestHandler
{
		public function handleRequest($request, $subController = false)
		{
				session_write_close();
				$this->request = $request;
				$this->subController = $subController;
				
				$this->init();
				
				if(strtolower($request->getParam(1)) == "images")
				{
						if(_ereg('^[0-9]+$',$request->getParam("height")))
						{
								if($request->getParam("width") == "x") {
									if(_eregi('\.(jpg|jpeg|png|bmp|gif)$', $request->remaining(), $ext))
									{
											$extension = $ext[1];
											$name = substr($request->remaining(), 0, 0 - strlen($ext[1]) - 1);
											return $this->resizeByHeight($request->getParam("height"), $name, $extension);
									}
								} else {
									if(_eregi('\.(jpg|jpeg|png|bmp|gif)$', $request->remaining(), $ext))
									{
											$extension = $ext[1];
											$name = substr($request->remaining(), 0, 0 - strlen($ext[1]) - 1);
											return $this->resize($request->getParam("width"), $request->getParam("height"), $name, $extension);
									}
								}
						} else 
						{
								if(_eregi('\.(jpg|jpeg|png|bmp|gif)$', $request->getParam("height") . '/' . $request->remaining(), $ext))
								{
										$extension = $ext[1];
										$name = substr($request->getParam("height") . '/' . $request->remaining(), 0, 0 - strlen($ext[1]) - 1);
										return $this->resizeByWidth($request->getParam("width"),$name, $extension);
								} else if(_eregi('\.(jpg|jpeg|png|bmp|gif)$', $request->getParam("height"), $ext)) {
										$extension = $ext[1];
										$name = substr($request->getParam("height"), 0, 0 - strlen($ext[1]) - 1);
										return $this->resizeByWidth($request->getParam("width"),$name, $extension);
								}
						}
				} else
				{
						if(_ereg('^[0-9]+$',$request->getParam("height")))
						{
								if($request->getParam("width") == "x") {
									if(_eregi('\.(jpg|jpeg|png|bmp|gif)$', $request->remaining(), $ext))
									{
											$extension = $ext[1];
											$name = substr($request->remaining(), 0, 0 - strlen($ext[1]) - 1);
											return $this->_resizeByHeight($request->getParam("height"), $name, $extension);
									}
								} else {
									if(_eregi('\.(jpg|jpeg|png|bmp|gif)$', $request->remaining(), $ext))
									{
											$extension = $ext[1];
											$name = substr($request->remaining(), 0, 0 - strlen($ext[1]) - 1);
											return $this->_resize($request->getParam("width"), $request->getParam("height"), $name, $extension);
									}
								}
						} else 
						{
								if(_eregi('\.(jpg|jpeg|png|bmp|gif)$', $request->getParam("height") . '/' . $request->remaining(), $ext))
								{
										$extension = $ext[1];
										$name = substr($request->getParam("height") . '/' . $request->remaining(), 0, 0 - strlen($ext[1]) - 1);
										return $this->_resizeByWidth($request->getParam("width"),$name, $extension);
								} else if(_eregi('\.(jpg|jpeg|png|bmp|gif)$', $request->getParam("height"), $ext))
								{
										$extension = $ext[1];
										$name = substr($request->getParam("height"), 0, 0 - strlen($ext[1]) - 1);
										return $this->_resizeByWidth($request->getParam("width"),$name, $extension);
								}
						}
				}
		}
		/**
		 * resizes an image
		 *@name resizeByWidth
		 *@access public
		 *@param numeric - new width
		 *@param string - filename
		 *@param string - file_extension
		*/
		public function resizeByWidth($width, $file, $extension)
		{
				
				$file = FileSystem::protect($file . "." . $extension);
				
				$image = new Image($file);
				
				if(isset($image->md5))
				{
						$img = $image->resizeByWidth($width);
						if(substr($file, 0, 7) == "Uploads") {
							FileSystem::requireDir(dirname(ROOT . URL));
							$img->toFile(ROOT . URL);
						}
						$img->Output();
						exit;
				}
				return $file . "." . $extension . " does not exist.";
		}
		/**
		 * resizes an image
		 *@name resizeByHeight
		 *@access public
		 *@param numeric - new height
		 *@param string - filename
		 *@param string - file_extension
		*/
		public function resizeByHeight($height, $file, $extension)
		{
				
				$file = FileSystem::protect($file . "." . $extension);
				
				$image = new Image($file);
				
				if(isset($image->md5))
				{
						$img = $image->resizeByHeight($height);
						if(substr($file, 0, 7) == "Uploads") {
							FileSystem::requireDir(dirname(ROOT . URL));
							$img->toFile(ROOT . URL);
						}
						$img->Output();
						exit;
				}
				return $file . "." . $extension . " does not exist.";
		}
		/**
		 * resizes an image
		 *@name resize
		 *@access public
		 *@param numeric - new width
		 *@param numeric - new height
		 *@param string - filename
		 *@param string - file_extension
		*/
		public function resize($width, $height, $file, $extension)
		{
				$file = FileSystem::protect($file . "." . $extension);
				
				$image = new Image($file);
				
				if(isset($image->md5))
				{
						$img = $image->resize($width, $height);
						if(substr($file, 0, 7) == "Uploads") {
							FileSystem::requireDir(dirname(ROOT . URL));
							$img->toFile(ROOT . URL);
						}
						$img->Output();
						exit;
				}
				return $file . "." . $extension . " does not exist.";
		}
		
		/**
		 * ROOTImage
		*/
		
		/**
		 * resizes an image
		 *@name resizeByWidth
		 *@access public
		 *@param numeric - new width
		 *@param string - filename
		 *@param string - file_extension
		*/
		public function _resizeByWidth($width, $file, $extension)
		{
				
				$file = FileSystem::protect($file . "." . $extension);
				
				$image = new ROOTImage($file);
				
				if(isset($image->md5))
				{
						$img = $image->resizeByWidth($width);
						if(substr($file, 0, 7) == "Uploads") {
							FileSystem::requireDir(dirname(ROOT . URL));
							$img->toFile(ROOT . URL);
						}
						$img->Output();
						exit;
				}
				return $file . "." . $extension . " does not exist.";
		}
		/**
		 * resizes an image
		 *@name resizeByHeight
		 *@access public
		 *@param numeric - new height
		 *@param string - filename
		 *@param string - file_extension
		*/
		public function _resizeByHeight($height, $file, $extension)
		{
				
				$file = FileSystem::protect($file . "." . $extension);
				
				$image = new ROOTImage($file);
				
				if(isset($image->md5))
				{
						
						$img = $image->resizeByHeight($height);
						if(substr($file, 0, 7) == "Uploads") {
							FileSystem::requireDir(dirname(ROOT . URL));
							$img->toFile(ROOT . URL);
						}
						$img->Output();
						exit;
				}
				return $file . "." . $extension . " does not exist.";
		}
		/**
		 * resizes an image
		 *@name resize
		 *@access public
		 *@param numeric - new width
		 *@param numeric - new height
		 *@param string - filename
		 *@param string - file_extension
		*/
		public function _resize($width, $height, $file, $extension)
		{
				$file = FileSystem::protect($file . "." . $extension);
				
				$image = new ROOTImage($file);
				
				if(isset($image->md5))
				{
						$img = $image->resize($width, $height);
						if(substr($file, 0, 7) == "Uploads") {
							FileSystem::requireDir(dirname(ROOT . URL));
							$img->toFile(ROOT . URL);
						}
						$img->Output();
						exit;
				}
				return $file . "." . $extension . " does not exist.";
		}
}

