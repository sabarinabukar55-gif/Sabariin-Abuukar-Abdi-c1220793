<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header_dashboard.php";

if ($_SESSION['user_type'] !== 'voter') {
    header("Location: admin_dashboard.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM candidates ORDER BY position, full_name");
?>

<div class="layout">

    <aside class="sidebar">
        <h3>User Panel</h3>
        <a href="user_dashboard.php">Dashboard</a>
        <a class="active" href="candidates.php">View Candidates</a>
        <a href="results.php">Results</a>
        <a href="profile.php">Profile</a>
        <a class="logout" href="logout.php">Logout</a>
    </aside>

    <main class="content">
        <h2 class="page-title">Candidate List</h2>

        <div class="candidate-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="candidate-card">
                    <img src="assets/images/<?php echo htmlspecialchars($row['photo'] ?: 'default.png'); ?>" alt="">

                    <div class="candidate-text">
                        <h4><?php echo htmlspecialchars($row['full_name']); ?></h4>
                        <span class="candidate-badge"><?php echo htmlspecialchars($row['position']); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

</div>

<?php include "includes/footer.php"; ?>
