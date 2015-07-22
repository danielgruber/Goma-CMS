<?php
/**
  *@package goma framework
  *@link http://goma-cms.org
  *@license: LGPL http://www.gnu.org/copyleft/lesser.html see 'license.txt'
  *@author Goma-Team
  * last modified: 09.01.2013
  * $Version 1.2.1
*/

defined('IN_GOMA') OR die('<!-- restricted access -->'); // silence is golden ;)

loadlang('bbcode');

class BBcode extends TextTransformer
{
		/**
		 * smilies
		 *
		 *@name smilies
		*/
		public static $smilies = array(
			":evil:"		=> "system/templates/images/emoticons/emotion_evilgrin.png",
			":D"			=> "system/templates/images/emoticons/emotion_grin.png",
			"=D"			=> "system/templates/images/emoticons/emotion_grin.png",
			":))"			=> "system/templates/images/emoticons/emotion_happy.png",
			":)"			=> "system/templates/images/emoticons/emotion_smile.png",
			"=)"			=> "system/templates/images/emoticons/emotion_smile.png",
			":O"			=> "system/templates/images/emoticons/emotion_suprised.png",
			":P"			=> "system/templates/images/emoticons/emotion_tongue.png",
			"=P"			=> "system/templates/images/emoticons/emotion_tongue.png",
			":("			=> "system/templates/images/emoticons/emotion_unhappy.png",
			";)"			=> "system/templates/images/emoticons/emotion_wink.png",
		);
		
