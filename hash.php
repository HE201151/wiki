<?php

class Hash {
	public static function get($string) {
		return hash('sha512', $string);
	}
}

?>