<?php
session_start();

if (isset($_POST["submit-deconnect"])) {
    $_SESSION = [];
    session_destroy();
}

header('Location: index.php');
exit;