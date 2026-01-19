<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header.php";

$user_id = $_SESSION['user_id'];

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id")
);

$voters = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"));
$candidates = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM candidates"));
$votes = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM votes"));
?>

<div class="main-container">
    <h2 class="page-title">Dashboard</h2>

    <div class="info-box">
        <p><strong>Welcome:</strong> <?php echo $user['first_name']." ".$user['last_name']; ?></p>
        <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
        <p><strong>User Type:</strong> <?php echo ucfirst($user['user_type']); ?></p>
    </div>

    <div class="stats">
        <div class="stat-card">
            <h3><?php echo $voters[0]; ?></h3>
            <p>Total Users</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $candidates[0]; ?></h3>
            <p>Total Candidates</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $votes[0]; ?></h3>
            <p>Total Votes</p>
        </div>
    </div>

    <div class="info-box" style="text-align:center;">
        <a href="vote.php" class="btn btn-primary">Vote</a>
        <a href="results.php" class="btn btn-success">Results</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<?php include "includes/footer.php"; ?>
