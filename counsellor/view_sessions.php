<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counsellor') {
    header("Location: ../login.php");
    exit;
}

$counsellor_id = $_SESSION['user_id'];
$msg = "";

// Handle deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt_del = $conn->prepare("DELETE FROM counselling_sessions WHERE id = ? AND counsellor_id = ?");
    $stmt_del->bind_param("ii", $delete_id, $counsellor_id);
    if ($stmt_del->execute()) {
        $msg = '<div class="alert alert-success">Session deleted successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error deleting session.</div>';
    }
    $stmt_del->close();
}

// Fetch sessions
$stmt = $conn->prepare("SELECT * FROM counselling_sessions WHERE counsellor_id = ? ORDER BY session_date DESC");
$stmt->bind_param("i", $counsellor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Sessions</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h3>My Sessions</h3>

    <?= $msg ?>

    <a href="add_session.php" class="btn btn-success mb-3">Add New Session</a>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Meet Link</th>
                    <th>Approval</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= $row['session_date'] ?></td>
                    <td><?= $row['start_time'] ?></td>
                    <td><?= $row['end_time'] ?></td>
                    <td><a href="<?= $row['meet_link'] ?>" target="_blank">Link</a></td>
                    <td><?= ucfirst($row['approval_status']) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <a href="edit_session.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary mb-1">Edit</a>
                        <a href="view_sessions.php?delete_id=<?= $row['id'] ?>" 
                           class="btn btn-sm btn-danger mb-1" 
                           onclick="return confirm('Are you sure to delete this session?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
