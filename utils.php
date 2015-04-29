<?php	

include_once 'jason.php';

// access session variables
session_name(Jason::getOnce('session_name'));
session_start();

class Utils {
	public static function isLoggedIn() {
		return (self::getSession('is_logged_in'));
	}

	public static function isPost($field) {
		return (isset($_POST[$field]) && !empty($_POST[$field]));
	}

	public static function isGet($field) {
		return (isset($_GET[$field]) && !empty($_GET[$field]));
	}

	public static function isSession($field) {
		return (isset($_SESSION[$field]) && !empty($_SESSION[$field]));
	}

	public static function post($field) {
		return $_POST[$field];
	}

	public static function get($field) {
		return $_GET[$field];
	}

	public static function getSession($field) {
		if (self::isSession($field))
			return $_SESSION[$field];
	}

	public static function setSession($field, $value) {
		$_SESSION[$field] = $value;
	}

	public static function handleUsers() {
		if (self::isPost('username')) {
			if (self::isPost('password'))
				return $_POST['username'];
			else
				return 'no password set';
		} else {
			return 'no info';
		}
	}

	public static function page() {
		if (self::isGet('page')) {
			return self::get('page');
		}
	}

	public static function goBack() {
		print '<script type="text/javascript">'
						. 'history.go(-1);'
						. '</script>';
	}

	public static function imageImport($filename, $width, $height, $imagePath) {
		/* get image info */
		$imageInfo = getimagesize($filename);
      	
      	$curWidth = $imageInfo[0];
      	$curHeight = $imageInfo[1];
      	$imageType = $imageInfo[2];

      	// XXX set accepted extensions in config ?
      	/* load image */
      	if ($imageType == IMAGETYPE_JPEG) {
        	$image = imagecreatefromjpeg($filename);
      	} else if ($imageType == IMAGETYPE_GIF) {
        	$image = imagecreatefromgif($filename);
      	} else if ($imageType == IMAGETYPE_PNG) {
      		$image = imagecreatefrompng($filename);
      	} else {
      		throw new Exception("Unsupported image format");
      	}

      	/* resize image if needed */
      	if ($width < $curWidth || $height < $curHeight) {
      		$newImage = imagecreatetruecolor($width, $height);
      		if (!imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $curWidth, $curHeight)) {
      			throw new Exception("Failed to resize image");
      		}
     	} else {
     		$newImage = $image;
     	}

      	/* overwrite current image with resized image */
      	if ($imageType == IMAGETYPE_JPEG) {
        	imagejpeg($newImage, $imagePath);
      	} else if ($imageType == IMAGETYPE_GIF) {
        	imagegif($newImage, $imagePath);
      	} else if($imageType == IMAGETYPE_PNG) {
        	imagepng($newImage, $imagePath);
      	}
	}

	public static function in_array_any($keys, $array) {
		return !!array_intersect($keys, $array);
	}

	public static function arrayToString($array) {
		if (count($array) > 1) {
			return implode(', ', $array);
		} else {
			return $array[0];
		}
	}

	public static function stringToArray($string) {
		if (strpos($string, ',') !== false) {
			return array_map('trim', array_filter(explode(', ', $string)));
		} else {
			return array($string);
		}
	}
}
?>
