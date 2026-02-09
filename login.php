<?php
include('config.php');
session_start();

$email = $password = "";
$email_err = $password_err = $login_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter password";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($email_err) && empty($password_err)) {

        $sql = "SELECT id, full_name, email, password, role, status 
                FROM users 
                WHERE email = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if ($user['status'] !== 'active') {
                $login_msg = '<div class="alert alert-warning text-center">
                                Account is inactive.
                              </div>';
            } elseif (password_verify($password, $user['password'])) {

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['role']      = $user['role'];

                if ($user['role'] === 'participant') {
                    header("Location: participant/dashboard.php");
                } elseif ($user['role'] === 'counsellor') {
                    header("Location: counsellor/dashboard.php");
                } else {
                    $login_msg = '<div class="alert alert-danger text-center">
                                    Invalid role
                                  </div>';
                }
                exit;

            } else {
                $password_err = "Invalid password";
            }

        } else {
            $email_err = "Email not found";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto shadow-sm" style="max-width: 500px;">
        <div class="card-body">

            <h3 class="text-center mb-4">Login</h3>

            <?= $login_msg ?>

            <form method="post">

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email"
                        class="form-control <?= !empty($email_err) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($email) ?>">
                    <div class="invalid-feedback"><?= $email_err ?></div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password"
                        class="form-control <?= !empty($password_err) ? 'is-invalid' : '' ?>">
                    <div class="invalid-feedback"><?= $password_err ?></div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4">Login</button>
                </div>

            </form>

            <p class="text-center mt-3">
                Don't have an account? <a href="register.php">Register</a>
            </p>

        </div>
    </div>
</div>

</body>
</html>
