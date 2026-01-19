<?php
session_start();
include "config/db_connect.php";
include "includes/header_public.php";

$error = "";
$success = "";
$token = $_GET['token'] ?? "";

if ($token == "") {
    $error = "Invalid reset token.";
} else {
    $stmt = mysqli_prepare($conn, "SELECT id, reset_expires FROM users WHERE reset_token=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (!$user = mysqli_fetch_assoc($res)) {
        $error = "Token not found.";
    } else {
        if (strtotime($user['reset_expires']) < time()) {
            $error = "Token expired. Please request again.";
        }
    }
}

if (isset($_POST['reset']) && $error == "") {
    $p1 = $_POST['password'];
    $p2 = $_POST['confirm_password'];

    if ($p1 !== $p2) {
        $error = "Passwords do not match.";
    } elseif (strlen($p1) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hash = password_hash($p1, PASSWORD_DEFAULT);

        $up = mysqli_prepare($conn, "UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?");
        mysqli_stmt_bind_param($up, "si", $hash, $user['id']);
        mysqli_stmt_execute($up);

        $success = "Password reset successful. You can login now.";
    }
}
?>

<div class="auth-container">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <a href="login.php" class="btn btn-primary" style="width:100%;">Go to Login</a>
        <?php include "includes/footer.php"; exit(); ?>
    <?php endif; ?>

    <?php if (!$error): ?>
        <form method="post">
            <input type="password" name="password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>

            <button type="submit" name="reset" class="btn btn-success" style="width:100%;">
                Reset Password
            </button>
        </form>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>
