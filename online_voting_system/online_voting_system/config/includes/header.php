<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header_public.php";

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit();
}

/* ✅ Correct Statistics */

// Total system users
$total_users = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];

// Total candidates
$total_candidates = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM candidates"))[0];

// ✅ Total votes = rows in votes table
$total_votes = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM votes"))[0];

// ✅ Total voters = DISTINCT users who voted
$total_voters = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(DISTINCT user_id) FROM votes"))[0];
?>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h3>Admin Panel</h3>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="manage_candidates.php">Manage Candidates</a>
        <a href="results.php">Results</a>
        <a href="logout.php">Logout</a>
    </aside>

    <!-- CONTENT -->
    <main class="content">
        <h2 class="page-title">Admin Dashboard</h2>

        <div class="stats-grid">
            <div class="stat-box blue">
                <h3><?php echo $total_users; ?></h3>
                <p>Total Users</p>
            </div>

            <div class="stat-box green">
                <h3><?php echo $total_candidates; ?></h3>
                <p>Total Candidates</p>
            </div>

            <div class="stat-box orange">
                <h3><?php echo $total_votes; ?></h3>
                <p>Total Votes</p>
            </div>

            <div class="stat-box blue" style="opacity:.9;">
                <h3><?php echo $total_voters; ?></h3>
                <p>Total Voters</p>
            </div>
        </div>

    </main>
</div>

<?php include "includes/footer.php"; ?>
