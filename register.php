<?php
session_start();
include "config/db_connect.php";
include "includes/header_public.php";

$error = "";

if (isset($_POST['register'])) {

    $fname     = trim($_POST['fname']);
    $lname     = trim($_POST['lname']);
    $gender    = $_POST['gender'];
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);

    // ✅ bcrypt password
    $passwordPlain = $_POST['password'];
    $passwordHash  = password_hash($passwordPlain, PASSWORD_DEFAULT);

    // ✅ check username exists
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) > 0) {
        $error = "Username already exists.";
    } else {

        // ✅ insert user
        $stmt = mysqli_prepare($conn, "
            INSERT INTO users (first_name,last_name,gender,username,password,email,phone,user_type,status)
            VALUES (?,?,?,?,?,?,?,'voter','active')
        ");
        mysqli_stmt_bind_param($stmt, "sssssss", $fname, $lname, $gender, $username, $passwordHash, $email, $phone);

        if (mysqli_stmt_execute($stmt)) {

            $newUserId = mysqli_insert_id($conn);

            // ✅ Auto login session
            session_regenerate_id(true);
            $_SESSION['user_id'] = $newUserId;
            $_SESSION['user_type'] = 'voter';
            $_SESSION['last_activity'] = time();

            /* ✅ REMEMBER ME after register (30 days)
               - This is what makes user login automatically next time
            */
            $token = bin2hex(random_bytes(32));
            $st = mysqli_prepare($conn, "UPDATE users SET remember_token=? WHERE id=?");
            mysqli_stmt_bind_param($st, "si", $token, $newUserId);
            mysqli_stmt_execute($st);

            setcookie("remember_token", $token, time() + (86400 * 30), "/");

            header("Location: user_dashboard.php");
            exit();

        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<div class="auth-container">
    <h2>Register</h2>

    <?php if (!empty($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <div class="register-grid">
            <input type="text" name="fname" placeholder="First Name" required>
            <input type="text" name="lname" placeholder="Last Name" required>

            <select name="gender" class="full" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>

            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email">

            <input type="text" name="phone" placeholder="Phone" class="full">
            <input type="password" name="password" placeholder="Password" class="full" required>
        </div>

        <button type="submit" name="register" class="btn btn-primary" style="width:100%;">
            Register
        </button>

        <p class="link">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </form>
</div>

<?php include "includes/footer.php"; ?>
