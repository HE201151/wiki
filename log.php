<?php

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
        $db->request('SELECT id, username, mail, status FROM users WHERE username = :name AND password = :pass');
        $db->bind(':name', $username);
        $db->bind(':pass', Hash::get(Utils::post('password')));
        $result = $db->getAssoc();

        if (!empty($result)) {
            if ($result['status'] == UserStatus::Registered) {
                Error::set("This user has not yet activated, please click the link in the email sent after registration.");
                return;
            }
            $_SESSION["username"] = $result['username'];
            $_SESSION["mail"] = $result['mail'];
            $_SESSION["user_id"] = $result['id'];
            $_SESSION["status"] = $result['status'];
            Error::alliswell();

            $_SESSION['is_logged_in'] = TRUE;
            $db->request('UPDATE users SET lastconnect=now() WHERE username = :username');
            $db->bind(':username', $_SESSION["username"]);
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

