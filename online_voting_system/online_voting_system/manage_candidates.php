<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header_dashboard.php";

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: user_dashboard.php");
    exit();
}

$error = "";
$success = "";

/* ✅ Fetch candidate for edit */
$editCandidate = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editCandidate = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM candidates WHERE id=$id LIMIT 1"));
}

/* ✅ Delete */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM candidates WHERE id=$id");
    header("Location: manage_candidates.php");
    exit();
}

/* ✅ Save candidate (Insert / Update) */
if (isset($_POST['save_candidate'])) {

    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $edit_id = !empty($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

    if ($name === "" || $position === "") {
        $error = "Please fill all fields.";
    } else {

        // ✅ get old photo when editing
        $oldPhoto = "";
        if ($edit_id > 0) {
            $oldRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT photo FROM candidates WHERE id=$edit_id LIMIT 1"));
            $oldPhoto = $oldRow['photo'] ?? "";
        }

        $photoName = $oldPhoto;

        // ✅ Upload if selected
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {

            $uploadDir = __DIR__ . "/assets/images/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $tmp = $_FILES['photo']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allow = ['jpg','jpeg','png','webp'];

            if (!in_array($ext, $allow)) {
                $error = "Only JPG, PNG, WEBP allowed.";
            } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                $error = "Max file size is 2MB.";
            } else {
                $photoName = "cand_" . time() . "_" . rand(1000,9999) . "." . $ext;

                if (!move_uploaded_file($tmp, $uploadDir . $photoName)) {
                    $error = "Photo upload failed. Check assets/images/ permission.";
                }
            }
        }

        if ($error === "") {

            if ($edit_id > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE candidates SET full_name=?, position=?, photo=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "sssi", $name, $position, $photoName, $edit_id);
                mysqli_stmt_execute($stmt);
                $success = "Candidate updated.";
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO candidates (full_name, position, photo) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sss", $name, $position, $photoName);
                mysqli_stmt_execute($stmt);
                $success = "Candidate added.";
            }

            header("Location: manage_candidates.php");
            exit();
        }
    }
}

$candidates = mysqli_query($conn, "SELECT * FROM candidates ORDER BY id DESC");
?>

<div class="layout">

    <aside class="sidebar">
        <h3>Admin Panel</h3>
        <a href="admin_dashboard.php">Dashboard</a>
        <a class="active" href="manage_candidates.php">Manage Candidates</a>
        <a href="results.php">Results</a>
        <a class="logout" href="logout.php">Logout</a>
    </aside>

    <main class="content">

        <h2 class="page-title">Manage Candidates</h2>

        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

        <!-- FORM -->
        <div class="info-box">
            <form method="post" class="candidate-form" enctype="multipart/form-data">
                <input type="hidden" name="edit_id" value="<?php echo $editCandidate['id'] ?? ''; ?>">

                <input type="text" name="name" placeholder="Candidate Name"
                       value="<?php echo htmlspecialchars($editCandidate['full_name'] ?? ''); ?>" required>

                <select name="position" required>
                    <option value="">-- Select Position --</option>
                    <?php
                    $positions = ['President', 'Vice President', 'Secretary'];
                    foreach ($positions as $pos):
                        $selected = (!empty($editCandidate) && $editCandidate['position'] === $pos) ? "selected" : "";
                    ?>
                        <option value="<?php echo $pos; ?>" <?php echo $selected; ?>>
                            <?php echo $pos; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- ✅ phone + pc -->
                <input type="file" name="photo" accept="image/*">

                <button type="submit" name="save_candidate" class="btn btn-primary">
                    <?php echo $editCandidate ? 'Update Candidate' : 'Add Candidate'; ?>
                </button>

                <?php if ($editCandidate): ?>
                    <a href="manage_candidates.php" class="btn btn-info">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- TABLE -->
        <div class="info-box">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th width="210">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($candidates)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <img src="assets/images/<?php echo htmlspecialchars($row['photo'] ?: 'default.png'); ?>"
                                 style="width:45px;height:45px;border-radius:50%;object-fit:cover;">
                        </td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['position']); ?></td>
                        <td>
                            <a class="btn btn-info" href="?edit=<?php echo $row['id']; ?>">Edit</a>
                            <a class="btn btn-danger" href="?delete=<?php echo $row['id']; ?>"
                               onclick="return confirm('Delete this candidate?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<?php include "includes/footer.php"; ?>
