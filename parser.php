<?php

include_once 'wiki.php';

class Parser {
	const tags = [
		'div' => [
			'code' => '/\[\s*d{1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<div>$1</div>',
			'cont' => false,
		],
		'bold' => [
			'code' => '/\[\s*b{1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<b>$1</b>',
			'cont' => false,
		],
		'h1' => [
			'code' => '/\[\s*1{1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<h1>$1</h1>',
			'cont' => false,
		],
		'h2' => [
			'code' => '/\[\s*2{1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<h2>$1</h2>',
			'cont' => false,
		],
		'h3' => [
			'code' => '/\[\s*3{1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<h3>$1</h3>',
			'cont' => false,
		],
		'p' => [
			'code' => '/\[\s*p{1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<p>$1</p>',
			'cont' => false,
		],
		'i' => [
			'code' => '/\[\s*i{1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<i>$1</i>',
			'cont' => false,
		],
		'u' => [
			'code' => '/\[\s*u{1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<u>$1</u>',
			'cont' => false,
		],
		'newline' => [
			'code' => '/\[\s*n{1}\s*\]/',
			'html' => '<br>',
			'cont' => false,
		],
		'hr' => [
			'code' => '/\[\s*h{1}\s*\]/',
			'html' => '<hr>',
			'cont' => false,
		],
		'br' => [
			'code' => '/\[\s*(br){1}\s*\]/',
			'html' => '<br>',
			'cont' => false,
		],
		'a' => [
			'code' => '/\[\s*a{1}\s*\|\s*(.*?)\|\s*(.*?)\]/',
			'html' => '<a href="$1">$2</a>',
			'cont' => false,
		],
		'img' => [
			'code' => '/\[\s*img{1}\s*\|\s*(.*?)\s*\|\s*(.*?)\s*\]/',
			'html' => '<img src="$1" alt="$2">',
			'cont' => false,
		],
		'ul' => [
			'code' => '/\[\s*ul{1}\s*\|\s*(.*)\s*\]/',
			'html' => '<ul>$1</ul>',
			'cont' => true,
		],
		'ol' => [
			'code' => '/\[\s*(ol){1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<ol>$2</ol>',
			'cont' => true,
		],
		'ol2' => [
			'code' => '/\[\s*(ol_){1}\s*\|\s*(.*?)\s*\|\s*(.*?)\s*\|\s*(.*?)\s*\]/',
			'html' => '<ol type="$2" start="$3">$4</ol>',
			'cont' => true,
		],
		'spancolor' => [
			'code' => '/\[\s*(#[A-f0-9]{6}|#[A-f0-9]{3})\s*\|\s*(.*)\s*\]/',
			'html' => '<span style="color: $1;">$2</span>',
			'cont' => false,
		],
		'spanbg' => [
			'code' => '/\[\s*bg{1}\s*\|\s*(#[A-f0-9]{6}|#[A-f0-9]{3})\s*\|\s*(.*)\s*\]/',
			'html' => '<span style="background: $1;">$2</span>',
			'cont' => false,
		],
		'divbg' => [
			'code' => '/\[\s*div{1}\s*\|\s*(#[A-f0-9]{6}|#[A-f0-9]{3})\s*\|\s*(.*?)\s*\]/',
			'html' => '<div style="background: $1;">$2</div>',
			'cont' => false,
		],
		'divbgimg' => [
			'code' => '/\[\s*div{1}\s*\|\s*(.*?)\s*\|\s*(.*)\s*\]/',
			'html' => '<div style="background: url($1);">$2</div>',
			'cont' => false,
		],
		's' => [ // ??
			'code' => '/\[\s*s{1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<span>$1</span>',
			'cont' => false,
		],
		't' => [
			'code' => '/\[\s*[t]{1}\s*\|\s*(.*?)\s*\|\s*(.*?)\s*\]/',
			'html' => '<table border="$1">$2</table>',
			'cont' => false,
		],
		'th' => [
			'code' => '/\[\s*(th){1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<the>$2</the>',
			'cont' => false,
		],
		'ti' => [
			'code' => '/\[\s*(ti){1}\s*\|\s*(.*?)\s*\]/',
			'html' => '<ti>$2</ti>',
			'cont' => false,
		],
	];

	public static function do_li($elements) {
		$string = "";
		foreach ($elements as $li) {
			// embedded ul's
			if (preg_match('/\s*<ul>\s*(.*?)\s*<\/ul>\s*/', $li, $ultags) > 0) {
				if (!preg_match('/\s*<li>\s*/', $ultags[0])) {
					$li = preg_replace('/\s*<ul>\s*(.*?)\s*<\/ul>\s*/', '<ul>$1</ul>', $li);
					// ul tags
					$count = preg_match_all('/<ul>(.*)<\/ul>/', $li, $matches);
					if ($count > 0) {
						$li = self::dolist($li, $matches[1][0], 'ul');
					}
				}
				$string .= $li;
			// embedded ol's
			} else if (preg_match('/\s*<ol>\s*(.*?)\s*<\/ol>\s*/', $li)) {
				$li = preg_replace('/\s*<ol>\s*(.*?)\s*<\/ol>\s*/', '<ol><li>$1</li></ol>', $li);
				$string .= $li;
			} else {
				$string .= '<li>' . $li . '</li>';
			}
		}
		return $string;
	}

	public static function do_th($list) {
		$string = "";
		foreach ($list as $th) {
			$string .= '<th>' . $th . '</th>';
		}
		return $string;
	}

	public static function do_td($list) {
		$string = '';
		foreach ($list as $td) {
			$string .= '<td>' . $td . '</td>';
		}
		return $string;
	}

	public static function do_tr($list) {
		$string = '';
		foreach ($list as $tr) {
			$string .= '<tr>' . $tr . '</tr>';
		}
		return $string;
	}

