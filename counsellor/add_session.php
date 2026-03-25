<?php
session_start();
include('../config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counsellor') {
    header("Location: ../login.php");
    exit;
}

$counsellor_id = $_SESSION['user_id'];
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $date = $_POST['session_date']; 
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $meet_link = trim($_POST['meet_link']);

    $stmt = $conn->prepare("INSERT INTO counselling_sessions 
        (counsellor_id, title, session_date, start_time, end_time, meet_link, approval_status) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isssss", $counsellor_id, $title, $date, $start_time, $end_time, $meet_link);

    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">Session added successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger">Error adding session.</div>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Session</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h3>Add New Session</h3>
    <?= $msg ?>

    <form method="post">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label>Meet Link</label>
                <input type="text" name="meet_link" class="form-control" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Date</label>
                <input type="date" name="session_date" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
                <label>Start Time</label>
                <input type="time" name="start_time" class="form-control" required>
            </div>
            <div class="form-group col-md-4">
                <label>End Time</label>
                <input type="time" name="end_time" class="form-control" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Add Session</button>
        <a href="view_sessions.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</div>
</div>
</body>
</html>


