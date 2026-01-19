<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header_dashboard.php";

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit();
}

// total users
$r1 = mysqli_query($conn, "SELECT COUNT(*) c FROM users");
$totalUsers = (int)mysqli_fetch_assoc($r1)['c'];

// total candidates
$r2 = mysqli_query($conn, "SELECT COUNT(*) c FROM candidates");
$totalCandidates = (int)mysqli_fetch_assoc($r2)['c'];

// total votes (rows in votes table)
$r3 = mysqli_query($conn, "SELECT COUNT(*) c FROM votes");
$totalVotes = (int)mysqli_fetch_assoc($r3)['c'];

// total voters (unique users who voted)
$r4 = mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) c FROM votes");
$totalVoters = (int)mysqli_fetch_assoc($r4)['c'];
?>

<div class="layout">
    <aside class="sidebar">
        <h3>Admin Panel</h3>
        <a class="active" href="admin_dashboard.php">Dashboard</a>
        <a href="manage_candidates.php">Manage Candidates</a>
        <a href="results.php">Results</a>
        <a class="logout" href="logout.php">Logout</a>
    </aside>

    <main class="content">
        <h2 class="page-title">Admin Dashboard</h2>

        <div class="stats-grid">
            <div class="stat-box blue">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-box green">
                <h3><?php echo $totalCandidates; ?></h3>
                <p>Total Candidates</p>
            </div>
            <div class="stat-box orange">
                <h3><?php echo $totalVotes; ?></h3>
                <p>Total Votes</p>
            </div>
            <div class="stat-box blue">
                <h3><?php echo $totalVoters; ?></h3>
                <p>Total Voters</p>
            </div>
        </div>
    </main>
</div>

<?php include "includes/footer.php"; ?>
