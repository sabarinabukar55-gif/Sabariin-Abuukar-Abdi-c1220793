<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header_dashboard.php";

if ($_SESSION['user_type'] !== 'voter') {
    header("Location: admin_dashboard.php");
    exit();
}

// fetch candidates
$result = mysqli_query($conn, "SELECT * FROM candidates ORDER BY position, full_name");
?>

<div class="layout">

    <aside class="sidebar">
        <h3>User Panel</h3>
        <a href="user_dashboard.php">Dashboard</a>
        <a class="active" href="view_candidates.php">View Candidates</a>
        <a href="results.php">Results</a>
        <a href="profile.php">Profile</a>
        <a class="logout" href="logout.php">Logout</a>
    </aside>

    <main class="content">
        <h2 class="page-title">Candidate List</h2>

        <?php if (mysqli_num_rows($result) == 0): ?>
            <div class="info-box">
                <p>No candidates found.</p>
            </div>
        <?php else: ?>

            <div class="candidate-grid">

                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                        $photo = !empty($row['photo']) ? $row['photo'] : "default.png";
                    ?>

                    <div class="candidate-card">
                        <img src="assets/images/<?php echo htmlspecialchars($photo); ?>" alt="Candidate">

                        <div class="candidate-text">
                            <h4><?php echo htmlspecialchars($row['full_name']); ?></h4>
                            <span class="badge"><?php echo htmlspecialchars($row['position']); ?></span>
                        </div>
                    </div>

                <?php endwhile; ?>

            </div>

        <?php endif; ?>
    </main>

</div>

<?php include "includes/footer.php"; ?>
