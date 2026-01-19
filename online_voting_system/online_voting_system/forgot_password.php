<?php
session_start();
include "config/db_connect.php";
include "includes/header_public.php";

$error = "";
$success = "";
$debugLink = "";

if (isset($_POST['send'])) {

    $email = trim($_POST['email']);

    $stmt = mysqli_prepare($conn, "SELECT id,email FROM users WHERE email=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {

        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600);

        $up = mysqli_prepare($conn, "UPDATE users SET reset_token=?, reset_expires=? WHERE id=?");
        mysqli_stmt_bind_param($up, "ssi", $token, $expires, $user['id']);
        mysqli_stmt_execute($up);

        $resetLink = "http://localhost/online_voting_system/reset_password.php?token=" . $token;

        $success = "Reset link generated (localhost mode).";
        $debugLink = $resetLink;

    } else {
        $error = "Email not found.";
    }
}
?>

<div class="auth-container">
    <h2>Forgot Password</h2>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>

        <?php if ($debugLink): ?>
            <p style="margin-top:10px; font-size:14px;">
                <b>Reset Link:</b><br>
                <a href="<?php echo htmlspecialchars($debugLink); ?>">
                    <?php echo htmlspecialchars($debugLink); ?>
                </a>
            </p>
        <?php endif; ?>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <input type="email" name="email" placeholder="Enter your email" required>

        <button type="submit" name="send" class="btn btn-primary" style="width:100%;">
            Send Reset Link
        </button>

        <p class="link"><a href="login.php">Back to Login</a></p>
    </form>
</div>

<?php include "includes/footer.php"; ?>
