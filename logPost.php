<?php

include_once 'log.php';

if (!empty($_POST)) {
    $log = new Log;
    header("Location: index.php");
}

?>