<?php
/**
  * extends the pages with the availablity of rating
  *
  *@package rating plugin
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 09.01.2013
  * $Version 1.0.1
*/   
 
defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

gloader::addLoadAble("rating", ClassInfo::getExpansionFolder("gomacms_rating") . "classes/rating.js");

class Rating extends DataObject
{
		/**
		 * db-fields
		 *
		 *@name db
		*/
		static $db = array(
			"name" 		=> "varchar(200)",
			"rates"		=> "int(10)",
			"rating"	=> "int(11)",
			"rators"	=> "text"
		);
		
		/**
		 * index
		*/
		static $index = array(
			"name" => "INDEX"
		);
		
		/**
		 * rights
		 *
		 *@name canWrite
		 *@access public
		*/
		public function canWrite($record = null)
		{
				return true;
		}
		
		/**
		 * checks if user already voted
		 *
		 *@name canInsert
		 *@access public
		*/
		public function canInsert($data = null, $check = false)
		{
				if($data["rators"] == "")
						return true;
				
				if($check)
				{
						$d = unserialize($data["rators"]);
						if(is_array($d))
								if(in_array($_SERVER["REMOTE_ADDR"], $d))
								{
										return false;
								} else
								{
										return true;
								}
						else
								return true;
				}
				else
						return true;
		}
		
		
		public function providePermissions()
		{
				return array(
					"RATING_ALL" => array(
						"default" 	=> 7,
						"hide" 		=> true,
						"implements" => array(
							"RATING_DELETE"
						)
					),
					"RATING_DELETE"	=> array(
						"title"		=> '{$_lang_exp_gomacms_rating.perms_delete}',
						"default"	=> 7
					)
				);
		}
		
		/**
		 * calculates the stars on special percision
		 * just 5 or 0 behind the commar are allowed
		 *@name GetCalculated
		 *@access public
		*/
		public function GetCalculated()
		{
				
				if($this->rates == 0)
				{
						return 0;
				}
				
				$count = round($this->rating / $this->rates, 1);
				if(in_array($count, array(1, 2, 3, 4, 5))) // check if exact number
				{
						$exact = true;
						return $count;
				} else
				{
						$number = substr((string)$count, 0, 1);
						$decimal = (int)substr((string)$count, -1);
						if($decimal > 2 && $decimal < 8)
						{
								$decimal = 5;
						} else
						{
								return $number;
						}
						
						return (int)$numer . "." . $decimal;
				}
		}
		
		/**
		 * gets the rendered stars
		 *@name getStars
		 *@access public
		*/
		public function getStars()
		{
				$starcount = $this->getcalculated();
				if(in_array($starcount, array(0,1, 2, 3, 4, 5))) // check if exact number
				{
						$half = false;
						$count = $starcount;
				} else
				{
						$half = true;
						$count = substr($starcount, 0, 1);
				}
				
				$output = "<div class=\"stars\" title=\"".$this->name."\">";
				$canWrite = $this->canInsert($this, true);
				
				for($i = 0; $i < 5; $i++)
				{
						$star = $i + 1;
						if($canWrite)
						{
								if($i < $count)
										$output .= '<a id="star_'.$this->name.'_'.$star.'" class="star_yellow star" href="rate/'.$this->name.'/'.$star.'" rel="ajaxfy" title="'.$star.'""><img src="images/star_yellow.png" alt="'.$star.'" /></a>';
								else if($i == $count && $half)
										$output .= '<a id="star_'.$this->name.'_'.$star.'" class="star_half star" href="rate/'.$this->name.'/'.$star.'" rel="ajaxfy" title="'.$star.'""><img src="images/star_half.png" alt="'.$star.'" /></a>';
								else
										$output .= '<a id="star_'.$this->name.'_'.$star.'" class="star_grey star" href="rate/'.$this->name.'/'.$star.'" rel="ajaxfy" title="'.$star.'""><img src="images/star_grey.png" alt="'.$star.'" /></a>';
						} else
						{
								if($i < $count)
										$output .= '<img src="images/star_yellow.png" alt="'.$star.'" />';
								else if($i == $count && $half)
										$output .= '<img src="images/star_half.png" alt="'.$star.'" />';
								else
										$output .= '<img src="images/star_grey.png" alt="'.$star.'" />';

						}
				}		
				$output .= "</div>";
				
				if($this->rates == 1)
				{
						$output .= "1 ".lang("exp_gomacms_rating.vote", "vote")."";
				} else if($this->rates == 0)
				{
						$output .= "0 " . lang("exp_gomacms_rating.votes", "votes");
				} else
				{
						$output .= "".$this->rates." " . lang("exp_gomacms_rating.votes", "votes");
				}
				
				return $output;
		}
		
