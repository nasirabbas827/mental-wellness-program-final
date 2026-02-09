<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counsellor') {
    header("Location: ../login.php");
    exit;
}

$counsellor_id = $_SESSION['user_id'];
$uploads_dir = '../uploads/';

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['resource_id'] ?? null;
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $resource_type = $_POST['resource_type'];
    $file_name = null;

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['file']['tmp_name'];
        $file_name = time() . "_" . basename($_FILES['file']['name']);
        move_uploaded_file($tmp_name, $uploads_dir . $file_name);
    }

    if ($id) {
        // Update
        if ($file_name) {
            $sql = "UPDATE wellness_resources 
                    SET title=?, content=?, resource_type=?, file=? 
                    WHERE id=? AND counsellor_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii", $title, $content, $resource_type, $file_name, $id, $counsellor_id);
        } else {
            $sql = "UPDATE wellness_resources 
                    SET title=?, content=?, resource_type=? 
                    WHERE id=? AND counsellor_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $title, $content, $resource_type, $id, $counsellor_id);
        }
        $stmt->execute();
    } else {
        // Insert
        $sql = "INSERT INTO wellness_resources (counsellor_id, title, content, resource_type, file) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $counsellor_id, $title, $content, $resource_type, $file_name);
        $stmt->execute();
    }

    header("Location: manage_resources.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $del_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM wellness_resources WHERE id=? AND counsellor_id=?");
    $stmt->bind_param("ii", $del_id, $counsellor_id);
    $stmt->execute();
    header("Location: manage_resources.php");
    exit;
}

// Fetch all resources for this counsellor
$stmt = $conn->prepare("SELECT * FROM wellness_resources WHERE counsellor_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $counsellor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Wellness Resources</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Manage Wellness Resources</h2>
    <hr>

    <!-- Button trigger modal -->
    <button type="button" class="btn btn-success mb-3" data-toggle="modal" data-target="#resourceModal" onclick="clearForm()">
        Add Resource
    </button>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>File</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count=1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $count++; ?></td>
                        <td><?= htmlspecialchars($row['title']); ?></td>
                        <td><?= ucfirst($row['resource_type']); ?></td>
                        <td>
                            <?php if ($row['file']): ?>
                                <a href="../uploads/<?= $row['file']; ?>" target="_blank">View File</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" 
                                    onclick="editResource(<?= $row['id']; ?>, '<?= htmlspecialchars(addslashes($row['title'])); ?>', 
                                    '<?= htmlspecialchars(addslashes($row['content'])); ?>', '<?= $row['resource_type']; ?>')">
                                Edit
                            </button>
                            <a href="?delete=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-muted">No resources added yet.</p>
    <?php endif; ?>
</div>

<!-- Modal Form for Add/Edit -->
<div class="modal fade" id="resourceModal" tabindex="-1" role="dialog" aria-labelledby="resourceModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="resource_id" id="resource_id">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="resourceModalLabel">Add / Edit Resource</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">

            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Content</label>
                <textarea name="content" id="content" class="form-control" rows="4" required></textarea>
            </div>

            <div class="form-group">
                <label>Resource Type</label>
                <select name="resource_type" id="resource_type" class="form-control" required>
                    <option value="">Select</option>
                    <option value="article">Article</option>
                    <option value="blog">Blog</option>
                    <option value="video">Video</option>
                    <option value="tool">Tool</option>
                </select>
            </div>

            <div class="form-group">
                <label>File (optional)</label>
                <input type="file" name="file" class="form-control-file">
            </div>

          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Save</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editResource(id, title, content, type) {
    $('#resource_id').val(id);
    $('#title').val(title);
    $('#content').val(content);
    $('#resource_type').val(type);
    $('#resourceModal').modal('show');
}

function clearForm() {
    $('#resource_id').val('');
    $('#title').val('');
    $('#content').val('');
    $('#resource_type').val('');
}
</script>
</body>
</html>