		/**
		 * transforms to BBcode
		 *@name transform
		*/
		public function transform()
		{
				
				$text = $this->text;
				
				// code
				
				$codes = array();
				$noparses = array();
				
				preg_match_all('/\[code\](.*?)\[\/code\]/si',$text,$code);
				
				foreach($code[0] as $key => $value)
				{
						$text = str_replace($value, '[code]' . $key . '[/code]', $text);
						$codes[$key] = "<div class=\"code\">
											<div class=\"codehead\">
												".lang("bb.code", "Source:")." 
												<br />&nbsp;
											</div>
											<div class=\"codetext\">
											" . highlight_string($code[1][$key]) . "
											</div>
										</div>
										";
				}
				
				$text = convert::raw2text($text);
				
				preg_match_all('/\[noparse\](.*?)\[\/noparse\]/si',$text,$noparse);
				
				foreach($noparse[0] as $key => $value)
				{
						$text = str_replace($value, '[noparse]' . $key . '[/noparse]', $text);
						$noparses[$key] = $noparse[1][$key];
				}
				
				
				
				
				$text = nl2br($text);
				$text = str_replace('  ','&nbsp;&nbsp;',$text);
				
				$this->callExtending("parseBBCode", $text);		
				
				/* lists */
				$text = str_replace('[li]','<li>',$text);
				$text = str_replace('[/li]','</li>',$text);
				$text = str_replace('[ul]','<ul>',$text);
				$text = str_replace('[/ul]','</ul>',$text);
				$text = str_replace('[ol]','<ol>',$text);
				$text = str_replace('[/ol]','</ol>',$text);
				
				$text = preg_replace('/\[list\](.*)\[\/list\]/Usi', '<ul>\\1</ul>', $text);
				$text = preg_replace('/\[list=a\](.*)\[\/list\]/Usi', '<ol sytle="list-style-type: lower-alpha;">\\1</ol>', $text);
				$text = preg_replace('/\[list=1\](.*)\[\/list\]/Usi', '<ol>\\1</ol>', $text);
				
				$text = preg_replace('/\[\*\](.*?)\n/si', '<li>\\1</li>', $text);
				
				/**
				 * standard-tags:
				 * url, i, u, b, img
				*/
				$text = preg_replace_callback('/\[url\](.*)\[\/url\]/Usi', array($this, 'url_callback'), $text);
				$text = preg_replace_callback('/\[url=(.*)\](.*)\[\/url\]/Usi', array($this, '_url_callback'), $text);
				$text = preg_replace('/\[img\](.*)\[\/img\]/Usi', '<img src="\\1" alt="\\1" />', $text);
				$text = preg_replace('/\[i\](.*)\[\/i\]/Usi', '<span style="font-style: italic;">\\1</span>', $text);
				$text = preg_replace('/\[b\](.*)\[\/b\]/Usi', '<strong>\\1</strong>', $text);
				$text = preg_replace('/\[u\](.*)\[\/u\]/Usi', '<span style="text-decoration: underline;">\\1</span>', $text);
				
				$text = preg_replace('/\[left\](.*)\[\/left\]/Usi', '<div style="text-align: left;">\\1</div>', $text);
				$text = preg_replace('/\[right\](.*)\[\/right\]/Usi', '<div style="text-align: right;">\\1</div>', $text);
				$text = preg_replace('/\[center\](.*)\[\/center\]/Usi', '<div style="text-align: center;">\\1</div>', $text);
				$text = preg_replace('/\[justify\](.*)\[\/justify\]/Usi', '<div style="text-align: justify;">\\1</div>', $text);
				
				/*heads*/
				
				$text = preg_replace('/\[h=(1|2|3|4|5|6)\](.*)\[\/h\]/Usi', '<h\\1>\\2</h\\1>', $text);
				
				/*colors*/
				
				$text = preg_replace('/\[color=([a-zA-Z0-9#_\-]+)\](.*)\[\/color\]/Usi', '<span style="color: \\1;">\\2</span>', $text);
				$text = preg_replace('/\[size=([0-9]+)\](.*)\[\/size\]/Usi', '<span style="font-size: \\1px;">\\2</span>', $text);
				
				/*
				 * quotes
				*/
				while(preg_match('/\[quote=(.*?)\](.*?)\[\/quote\]/si',$text))
				{
						$text = preg_replace('/\[quote=(.*?)\](.*?)\[\/quote\]/si','<blockquote><strong>\\1 '.lang("bb.has_written", "wrote:").'</strong><br />\\2</blockquote>',$text); // qoute
				}
				while(preg_match('/\[quote\](.*?)\[\/quote\]/si',$text))
				{
						$text = preg_replace('/\[quote\](.*?)\[\/quote\]/si','<blockquote class="quote"><div class="quotehead"><strong>'.lang("bb.quote", "Quotation:").'</strong></div><div class="quotetext">\\1</div></blockquote>',$text); // qoute
				}
				
				foreach($codes as $key => $value)
				{
						$text = str_replace('[code]'.$key.'[/code]', $value, $text);
				}
				
				foreach($noparses as $key => $value)
				{
						$text = str_replace('[noparse]'.$key.'[/noparse]', $value, $text);
				}
				
				foreach(Static::$smilies as $code => $file) {
					$text = str_replace($code,'<img src="'.$file.'" alt="'.$code.'" />',$text);
				}
				
				return $text;
				
		}
		
		/**
		 * for urls
		 *@name url_callback
		*/
		public function url_callback($matches)
		{
				$url = $matches[1];
				$title = $matches[1];
				
				if(strlen($title) > 50)
				{
						// we need 3 points (...)
						$diff = strlen($title) - 47;
						$firstpart = substr($title, 0, 23);
						$secondpart = substr($title, 23 + $diff);
						$title = $firstpart . "..." . $secondpart;
				}
				
				if(preg_match('/^https?\:\/\//Usi', $url))
				{
						return '<a target="_blank" href="'.$url.'">'.$title.'</a>';
				} else
				{
				
						return '<a href="'.$url.'">'.$title.'</a>';
				}
		}
		
		/**
		 * for urls
		 *@name _url_callback
		*/
		public function _url_callback($matches)
		{
				$url = $matches[1];
				$title = $matches[2];
				
				if(preg_match('/^https?\:\/\//Usi', $url))
				{
						return '<a target="_blank" href="'.$url.'">'.$title.'</a>';
				} else
				{
				
						return '<a href="'.$url.'">'.$title.'</a>';
				}
		}
}