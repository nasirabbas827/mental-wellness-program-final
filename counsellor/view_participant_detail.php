<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counsellor') {
    header("Location: ../login.php");
    exit;
}

$counsellor_id = $_SESSION['user_id'];

/* Get participant ID from URL */
$participant_id = $_GET['id'] ?? null;
if (!$participant_id) {
    die("Participant not specified.");
}

/* Fetch participant details, ensure they are assigned to this counsellor */
$stmt = $conn->prepare("
    SELECT id, full_name, email, phone, age, gender, organization, status
    FROM users
    WHERE id=? AND role='participant' AND preferred_counsellor_id=?
");
$stmt->bind_param("ii", $participant_id, $counsellor_id);
$stmt->execute();
$result = $stmt->get_result();
$participant = $result->fetch_assoc();

if (!$participant) {
    die("Participant not found or not assigned to you.");
}

/* Fetch participant's mood check-ins */
$stmt = $conn->prepare("
    SELECT mood, notes, created_at
    FROM mood_checkins
    WHERE participant_id=?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $participant_id);
$stmt->execute();
$checkins_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Participant Detail</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    
    <h2>Participant Profile</h2>
    <div class="card mb-4">
        <div class="card-body">
            <p><strong>Full Name:</strong> <?= htmlspecialchars($participant['full_name']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($participant['email']); ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($participant['phone']); ?></p>
            <p><strong>Age:</strong> <?= htmlspecialchars($participant['age']); ?></p>
            <p><strong>Gender:</strong> <?= ucfirst($participant['gender']); ?></p>
            <p><strong>Organization:</strong> <?= htmlspecialchars($participant['organization']); ?></p>
            <p><strong>Status:</strong> <?= ucfirst($participant['status']); ?></p>
        </div>
    </div>

    <h3>Mood Check-ins</h3>
    <hr>
    <?php if ($checkins_result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>Mood</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count=1; while ($checkin = $checkins_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $count++; ?></td>
                        <td><?= date('M d, Y H:i', strtotime($checkin['created_at'])); ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $checkin['mood'])); ?></td>
                        <td><?= htmlspecialchars($checkin['notes']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No mood check-ins recorded for this participant.</p>
    <?php endif; ?>
</div>

</body>
</html>