	public static function dolist($in, $str, $tag) {
		$list = explode('|', $str);
		if ($tag === 'ol' || $tag == 'ul') {
			$listitems = self::do_li($list);
		} else if ($tag === 'th') {
			$listitems = self::do_th($list);
		} else if ($tag === 'td') {
			$listitems = self::do_td($list);
		} else if ($tag === 'tr') {
			$listitems = self::do_tr($list);
		}
		$in = str_replace($str, $listitems, $in);
		return $in;
	}

	public static function getBrackets($str, &$out) {
		$length = strlen($str);
		$stack  = array();
		$result = array();

		for ($i = 0; $i < $length; $i++) {
			$open = -1;
			if ($str[$i] === '[') {
				if ($i > 0) {
				 	if ($str[$i - 1] !== '\\') {
				 		$stack[] = $i;
				 	}
				} else if ($i === 0) {
					$stack[] = $i;
				}
			}

			if ($str[$i] === ']') {
				if ($i > 0) {
			 		if ($str[$i - 1] !== '\\') {
						$open = array_pop($stack);
				 	}
				}
				if ($open !== -1) {
					$result[] = substr($str, $open, $i - $open + 1);
				}
				
			}
		}
		$out = $result;
  		return count($result);
	}

	public static function get($in) {
		// first match words so our brackets searcher doesn't get confused
		$count = preg_match_all('/\[\[\s*(.*?)\s*\]\]/', $in, $matches);
		if ($count > 0) {
			foreach ($matches[0] as $string) {
				$oldstring = $string;
				$string = preg_replace('/(\[|\])/', '', $string);
				if (Wiki::findWord($string)) {
					$in = str_replace($oldstring, '<a href="index.php?page=wiki&keyword='.$string.'">'.$string.'</a>', $in);
				} else {
					$in = str_replace($oldstring, '<a id="wordnotfound" href="index.php?page=wiki&keyword='.$string.'&action=new">'.$string.'</a>', $in);
				}
			}
		}

		// then do comments, we don't want to recursively match stuff from comments
		$count = preg_match_all('/\[\s*\!{1}\s*\|\s*(.*?)\s*\]/', $in, $matches);
		if ($count > 0) {
			foreach ($matches[0] as $string) {
				$in = preg_replace('/\[\s*\!{1}\s*\|\s*(.*?)\s*\]/', '<!-- $1 -->', $in);
			}
		}

		// other tags
		$bcount = self::getBrackets($in, $out);
		for ($x = 0; $x < $bcount; $x++) {
			if ($bcount > 1) {
				self::getBrackets($in, $out);
			}
			$old = $new = reset($out);
			foreach (self::tags as $name => $parser) {
			 	$new = preg_replace($parser['code'], $parser['html'], $new);
			}
			$in = str_replace($old, $new, $in);
		}

		// ul tags
		$count = preg_match_all('/<ul>(.*)<\/ul>/', $in, $matches);
		if ($count > 0) {
			foreach ($matches[1] as $string) {
				$in = self::dolist($in, $string, 'ul');
			}
		}

		// ol tags
		$count = preg_match_all('/<ol>(.*)<\/ol>/', $in, $matches);
		if ($count > 0) {
			foreach ($matches[1] as $string) {
				$in = self::dolist($in, $string, 'ol');
				$innercount = preg_match('/<ol>(.*?)<\/ol>/', $string, $innermatches);
				if ($innercount > 0) {
					$in = self::dolist($in, '<ol>' . $innermatches[1], 'ol');
				}
			}
		}

		// ol_ tags
		$count = preg_match_all('/<ol.*?\">(.*)<\/ol>/', $in, $matches);
		if ($count > 0) {
			foreach ($matches[1] as $string) {
				$in = self::dolist($in, $string, 'ol');
			}
		}

		// th tags
		$count = preg_match_all('/<the>\s*(.*?)\s*<\/the>/', $in, $matches);
		if ($count > 0) {
			foreach ($matches[1] as $string) {
				$in = self::dolist($in, $string, 'th');
			}
			$in = preg_replace('/<the>/', '<tr>', $in);
			$in = preg_replace('/<\/the>/', '</tr>', $in);
		}

		// tr tags
		$count = preg_match_all('/<ti>\s*(.*?)\s*<\/ti>/', $in, $matches);
		if ($count > 0) {
			foreach ($matches[0] as $string) {
				$in = self::dolist($in, $string, 'td');
			}
			$in = preg_replace('/<[\/]{0,1}ti>/', '', $in);
		}

		// td tags
		$count = preg_match_all('/<table.*?>\s*.*?\s*<\/tr>\s*(.*?)\s*<\/table>/', $in, $matches);
		if ($count > 0) {
			foreach ($matches[1] as $string) {
				$in = self::dolist($in, $string, 'tr');
			}
		}

		// replace non escaped ^ with space.
		$in = preg_replace("/(?<!\\\)[\^]/", " ", $in);

		// remove stray [, ]
		$in = preg_replace("/(?<!\\\)[\[\]]/", "", $in);

		// remove simple backslashes
		$in = stripslashes($in);
		return $in;
	}
}

// invented table markup :
//$tabletest = '[t|2|[th|t1|t2|t3]|[ti|one|two|three]|[ti|four|five|six]|[ti|seven|eight|nine]]';
//$deltest = '[p|Et encore du [b|gras [u|souligné]][br]et du[#F00|rouge]]';

// $uloltest = '[div|	http://www.webweaver.nu/clipart/img/web/backgrounds/halloween/ghosts.gif |	
// [#ff0|[ol_|a|0|[ol|a1|[#fff|b2]]|[ul|1|2|3]|z]]
// ]';
// print Parser::get($uloltest);
?>
