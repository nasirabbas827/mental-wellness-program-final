<?php
session_start();
include('config.php');

// Redirect if not admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Handle status update request
if (isset($_POST['update_session'])) {
    $session_id = $_POST['session_id'];
    $approval_status = $_POST['approval_status'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE counselling_sessions SET approval_status = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $approval_status, $status, $session_id);
    $stmt->execute();

    $msg = "<div class='alert alert-success'>Session updated successfully.</div>";
}

// Handle search
$search = "";
$where_clause = "";
if (!empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $where_clause = "WHERE cs.title LIKE ? OR u.full_name LIKE ? OR cs.session_date LIKE ?";
}

// Fetch sessions with counsellor names
$sql = "SELECT cs.*, u.full_name AS counsellor_name 
        FROM counselling_sessions cs
        JOIN users u ON cs.counsellor_id = u.id
        $where_clause
        ORDER BY cs.session_date DESC";

if (!empty($where_clause)) {
    $stmt = $conn->prepare($sql);
    $like_search = "%$search%";
    $stmt->bind_param("sss", $like_search, $like_search, $like_search);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Counselling Sessions</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include('admin_navbar.php'); ?>

<div class="container mt-5">
    <h2>All Counselling Sessions</h2>

    <?php if(isset($msg)) echo $msg; ?>

    <form method="get" class="form-inline mb-3">
        <input type="text" name="search" class="form-control mr-2" placeholder="Search by title, counsellor, or date" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="view_sessions.php" class="btn btn-secondary ml-2">Reset</a>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Title</th>
                    <th>Counsellor</th>
                    <th>Date</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Meet Link</th>
                    <th>Approval Status</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= htmlspecialchars($row['counsellor_name']) ?></td>
                    <td><?= $row['session_date'] ?></td>
                    <td><?= $row['start_time'] ?></td>
                    <td><?= $row['end_time'] ?></td>
                    <td><a href="<?= $row['meet_link'] ?>" target="_blank">Link</a></td>
                    <td><?= ucfirst($row['approval_status']) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <form method="post" class="form-inline">
                            <input type="hidden" name="session_id" value="<?= $row['id'] ?>">

                            <select name="approval_status" class="form-control mb-1 mr-1">
                                <option value="approved" <?= $row['approval_status']=='approved'?'selected':'' ?>>Approved</option>
                                <option value="disapproved" <?= $row['approval_status']=='disapproved'?'selected':'' ?>>Disapproved</option>
                                <option value="pending" <?= $row['approval_status']=='pending'?'selected':'' ?>>Pending</option>
                            </select>

                            <select name="status" class="form-control mb-1 mr-1">
                                <option value="scheduled" <?= $row['status']=='scheduled'?'selected':'' ?>>Scheduled</option>
                                <option value="completed" <?= $row['status']=='completed'?'selected':'' ?>>Completed</option>
                                <option value="archived" <?= $row['status']=='archived'?'selected':'' ?>>Archived</option>
                            </select>

                            <button type="submit" name="update_session" class="btn btn-sm btn-primary">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
