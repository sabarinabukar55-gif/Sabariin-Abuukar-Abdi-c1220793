<?php
session_start();
include "config/db_connect.php";

if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    mysqli_query($conn, "UPDATE users SET remember_token=NULL WHERE id=$uid");
}

setcookie("remember_token", "", time() - 3600, "/");
session_unset();
session_destroy();

header("Location: login.php");
exit();
