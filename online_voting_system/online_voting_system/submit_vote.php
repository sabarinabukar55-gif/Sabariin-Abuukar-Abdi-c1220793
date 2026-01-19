<?php
include "includes/session_check.php";
include "config/db_connect.php";

$user = $_SESSION['user_id'];
$candidate = $_POST['candidate'];

mysqli_query($conn,
    "INSERT INTO votes (user_id, candidate_id) 
     VALUES ($user, $candidate)"
);

header("Location: user_dashboard.php");
