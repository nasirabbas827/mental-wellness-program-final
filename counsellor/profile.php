<?php
session_start();
include('config.php');


if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* Fetch user data */
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found");
}

/* Handle update */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $age = !empty($_POST['age']) ? $_POST['age'] : null;
    $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;

    // Participant
    $organization = $_POST['organization'] ?? null;

    // Counsellor
    $specialization = $_POST['specialization'] ?? null;
    $qualification  = $_POST['qualification'] ?? null;
    $experience     = $_POST['experience_years'] ?? null;
    $bio            = $_POST['bio'] ?? null;

    if (!empty($_POST['password'])) {
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $sql = "UPDATE users SET 
                    full_name=?, phone=?, age=?, gender=?, organization=?,
                    specialization=?, qualification=?, experience_years=?, bio=?, password=?
                WHERE id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssissssissi",
            $full_name,
            $phone,
            $age,
            $gender,
            $organization,
            $specialization,
            $qualification,
            $experience,
            $bio,
            $hashed_password,
            $user_id
        );
    } else {

        $sql = "UPDATE users SET 
                    full_name=?, phone=?, age=?, gender=?, organization=?,
                    specialization=?, qualification=?, experience_years=?, bio=?
                WHERE id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssissssisi",
            $full_name,
            $phone,
            $age,
            $gender,
            $organization,
            $specialization,
            $qualification,
            $experience,
            $bio,
            $user_id
        );
    }

    $stmt->execute();
    header("Location: profile.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-4">
        <div class="card mx-auto" style="max-width: 800px;">
        <div class="card-body">
    <h3 class="mb-4">Update Profile</h3>

    <form method="post">

        <!-- Row 1 -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Full Name</label>
                <input type="text" class="form-control" name="full_name"
                       value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>

            <div class="form-group col-md-6">
                <label>Email</label>
                <input type="email" class="form-control"
                       value="<?= htmlspecialchars($user['email']) ?>" readonly>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Phone</label>
                <input type="text" class="form-control" name="phone"
                       value="<?= htmlspecialchars($user['phone']) ?>">
            </div>

            <div class="form-group col-md-3">
                <label>Age</label>
                <input type="number" class="form-control" name="age"
                       value="<?= htmlspecialchars($user['age']) ?>">
            </div>

            <div class="form-group col-md-3">
                <label>Gender</label>
                <select name="gender" class="form-control">
                    <option value="">Select</option>
                    <option value="male"   <?= $user['gender']=='male'?'selected':'' ?>>Male</option>
                    <option value="female" <?= $user['gender']=='female'?'selected':'' ?>>Female</option>
                    <option value="other"  <?= $user['gender']=='other'?'selected':'' ?>>Other</option>
                </select>
            </div>
        </div>

        <!-- Password -->
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>New Password (optional)</label>
                <input type="password" class="form-control" name="password">
            </div>
        </div>

        <!-- Participant -->
        <?php if ($user['role'] === 'participant'): ?>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Organization</label>
                <input type="text" class="form-control" name="organization"
                       value="<?= htmlspecialchars($user['organization']) ?>">
            </div>
        </div>
        <?php endif; ?>

        <!-- Counsellor -->
        <?php if ($user['role'] === 'counsellor'): ?>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Specialization</label>
                <input type="text" class="form-control" name="specialization"
                       value="<?= htmlspecialchars($user['specialization']) ?>">
            </div>

            <div class="form-group col-md-6">
                <label>Qualification</label>
                <input type="text" class="form-control" name="qualification"
                       value="<?= htmlspecialchars($user['qualification']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Experience (Years)</label>
                <input type="number" class="form-control" name="experience_years"
                       value="<?= htmlspecialchars($user['experience_years']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Bio</label>
            <textarea class="form-control" name="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Update Profile</button>

    </form>
</div>
</div>
</div>

</body>
</html>
