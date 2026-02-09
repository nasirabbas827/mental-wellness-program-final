<?php
session_start();
include('config.php');


/* Handle search/filter */
$filters = [];
$params = [];
$types = "";

$search_specialization = $_GET['specialization'] ?? "";
$search_qualification  = $_GET['qualification'] ?? "";
$search_experience     = $_GET['experience_years'] ?? "";

// Build query dynamically
$query = "SELECT id, full_name, specialization, qualification, experience_years, bio, phone
          FROM users
          WHERE role='counsellor' AND status='active' ";

if ($search_specialization) {
    $query .= " AND specialization LIKE ? ";
    $types .= "s";
    $params[] = "%$search_specialization%";
}
if ($search_qualification) {
    $query .= " AND qualification LIKE ? ";
    $types .= "s";
    $params[] = "%$search_qualification%";
}
if ($search_experience) {
    $query .= " AND experience_years >= ? ";
    $types .= "i";
    $params[] = $search_experience;
}

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Counsellors</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>Find Counsellors</h2>
    <hr>

    <!-- Search Form -->
    <form method="get" class="form-inline mb-4">
        <input type="text" name="specialization" class="form-control mr-2" placeholder="Specialization"
               value="<?= htmlspecialchars($search_specialization); ?>">
        <input type="text" name="qualification" class="form-control mr-2" placeholder="Qualification"
               value="<?= htmlspecialchars($search_qualification); ?>">
        <input type="number" name="experience_years" class="form-control mr-2" placeholder="Min Experience (Years)"
               value="<?= htmlspecialchars($search_experience); ?>">
        <button class="btn btn-primary">Search</button>
    </form>

    <?php if ($result->num_rows > 0): ?>
        <div class="row">
            <?php while ($counsellor = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($counsellor['full_name']); ?></h5>
                            <p><strong>Specialization:</strong> <?= htmlspecialchars($counsellor['specialization']); ?></p>
                            <p><strong>Qualification:</strong> <?= htmlspecialchars($counsellor['qualification']); ?></p>
                            <p><strong>Experience:</strong> <?= $counsellor['experience_years']; ?> years</p>
                            <p><?= htmlspecialchars(substr($counsellor['bio'], 0, 80)); ?>...</p>
                            <button class="btn btn-success btn-sm" data-toggle="modal"
                                    data-target="#scheduleModal<?= $counsellor['id']; ?>">
                                Schedule Session
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Schedule Session Modal -->
                <div class="modal fade" id="scheduleModal<?= $counsellor['id']; ?>" tabindex="-1" role="dialog">
                  <div class="modal-dialog" role="document">
                    <form method="post" action="login.php">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Schedule Session with <?= htmlspecialchars($counsellor['full_name']); ?></h5>
                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="counsellor_id" value="<?= $counsellor['id']; ?>">
                            <div class="form-group">
                                <label>Preferred Date</label>
                                <input type="date" name="preferred_date" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Preferred Time</label>
                                <input type="time" name="preferred_time" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Reason / Notes</label>
                                <textarea name="reason" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-primary">Send Request</button>
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">No counsellors found matching your criteria.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
