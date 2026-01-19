<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header_dashboard.php";

if ($_SESSION['user_type'] !== 'voter') {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id")
);

$hasVoted = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM votes WHERE user_id = $user_id")
);
?>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h3>User Panel</h3>

        <a href="user_dashboard.php" class="active">Dashboard</a>
        <a href="view_candidates.php">View Candidates</a>
        <a href="results.php">Results</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php" class="logout">Logout</a>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="content">

        <h2 class="page-title">User Dashboard</h2>

        <div class="info-box">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Role:</strong> Voter</p>
        </div>

        <div class="info-box">

            <?php if ($hasVoted): ?>
                <p style="color:#198754; font-weight:bold;">✔ You have already voted.</p>
                <a href="results.php" class="btn btn-success">View Results</a>
            <?php else: ?>
                <p style="color:#dc3545; font-weight:bold;">✖ You have not voted yet.</p>
                <a href="vote.php" class="btn btn-primary">Vote Now</a>
            <?php endif; ?>

        </div>

    </main>

</div>

<?php include "includes/footer.php"; ?>