		/**
		 * draws the rating
		*/
		public static function draw($name)
		{
				$name = strtolower($name);
				$data = DataObject::get("rating",array("name" => $name));
				$data->name = $name;
				
				
				$message = isset($_SESSION["rating_message"]) ? $_SESSION["rating_message"] : "";
				if(isset($_SESSION["rating_message"]))
						unset($_SESSION["rating_message"]);
				
				gloader::load("rating");
				
				return '<div id="rating_'.$name.'">'. $data->stars . '</div><div class="message">'.$message.'</div>';
				
		}
}

class ratingController extends Controller
{
		/**
		 *@name handleRequet
		 *@access public
		*/
		public function handleRequest($request, $subController = false)
		{
				$this->request = $request;
				
				$this->Init();
						
				$name = strtolower($request->getParam("name"));
				$rate = $request->getParam("rate");
				if(DataObject::count("rating", array("name" => $name)) == 0)
				{
						$model = new rating;
						if($model->canInsert($model, true))
						{
								$model->rates = 1;
								$model->rating = $rate;
								$model->rators = serialize(array($_SERVER["REMOTE_ADDR"]));
								$model->name = $name;
								if($model->write())
								{
										if(request::isJSResponse())
										{
												HTTPResponse::addHeader("content-type", "text/javascript");
												$response = new AjaxResponse;
												$response->exec('$("#rating_'.$name.'").html("'.convert::raw2js($model->stars).'<div class=\"message\">'.lang("exp_gomacms_rating.thanks_for_voting").'</div>");');
												return $response->render();
										} else
										{
												$this->redirectback();
										}
								} else
								{
										if(request::isJSResponse())
										{
												HTTPResponse::addHeader("content-type", "text/javascript");
												$response = new AjaxResponse;
												$response->exec("alert('".lang("exp_gomacms_rating.rated")."');");
												return $response->render();
										} else
										{
												$_SESSION["rating_message"] = lang("exp_gomacms_rating.rated");
												$this->redirectback();
										}
								}
						} else
						{
								if(request::isJSResponse())
								{
										HTTPResponse::addHeader("content-type", "text/javascript");
										$response = new AjaxResponse;
										$response->exec("alert('".lang("exp_gomacms_rating.rated")."');");
										return $response->render();
								} else
								{
										$_SESSION["rating_message"] = lang("exp_gomacms_rating.rated");
										$this->redirectback();
								}
						}
				} else
				{
						$model = DataObject::get("rating",array("name" => $name));
						if($model->canInsert($model, true))
						{
								$model->rates += 1;
								$model->rating += $rate;
								$model->rators = serialize(array_merge(array($_SERVER["REMOTE_ADDR"]), (array)unserialize($model->rators)));
								if($model->write())
								{
										if(request::isJSResponse())
										{
												HTTPResponse::addHeader("content-type", "text/javascript");
												$response = new AjaxResponse;
												$response->exec('$("#rating_'.$name.'").html("'.convert::raw2js($model->stars).'<div class=\"message\">'.lang("exp_gomacms_rating.thanks_for_voting").'</div>");');
												return $response->render();
										} else
										{
												$this->redirectback();
										}
								} else
								{
										if(request::isJSResponse())
										{
												HTTPResponse::addHeader("content-type", "text/javascript");
												$response = new AjaxResponse;
												$response->exec("alert('".lang("exp_gomacms_rating.rated")."');");
												return $response->render();
										} else
										{
												$_SESSION["rating_message"] = lang("exp_gomacms_rating.rated");
												$this->redirectback();
										}
								}
						} else
						{
								if(request::isJSResponse())
								{
										HTTPResponse::addHeader("content-type", "text/javascript");
										$response = new AjaxResponse;
										$response->exec("alert('".lang("exp_gomacms_rating.rated")."');");
										return $response->render();
								} else
								{
										$_SESSION["rating_message"] = lang("exp_gomacms_rating.rated");
										$this->redirectback();
								}
						}
				}
		}
}

class RatingDataObjectExtension extends DataObjectExtension {
	/**
	 * add db-field for switching
	*/
	static $db = array(
		'rating' 			=> 'int(1)'
	);
	/**
	 * set defaults
	*/
	static $default = array(
		"rating" => 0
	);
	/**
	 * appends rating
	*/
	public function appendContent(&$content) {
		if($this->getOwner()->rating)
			$content->prepend(Rating::draw("page_" . $this->getOwner()->id));
	}
	/**
	 * renders the field in the form
	*/
	public function getForm(&$form) {
		$form->meta->add(new Checkbox('rating', lang('exp_gomacms_rating.allow_rate')), 7);
	}
}

Object::extend("pages", "RatingDataObjectExtension");