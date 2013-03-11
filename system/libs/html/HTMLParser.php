<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: http://www.gnu.org/licenses/gpl-3.0.html see 'license.txt'
  *@Copyright (C) 2009 - 2013  Goma-Team
  * last modified: 11.03.2013
  * $Version: 2.2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

class HTMLParser extends Object
{
		/**
		 * parses HTML-code
		 *@name parseHTML
		 *@return string
		*/
		static function parseHTML($html)
		{
				if(PROFILE) Profiler::mark("HTMLParser::parseHTML");
				
				if(PROFILE) Profiler::mark("HTMLParser scriptParse");
				if(!HTTPResponse::$disabledparsing)
				{
						preg_match_all('/\<script[^\>]*\>(.*)\<\/script\s*\>/Usi', $html, $no_tags);
						foreach($no_tags[1] as $key => $js)
						{
								if(!empty($js))
								{
										$html = str_replace($no_tags[0][$key], self::js($js), $html );
								}
						}

						
						preg_match_all('/\<script[^\>]*src="(.+)"[^>]*\>(.*)\<\/script\s*\>/Usi', $html, $no_tags);
						foreach($no_tags[1] as $key => $js)
						{
								if(!empty($js) && file_exists(ROOT . $js))
								{
										$html = str_replace($no_tags[0][$key], self::jsFile(ROOT . $js), $html );
								}
						}
				}
				if(PROFILE) Profiler::unmark("HTMLParser scriptParse");
				
				if(!Core::is_ajax())
					if(_eregi('</title>',$html)) {
						if(_eregi('\<base',$html)) {
							$html = str_replace('</title>', "</title>\n		<meta charset=\"utf-8\" />\n\n		<!--Resources-->\n" . resources::get() . "\n", $html);
						} else {
							$html = str_replace('</title>', "</title>\n		<meta charset=\"utf-8\" />\n<base href=\"".BASE_URI."\" />\n\n\n		<!--Resources-->\n" . resources::get() . "\n", $html);
						}
					} else {
						if(_eregi('\<base',$html)) {
							$html = '<meta charset="utf-8" />' . resources::get() . $html;
						} else {
							$html = '<!DOCTYPE html><html><head><meta charset="utf-8" /><title></title><base href="'.BASE_URI.'" />' . "\n".resources::get() . "\n</head><body>" . $html . "\n</body></html>";
						}
					}
				
				if(!HTTPResponse::$disabledparsing)
				{
						$html = self::process_links($html);
				}
				if(PROFILE) Profiler::unmark("HTMLParser::parseHTML");
				return $html;
		}
		
		/**
		  * processes links for non-mod-rewrite
		  *@name process_links
		  *@access public
		  *@param string - html
		*/
		public static function process_links($html)
		{
				if(PROFILE) Profiler::mark("HTMLParser::process_links");
				preg_match_all('/<a([^>]+)href="([^">]+)"([^>]*)>/Usi', $html, $links);
				foreach($links[2] as $key => $href)
				{
						$attrs = "";
						// check http
						if(preg_match('/^(http|https|ftp)/Usi', $href))
						{
								continue;
						}
						if(preg_match('/^#(.+)/', $href, $m))
						{
								$href = URL . URLEND . "#" . $m[1];
								$attrs = ' data-anchor="'.$m[1].'"';
						}
						if(preg_match('/^javascript:/i', $href))
						{
								continue;
						}
						if(preg_match('/^mailto:/', $href))
						{
								continue;
						}
						
						// check ROOT_PATH
						if(preg_match('/^' . preg_quote(ROOT_PATH, '/') . '/Usi', $href))
						{
								$href = substr($href, strlen(ROOT_PATH));
						}
						
						if(!_eregi('\.php/(.*)', $href) && !strpos($href, "?"))
						{
								if(file_exists(ROOT . $href))
								{
										continue;
								}
						}
						
						if(preg_match('/^' . preg_quote(BASE_SCRIPT, '/') . '/Usi', $href) || preg_match('/^.\/' . preg_quote(BASE_SCRIPT, '/') . '/Usi', $href))
						{
						
						} else
						{
								$href = BASE_SCRIPT . $href;
						}
						$newlink = '<a'.$links[1][$key].'href="'.$href.'"'.$links[3][$key].' '.$attrs.'>';
						$html = str_replace($links[0][$key], $newlink, $html);
				}
				
				preg_match_all('/<iframe([^>]+)src="([^">]+)"([^>]*)>/Usi', $html, $frames);
				foreach($frames[2] as $key => $href)
				{
						// check http
						if(preg_match('/^(http|https|ftp)/Usi', $href))
						{
								continue;
						}
						if(preg_match('/^#/', $href))
						{
								continue;
						}
						// check ROOT_PATH
						if(preg_match('/^' . preg_quote(ROOT_PATH, '/') . '/Usi', $href))
						{
								$href = substr($href, strlen(ROOT_PATH));
						}
						if(!_eregi('\.php/(.+)', $href) && !strpos($href, "?"))
						{
								if(file_exists(ROOT . $href))
								{
										continue;
								}
						}
						if(preg_match('/^' . preg_quote(BASE_SCRIPT, '/') . '/Usi', $href) || preg_match('/^.\/' . preg_quote(BASE_SCRIPT, '/') . '/Usi', $href))
						{
						
						} else
						{
								$href = BASE_SCRIPT . $href;
						}
						$newframes = '<iframe'.$frames[1][$key].'src="'.$href.'"'.$frames[3][$key].'>';
						$html = str_replace($frames[0][$key], $newframes, $html);
				}
				
				preg_match_all('/<img([^>]+)src="([^">]+)"([^>]*)>/Usi', $html, $images);
				foreach($images[2] as $key => $href)
				{
						if(!preg_match('/^images\/resampled/i', $href)) {
							continue;
						}
						$href = BASE_SCRIPT . $href;
						$newframes = '<img'.$images[1][$key].'src="'.$href.'"'.$images[3][$key].' />';
						$html = str_replace($images[0][$key], $newframes, $html);
				}
				
				if(PROFILE) Profiler::unmark("HTMLParser::process_links");
				return $html;
		}
		
