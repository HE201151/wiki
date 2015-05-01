<?php

include_once 'error.php';

class Jason {
    /* config file path */
    const config_file = "config.ini";
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
     * Load JSON from file.
     * 
     *
     */
    public function __construct() {
    	try {
    		$jfile = file_get_contents(self::config_file);
    	} catch (Exception $i) {
    		Error::exception($i);
    	}

        $this->decode($jfile);
    }

    public function showConfig() {
        print '
        <form id="register" action="index.php?page=admin&action=saveSettings" method="post">
            <table border="0" cellspacing="0" cellpadding="6" class="tborder">
            <tbody>
                <tr>
                    <td id="regtitle">Website Settings</td>
                </tr>
                <tr id="formcontent">
                    <td>
                        <table cellpadding="6" cellspacing="0" width=100%>
                            <tbody>
                            <tr>
                                <td id="tcat">key</td>
                                <td id="tcat">value</td>';

                                foreach ($this->json as $key => $value) {
                                    print  '<tr>
                                                <td class="trow">' . $key . '</td>
                                                <td class="trow">
                                                    <input type="text" name="' . $key . '" id=config_' . $key . ' value="' . $value . '" />
                                                </td>
                                            </tr>';
                                }
                            print '</tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
            </table>
            <div align="center" id="submit">
                    <input id="submit_button" type="submit" value="Save Settings" />
            </div>
        </form>';
    }

    /*
     *
     * Print last JSON error
     *
     */
    public function getJSONerror() {
            $err = static::$json_errors[json_last_error()];
            if ($err != JSON_ERROR_NONE)
                Error::set($err);                
    }

    /*
     *
     * JSON decoding from file
     *
     *
     */
    public function decode($stuff) {
        $this->json = json_decode($stuff, JSON_PRETTY_PRINT);
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
        return $this->json[$field];
    }

    public static function getOnce($field) {
        $config = new Jason;
        return $config->get($field);
    }

    /*
     *
     * Set field value
     *
     */
    public function set($field, $value) {
        $this->json[$field] = $value;
    }

    /*
     *
     * Write back JSON to config file
     *
     */
    public function writeFile() {
        file_put_contents(self::config_file, json_encode($this->json)); 
    }

}
?>
