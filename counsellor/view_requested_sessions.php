<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counsellor') {
    header("Location: ../login.php");
    exit;
}

$counsellor_id = $_SESSION['user_id'];

/* Handle status update */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['status'])) {
    $request_id = $_POST['request_id'];
    $status     = $_POST['status'];

    $stmt = $conn->prepare("UPDATE session_requests SET status=? WHERE id=? AND counsellor_id=?");
    $stmt->bind_param("sii", $status, $request_id, $counsellor_id);
    $stmt->execute();

    header("Location: view_requested_sessions.php");
    exit;
}

/* Fetch all session requests assigned to this counsellor */
$stmt = $conn->prepare("
    SELECT sr.id, u.full_name AS participant_name, sr.preferred_date, sr.preferred_time, sr.reason, sr.status, sr.created_at
    FROM session_requests sr
    JOIN users u ON sr.participant_id = u.id
    WHERE sr.counsellor_id=?
    ORDER BY sr.created_at DESC
");
$stmt->bind_param("i", $counsellor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Session Requests</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Session Requests</h2>
    <hr>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Participant</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count=1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $count++; ?></td>
                        <td><?= htmlspecialchars($row['participant_name']); ?></td>
                        <td><?= htmlspecialchars($row['preferred_date']); ?></td>
                        <td><?= htmlspecialchars($row['preferred_time']); ?></td>
                        <td><?= htmlspecialchars($row['reason']); ?></td>
                        <td><?= ucfirst($row['status']); ?></td>
                        <td><?= date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                        <td>
                            <form method="post" class="form-inline">
                                <input type="hidden" name="request_id" value="<?= $row['id']; ?>">
                                <select name="status" class="form-control form-control-sm mr-2">
                                    <option value="pending"  <?= $row['status']=='pending'?'selected':'' ?>>Pending</option>
                                    <option value="approved" <?= $row['status']=='approved'?'selected':'' ?>>Approved</option>
                                    <option value="rejected" <?= $row['status']=='rejected'?'selected':'' ?>>Rejected</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No session requests found.</p>
    <?php endif; ?>
</div>

</body>
</html>
