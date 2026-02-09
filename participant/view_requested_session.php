<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: ../login.php");
    exit;
}

$participant_id = $_SESSION['user_id'];

/* Fetch participant's session requests */
$stmt = $conn->prepare("
    SELECT sr.id, u.full_name AS counsellor_name, sr.preferred_date, sr.preferred_time, sr.reason, sr.status, sr.created_at
    FROM session_requests sr
    JOIN users u ON sr.counsellor_id = u.id
    WHERE sr.participant_id=?
    ORDER BY sr.created_at DESC
");
$stmt->bind_param("i", $participant_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Session Requests</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>My Session Requests</h2>
    <hr>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Counsellor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count=1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $count++; ?></td>
                        <td><?= htmlspecialchars($row['counsellor_name']); ?></td>
                        <td><?= htmlspecialchars($row['preferred_date']); ?></td>
                        <td><?= htmlspecialchars($row['preferred_time']); ?></td>
                        <td><?= htmlspecialchars($row['reason']); ?></td>
                        <td><?= ucfirst($row['status']); ?></td>
                        <td><?= date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">You have not requested any sessions yet.</p>
    <?php endif; ?>
</div>
</body>
</html>
