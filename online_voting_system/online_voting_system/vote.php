<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header_dashboard.php";

if ($_SESSION['user_type'] !== 'voter') {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Already voted? (one vote total)
$checkVote = mysqli_query($conn, "SELECT id FROM votes WHERE user_id=$user_id LIMIT 1");
if (mysqli_num_rows($checkVote) > 0) {
    header("Location: user_dashboard.php");
    exit();
}

$error = "";
$success = "";

// Submit vote (ONE candidate only)
if (isset($_POST['submit_vote'])) {

    if (empty($_POST['candidate_id'])) {
        $error = "Please select one candidate.";
    } else {
        $candidate_id = (int)$_POST['candidate_id'];

        // check candidate exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM candidates WHERE id=? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $candidate_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($res) !== 1) {
            $error = "Invalid candidate selected.";
        } else {

            // insert vote (one per user)
            $ins = mysqli_prepare($conn, "INSERT INTO votes (user_id, candidate_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins, "ii", $user_id, $candidate_id);

            if (mysqli_stmt_execute($ins)) {
                $success = "✅ Vote submitted successfully!";
            } else {
                // if UNIQUE(user_id) exists, this may trigger if double submit
                $error = "You already voted or vote failed.";
            }
        }
    }
}

// list candidates (all)
$result = mysqli_query($conn, "SELECT * FROM candidates ORDER BY position, full_name");
?>

<div class="layout">
    <aside class="sidebar">
        <h3>User Panel</h3>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="vote.php" class="active">Vote Now</a>
        <a href="results.php">Results</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </aside>

    <main class="content">
        <h2 class="page-title">Vote Now</h2>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
            <a class="btn btn-success" href="results.php">View Results</a>
            <?php include "includes/footer.php"; exit(); ?>
        <?php endif; ?>

        <form method="post">
            <div class="candidate-list">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <label class="candidate vote-card">
                        <input
                            type="radio"
                            name="candidate_id"
                            value="<?php echo (int)$row['id']; ?>"
                            class="vote-radio"
                            required
                        >
                        <img src="assets/images/<?php echo htmlspecialchars($row['photo'] ?: 'default.png'); ?>" alt="">
                        <div class="candidate-info">
                            <h4><?php echo htmlspecialchars($row['full_name']); ?></h4>
                            <p><?php echo htmlspecialchars($row['position']); ?></p>
                        </div>
                    </label>
                <?php endwhile; ?>
            </div>

            <button type="submit" name="submit_vote" class="btn btn-success" style="width:100%; padding:16px;">
                Submit Vote
            </button>
        </form>
    </main>
</div>

<?php include "includes/footer.php"; ?>
