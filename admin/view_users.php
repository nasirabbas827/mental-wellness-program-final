<?php
session_start();
include('config.php');

/* Admin protection */
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

/* Handle status update */
if (isset($_POST['update_status'])) {
    $user_id = intval($_POST['user_id']);
    $status  = $_POST['status'];

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $user_id);
    $stmt->execute();

    header("Location: view_users.php");
    exit;
}

/* Fetch users */
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>View Users</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet"
          href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container mt-4">
    <h3 class="mb-4">All Users</h3>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Created At</th>
                <th width="220">Actions</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>

                    <td>
                        <span class="badge badge-info">
                            <?= ucfirst($row['role']) ?>
                        </span>
                    </td>

                    <td>
                        <span class="badge 
                            <?= $row['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                            <?= ucfirst($row['status']) ?>
                        </span>
                    </td>

                    <td><?= $row['created_at'] ?></td>

                    <td>
                        <form method="post" class="mb-2">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">

                            <select name="status" class="form-control form-control-sm mb-2">
                                <option value="active"   <?= $row['status']=='active'?'selected':'' ?>>Active</option>
                                <option value="inactive" <?= $row['status']=='inactive'?'selected':'' ?>>Inactive</option>
                            </select>

                            <button type="submit"
                                    name="update_status"
                                    class="btn btn-primary btn-sm btn-block">
                                Update Status
                            </button>
                        </form>

                        <a href="view_user_profile.php?user_id=<?= $row['id'] ?>"
                           class="btn btn-info btn-sm btn-block">
                            View Profile
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>

        </table>
    </div>
</div>

</body>
</html>