		/**
		 * jshandler
		 *@name jsFile
		 *@return string
		*/
		static function jsFile($file)
		{
				Resources::add($file, "js", "tpl");
		}
		
		/**
		 * jshandler
		 *@name js
		 *@return string
		*/
		static function js($js)
		{
				Resources::addJS($js, "scripts");
		}
		
		/**
		 * csshandler
		 *@name css
		 *@return string
		*/
		static function css($css)
		{
				$name = "hash." . md5($css) . ".css";
				$file = ROOT_PATH . "js/cache/" . $name;
				if(file_exists($_SERVER['DOCUMENT_ROOT'] . $file))
				{
						return '<link rel="stylesheet" href="'.$file.'" type="text/css" />';
				} else
				{
						if($h = fopen($_SERVER['DOCUMENT_ROOT'] . $file, 'w'))
						{
								fwrite($h, $css);
								fclose($h);
								return '<link rel="stylesheet" href="'.$file.'" type="text/css" />';
						} else
						{
								fclose($h);
								return "";
						}
				}
		}
		
		/**
		 * XSS protection
		 *@name: protect
		 *@param: string - text
		 *@use: protect html entitites
		 *@return the protected string
		*/
		static function protect($str)
		{
			return convert::raw2xml($str);
		}
		
		/**
		 * lists all words in a given HTML-Code
		 *
		 *@name listWords
		 *@access public
		*/
		static function list_words($orghtml) {
			// replace img-tags with alt-attribute
			$html = preg_replace('/\<img[^\/\>]+alt="([^"]+)"[^\/\>]+\/\>/', '$1', $orghtml);
			
			$text = html_entity_decode(strip_tags($html));
			
			$words = preg_split("/[\s\W]/", $text, -1, PREG_SPLIT_NO_EMPTY);
			
			return $words;
		}
		
		
		/**
		 * lists all words in a given HTML-Code
		 *
		 *@name listWordsInTag
		 *@access public
		*/
		static function list_words_in_tag($orghtml, $tag) {
			// replace img-tags with alt-attribute
			preg_match_all('/\<'.preg_quote($tag, "/").'[^\>]*\>(.*)\<\/'.preg_quote($tag, "/").'\s*\>/Usi', $orghtml, $matches);
			$words = array();
			foreach($matches[1] as $text) {
				$words += self::list_words($text);
			}
			return $words;
		}
		
		/**
		 * Rates a word in the given context
		 * Returnvalues can be between 0 and 3
		 * @access public
		 * @return int - result
		*/
		
		public function rate_word($word, $context)
		{
			if(empty($word) || empty($context))
				return 0;
			
			$value = count_word($word, $context);
			$div = $value;
			
			$value += count_title_words($word, $context);
			$value += count_question_words($word, $context);
			$value += count_exclamation_words($word, $context);
			
			return $value / $div;
		}
		
		
		public function count_word($word, $context)
		{
			$count = preg_split($word, $context, -1);
			$count = sizeof($count);
			
			if(!($count <= 0))
				$count --;
			
			return $count;	
		}
		
		public function count_title_words($word, $context)
		{
			
		}
		
		public function count_question_words($word, $context)
		{
			
		}
		
		public function count_exclamation_words($word, $context)
		{
			
		}
}
