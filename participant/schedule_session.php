<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $participant_id = $_SESSION['user_id'];
    $counsellor_id = $_POST['counsellor_id'];
    $preferred_date = $_POST['preferred_date'];
    $preferred_time = $_POST['preferred_time'];
    $reason = $_POST['reason'];

    $stmt = $conn->prepare("
        INSERT INTO session_requests (participant_id, counsellor_id, preferred_date, preferred_time, reason)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisss", $participant_id, $counsellor_id, $preferred_date, $preferred_time, $reason);
    $stmt->execute();

    header("Location: view_counsellors.php?requested=1");
    exit;
}
