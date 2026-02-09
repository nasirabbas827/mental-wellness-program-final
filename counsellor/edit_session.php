<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counsellor') {
    header("Location: ../login.php");
    exit;
}

$counsellor_id = $_SESSION['user_id'];
$id = intval($_GET['id']);
$msg = "";

// Fetch the session
$stmt = $conn->prepare("SELECT * FROM counselling_sessions WHERE id = ? AND counsellor_id = ?");
$stmt->bind_param("ii", $id, $counsellor_id);
$stmt->execute();
$result = $stmt->get_result();
$session = $result->fetch_assoc();

if (!$session) {
    die("Session not found or you don't have permission.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $date = $_POST['session_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $meet_link = trim($_POST['meet_link']);
    $status = $_POST['status']; // Only status editable

    $stmt = $conn->prepare("UPDATE counselling_sessions SET 
        title=?, session_date=?, start_time=?, end_time=?, meet_link=?, status=? 
        WHERE id=? AND counsellor_id=?");
    $stmt->bind_param("ssssssii", $title, $date, $start_time, $end_time, $meet_link, $status, $id, $counsellor_id);

    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Session updated successfully.</div>';
        $session['title'] = $title;
        $session['session_date'] = $date;
        $session['start_time'] = $start_time;
        $session['end_time'] = $end_time;
        $session['meet_link'] = $meet_link;
        $session['status'] = $status;
    } else {
        $msg = '<div class="alert alert-danger">Error updating session.</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Session</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h3>Edit Session</h3>
            <?= $msg ?>

            <form method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($session['title']) ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Meet Link</label>
                        <input type="text" name="meet_link" class="form-control" required value="<?= htmlspecialchars($session['meet_link']) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Date</label>
                        <input type="date" name="session_date" class="form-control" required value="<?= $session['session_date'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Start Time</label>
                        <input type="time" name="start_time" class="form-control" required value="<?= $session['start_time'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>End Time</label>
                        <input type="time" name="end_time" class="form-control" required value="<?= $session['end_time'] ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="scheduled" <?= $session['status']=='scheduled'?'selected':'' ?>>Scheduled</option>
                            <option value="completed" <?= $session['status']=='completed'?'selected':'' ?>>Completed</option>
                            <option value="archived" <?= $session['status']=='archived'?'selected':'' ?>>Archived</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Session</button>
                <a href="view_sessions.php" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
