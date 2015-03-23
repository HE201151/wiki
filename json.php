<?php
class Jason {
    /* config file path */
    protected static $config_file = "file.ini";
    /* json data variable */
    private $json;
    /* fields */


    /* json error messages */
    protected static $json_errors = array (
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    );

    /*
     *
     * Print last JSON error
     *
     */
    public function getJSONerror() {
            $err = static::$json_errors[json_last_error()];
            if ($err != JSON_ERROR_NONE)
                echo '!! JSON ERROR => ' . $err;
                
    }

    /*
     *
     * Load JSON from file.
     * 
     *
     */
    public function __construct() {
    	try {
    		$jfile = file_get_contents(self::$config_file);
    	} catch (Exception $i) {
    		echo 'Exception : ', $e->getMessage(), "\n";
    	}

        $this->decode($jfile);
    }

    /*
     *
     * JSON decoding from file
     *
     *
     */
    public function decode($stuff) {
        $this->json = json_decode($stuff, JSON_PRETTY_PRINT);
        return $this->json;
        $this->getJSONerror();
    }

    /*
     *
     * Pretty print JSON output 
     *
     */
    public function toString() {
        echo "<pre>";
        print_r($this->json);
        echo "</pre>";
    }

    /*
     *
     * Get field value
     *
     *
     */
    public function get($field) {
        return $json[$field];
    }

    /*
     *
     * Set field value
     *
     */
    public function set($field, $value) {
        $json[$field] = $value;
    }

    /*
     *
     * Write back JSON to config file
     *
     */
    public function writeFile($file) {
        file_put_contents($file, json_encode($json));
    }
}


$j = new Jason();
$j->toString();
?>
