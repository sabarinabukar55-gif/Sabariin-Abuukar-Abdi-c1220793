<?php
include "includes/session_check.php";
include "config/db_connect.php";
include "includes/header_dashboard.php";

$user_id = (int)$_SESSION['user_id'];

$error = "";
$success = "";

/* ✅ Load user */
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id LIMIT 1"));
if (!$user) {
    die("User not found.");
}

$img = !empty($user['profile_picture']) ? $user['profile_picture'] : "default.png";

/* ✅ Update text info */
if (isset($_POST['save_profile'])) {
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $stmt = mysqli_prepare($conn, "UPDATE users SET first_name=?, last_name=?, email=?, phone=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssi", $fname, $lname, $email, $phone, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $success = "Profile updated.";
        // reload user
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id LIMIT 1"));
        $img = !empty($user['profile_picture']) ? $user['profile_picture'] : "default.png";
    } else {
        $error = "Update failed.";
    }
}

/* ✅ Upload photo */
if (isset($_POST['upload_photo'])) {

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please choose a photo.";
    } else {

        // ✅ correct folder (same as your site)
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

            $filename = "user_" . $user_id . "_" . time() . "." . $ext;

            if (move_uploaded_file($tmp, $uploadDir . $filename)) {

                $stmt = mysqli_prepare($conn, "UPDATE users SET profile_picture=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "si", $filename, $user_id);
                mysqli_stmt_execute($stmt);

                $success = "Photo uploaded.";
                $img = $filename;

            } else {
                $error = "Could not save file. Check folder permission: assets/images/";
            }
        }
    }
}
?>

<div class="layout">

    <aside class="sidebar">
        <h3>User Panel</h3>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="vote.php">Vote Now</a>
        <a href="results.php">Results</a>
        <a class="active" href="profile.php">Profile</a>
        <a class="logout" href="logout.php">Logout</a>
    </aside>

    <main class="content">
        <h2 class="page-title">Profile</h2>

        <?php if ($error): ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

        <div class="info-box" style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
            <img src="assets/images/<?php echo htmlspecialchars($img); ?>"
                 style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:2px solid #eee;">

            <form method="post" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <!-- ✅ phone + PC -->
                <input type="file" name="photo" accept="image/*" required>
                <button type="submit" name="upload_photo" class="btn btn-primary">Upload Photo</button>
            </form>
        </div>

        <div class="info-box">
            <form method="post" class="candidate-form">
                <input type="text" name="first_name" placeholder="First name"
                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

                <input type="text" name="last_name" placeholder="Last name"
                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

                <input type="email" name="email" placeholder="Email"
                       value="<?php echo htmlspecialchars($user['email']); ?>">

                <input type="text" name="phone" placeholder="Phone"
                       value="<?php echo htmlspecialchars($user['phone']); ?>">

                <button type="submit" name="save_profile" class="btn btn-success">Save Changes</button>
            </form>
        </div>

    </main>
</div>

<?php include "includes/footer.php"; ?>
