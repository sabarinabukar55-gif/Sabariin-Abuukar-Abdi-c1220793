<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header_dashboard.php";

$user_type = $_SESSION['user_type'] ?? 'voter';

// fetch results
$sql = "SELECT c.id, c.full_name, c.position, c.photo,
        COUNT(v.id) AS total_votes
        FROM candidates c
        LEFT JOIN votes v ON v.candidate_id = c.id
        GROUP BY c.id
        ORDER BY c.position, total_votes DESC, c.full_name";

$res = mysqli_query($conn, $sql);
?>

<div class="layout">
    <aside class="sidebar">
        <h3><?php echo $user_type === 'admin' ? "Admin Panel" : "User Panel"; ?></h3>

        <?php if ($user_type === 'admin'): ?>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_candidates.php">Manage Candidates</a>
            <a href="results.php" class="active">Results</a>
        <?php else: ?>
            <a href="user_dashboard.php">Dashboard</a>
            <a href="vote.php">Vote Now</a>
            <a href="results.php" class="active">Results</a>
            <a href="profile.php">Profile</a>
        <?php endif; ?>

        <a href="logout.php">Logout</a>
    </aside>

    <main class="content">
        <h2 class="page-title">Voting Results</h2>

        <div class="info-box">
            <table>
                <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Position</th>
                    <th>Votes</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td style="display:flex; align-items:center; gap:10px;">
                            <img
                                src="assets/images/<?php echo htmlspecialchars($row['photo'] ?: 'default.png'); ?>"
                                style="width:40px;height:40px;border-radius:50%;object-fit:cover;"
                                alt=""
                            >
                            <?php echo htmlspecialchars($row['full_name']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                        <td><b><?php echo (int)$row['total_votes']; ?></b></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include "includes/footer.php"; ?>
