<?php

// XXX use functions from user.php
include_once 'db.php';
include_once 'utils.php';
include_once 'error.php';
include_once 'hash.php';
include_once 'user.php';

class Log {
    public function __construct() {
        // make sure the user did fill in username and pass
        $username = Utils::handleUsers();

        $db = new db;
        $db->request('SELECT id, username, email, status, avatar FROM users WHERE username = :name AND password = :pass');
        $db->bind(':name', $username);
        $db->bind(':pass', Hash::get(Utils::post('password')));
        $result = $db->getAssoc();

        if (!empty($result)) {
            if (!User::canLogin(Utils::stringToArray($result['status']))) {
                Error::set("This user can't login.");
                return;
            }
            Utils::setSession("username", $result['username']);
            Utils::setSession("email", $result['email']);
            // XXX why isn't PDO setting "0" to session
            Utils::setSession("user_id", $result['id'] === "0" ? "admin" : $result['id']);
            Utils::setSession("status", Utils::stringToArray($result['status']));
            Utils::setSession("avatar", $result['avatar']);

            Error::alliswell();

            Utils::setSession('is_logged_in', true);
            $db->request('UPDATE users SET lastconnect=now() WHERE username = :username');
            $db->bind(':username', Utils::getSession("username"));
            $db->doquery();
        } else {
            Error::set("wrong username or password");
        }
    }

    public static function logout() {
        $_SESSION = array();
        session_unset();
        session_destroy();

        header("Location: index.php");
        exit();
    }
}

?>

