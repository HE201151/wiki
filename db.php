<?php

include_once 'jason.php';
include_once 'session.php';

class db extends PDO {
	protected static $tUser;
	
	protected $database;
	protected $engine;
	protected $host;
	protected $user;
	protected $pw;

	protected $req;
	protected $sth;

	public function __construct() {
		$config_file = new Jason;
		$this->database = $config_file->get('db');
		$this->engine = $config_file->get('db_engine');
		$this->host = $config_file->get('db_host');
		$this->user = $config_file->get('db_user');
		$this->pw = $config_file->get('db_pw');

        $dsn = $this->engine.':host='.$this->host.';dbname='.$this->database; 
        try {
			parent::__construct($dsn, $this->user, $this->pw);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
    		Error::exception($e);
		}
	}

	public function request($r) { 
		$this->req = $r;
		$this->sth = $this->prepare($this->req);
	}

	public function bind($var, $value) {
        switch (true) {
            case is_int($value):
                $type = PDO::PARAM_INT;
                break;
            case is_bool($value):
                $type = PDO::PARAM_BOOL;
                break;
            case is_null($value):
                $type = PDO::PARAM_NULL;
                break;
            default:
                $type = PDO::PARAM_STR;
        }
		$this->sth->bindParam($var, $value, $type);
	}
	
	public function exec() {
		$this->sth->execute();
	}

	public function getAssoc() {
		$this->sth->execute();
		return $this->sth->fetch(PDO::FETCH_ASSOC);
	}

	public function toString() {
		return $this->database;
	}
}
?>