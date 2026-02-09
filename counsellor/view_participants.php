<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counsellor') {
    header("Location: ../login.php");
    exit;
}

$counsellor_id = $_SESSION['user_id'];

/* Fetch participants assigned to this counsellor */
$stmt = $conn->prepare("
    SELECT id, full_name, email, phone, age, gender, organization, status
    FROM users
    WHERE role='participant' AND preferred_counsellor_id=?
    ORDER BY full_name ASC
");
$stmt->bind_param("i", $counsellor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Participants</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>My Participants</h2>
    <hr>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Organization</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count=1; while ($participant = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $count++; ?></td>
                        <td><?= htmlspecialchars($participant['full_name']); ?></td>
                        <td><?= htmlspecialchars($participant['email']); ?></td>
                        <td><?= htmlspecialchars($participant['phone']); ?></td>
                        <td><?= htmlspecialchars($participant['age']); ?></td>
                        <td><?= ucfirst($participant['gender']); ?></td>
                        <td><?= htmlspecialchars($participant['organization']); ?></td>
                        <td>
                            <a href="view_participant_detail.php?id=<?= $participant['id']; ?>" class="btn btn-sm btn-info">Profile & Mood Checkins</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No participants assigned to you yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
