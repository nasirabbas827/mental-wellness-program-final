<?php
session_start();
include('config.php');

/* Admin check */
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

/* Get user ID from GET */
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    die("User ID not provided.");
}

$user_id = intval($_GET['user_id']);

/* Fetch user data */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <h3 class="mb-4">User Profile: <?= htmlspecialchars($user['full_name']) ?></h3>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>Full Name:</strong>
                    <p><?= htmlspecialchars($user['full_name']) ?></p>
                </div>
                <div class="col-md-6">
                    <strong>Email:</strong>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <strong>Phone:</strong>
                    <p><?= htmlspecialchars($user['phone']) ?></p>
                </div>
                <div class="col-md-6">
                    <strong>Age:</strong>
                    <p><?= htmlspecialchars($user['age']) ?></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <strong>Gender:</strong>
                    <p><?= ucfirst($user['gender']) ?></p>
                </div>
                <div class="col-md-6">
                    <strong>Role:</strong>
                    <p><?= ucfirst($user['role']) ?></p>
                </div>
            </div>

            <?php if ($user['role'] === 'participant'): ?>
            <div class="row">
                <div class="col-md-6">
                    <strong>Organization:</strong>
                    <p><?= htmlspecialchars($user['organization']) ?></p>
                </div>
                <div class="col-md-6">
                    <strong>Preferred Counsellor ID:</strong>
                    <p><?= $user['preferred_counsellor_id'] ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($user['role'] === 'counsellor'): ?>
            <div class="row">
                <div class="col-md-6">
                    <strong>Specialization:</strong>
                    <p><?= htmlspecialchars($user['specialization']) ?></p>
                </div>
                <div class="col-md-6">
                    <strong>Qualification:</strong>
                    <p><?= htmlspecialchars($user['qualification']) ?></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <strong>Experience (Years):</strong>
                    <p><?= $user['experience_years'] ?></p>
                </div>
                <div class="col-md-6">
                    <strong>Bio:</strong>
                    <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <strong>Status:</strong>
                    <p><?= ucfirst($user['status']) ?></p>
                </div>
                <div class="col-md-6">
                    <strong>Created At:</strong>
                    <p><?= $user['created_at'] ?></p>
                </div>
            </div>

            <a href="view_users.php" class="btn btn-secondary mt-3">Back to Users</a>
        </div>
    </div>
</div>

</body>
</html>
