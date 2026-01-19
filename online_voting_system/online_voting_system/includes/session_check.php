<?php
if (session_status() === PHP_SESSION_NONE) session_start();

include __DIR__ . "/../config/db_connect.php";

/* =========================
   AUTO LOGIN BY COOKIE
========================= */
if (!isset($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {

    $token = $_COOKIE['remember_token'];

    $stmt = mysqli_prepare($conn, "SELECT id, user_type, status FROM users WHERE remember_token=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($u = mysqli_fetch_assoc($res)) {

        if (isset($u['status']) && $u['status'] !== 'active') {
            // inactive => clear cookie
            setcookie("remember_token", "", time() - 3600, "/");
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            $_SESSION['user_type'] = $u['user_type'];
            $_SESSION['last_activity'] = time();
        }

    } else {
        // invalid token => clear cookie
        setcookie("remember_token", "", time() - 3600, "/");
    }
}

/* =========================
   CHECK LOGIN
========================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   SESSION TIMEOUT
========================= */
$timeout = 3600; // 1 hour (change if you want)

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php?expired=1");
    exit();
}

$_SESSION['last_activity'] = time();
