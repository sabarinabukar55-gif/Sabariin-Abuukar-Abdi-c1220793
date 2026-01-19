<?php
session_start();
include "config/db_connect.php";

$error = "";

/* =========================
   AUTO LOGIN IF COOKIE EXISTS
   (MUST be before any HTML output)
========================= */
if (!isset($_SESSION['user_id']) && !empty($_COOKIE['remember_token'])) {

    $rawToken  = $_COOKIE['remember_token'];
    $hashToken = hash("sha256", $rawToken);

    $stmt = mysqli_prepare($conn, "SELECT id, user_type, status FROM users WHERE remember_token=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $hashToken);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($u = mysqli_fetch_assoc($res)) {

        if (isset($u['status']) && $u['status'] !== 'active') {
            setcookie("remember_token", "", time() - 3600, "/");
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            $_SESSION['user_type'] = $u['user_type'];
            $_SESSION['last_activity'] = time();

            header("Location: " . ($u['user_type'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php"));
            exit();
        }

    } else {
        setcookie("remember_token", "", time() - 3600, "/");
    }
}

/* =========================
   LOGIN SUBMIT
========================= */
if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {

        if (isset($user['status']) && $user['status'] !== 'active') {
            $error = "Account is inactive. Contact admin.";
        } else {

            $ok = false;

            // ✅ md5 fallback upgrade
            if ($user['password'] === md5($password)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
                mysqli_stmt_bind_param($up, "si", $newHash, $user['id']);
                mysqli_stmt_execute($up);
                $ok = true;
            }

            // ✅ bcrypt verify
            if (!$ok && password_verify($password, $user['password'])) {
                $ok = true;
            }

            if ($ok) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['last_activity'] = time();

                /* ✅ Remember Me */
                if (!empty($_POST['remember_me'])) {

                    $rawToken  = bin2hex(random_bytes(32));
                    $hashToken = hash("sha256", $rawToken);

                    $st = mysqli_prepare($conn, "UPDATE users SET remember_token=? WHERE id=?");
                    mysqli_stmt_bind_param($st, "si", $hashToken, $user['id']);
                    mysqli_stmt_execute($st);

                    // 30 days cookie
                    setcookie("remember_token", $rawToken, time() + (86400 * 30), "/");

                } else {
                    // if not checked => clear db token + cookie
                    $uid = (int)$user['id'];
                    mysqli_query($conn, "UPDATE users SET remember_token=NULL WHERE id=$uid");
                    setcookie("remember_token", "", time() - 3600, "/");
                }

                header("Location: " . ($user['user_type'] === 'admin' ? "admin_dashboard.php" : "user_dashboard.php"));
                exit();
            }

            $error = "Invalid username or password";
        }

    } else {
        $error = "Invalid username or password";
    }
}

/* ✅ NOW include header AFTER cookies/redirects */
include "includes/header_public.php";
?>

<div class="auth-container">
    <h2>Login</h2>

    <form method="post" autocomplete="off">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>

        <label class="remember-row" style="display:flex;gap:10px;align-items:center;margin:10px 0;">
            <input type="checkbox" name="remember_me" value="1">
            <span>Remember me</span>
        </label>

        <button type="submit" name="login" class="btn btn-primary" style="width:100%;">Login</button>

        <p class="link"><a href="forgot_password.php">Forgot Password?</a></p>
        <p class="link">No account? <a href="register.php">Register</a></p>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </form>
</div>

<?php include "includes/footer.php"; ?>
