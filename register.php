<?php
include('config.php');

$errors = [];
$success = "";

$full_name = $email = $phone = $password = $role = $gender = $age = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $password  = $_POST['password'];
    $role      = $_POST['role'];
    $gender    = $_POST['gender'];
    $age       = $_POST['age'];

    if ($full_name == "") $errors[] = "Full name is required";
    if ($email == "") $errors[] = "Email is required";
    if ($password == "") $errors[] = "Password is required";
    if ($role == "") $errors[] = "Role is required";

    // Check email uniqueness
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    $check->close();

    if (empty($errors)) {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status = 'active';

        $sql = "INSERT INTO users 
            (full_name, email, password, phone, role, status, age, gender)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssis",
            $full_name, $email, $hashed_password, $phone,
            $role, $status, $age, $gender
        );

        if ($stmt->execute()) {
            $success = "Registration successful";
        } else {
            $errors[] = "Registration failed";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 800px;">
        <div class="card-body">

            <h3 class="text-center mb-4">User Registration</h3>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success text-center"><?= $success ?></div>
            <?php endif; ?>

            <form method="post">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="">Select</option>
                            <option value="participant">Participant</option>
                            <option value="counsellor">Counsellor</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Age</label>
                        <input type="number" name="age" class="form-control">
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-5">
                        Register
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>
