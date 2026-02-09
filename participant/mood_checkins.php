<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: ../login.php");
    exit;
}

$participant_id = $_SESSION['user_id'];

/* Fetch participant's assigned counsellor */
$stmt = $conn->prepare("SELECT preferred_counsellor_id FROM users WHERE id=?");
$stmt->bind_param("i", $participant_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$counsellor_id = $user['preferred_counsellor_id'] ?? null;

if (!$counsellor_id) {
    die("No counsellor assigned. Please select a counsellor first.");
}

/* Handle Add/Edit submission */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['checkin_id'] ?? null; // If editing
    $mood = $_POST['mood'] ?? null;
    $notes = trim($_POST['notes']) ?? null;

    if ($mood) {
        if ($id) {
            // Edit
            $stmt = $conn->prepare("UPDATE mood_checkins SET mood=?, notes=? WHERE id=? AND participant_id=?");
            $stmt->bind_param("ssss", $mood, $notes, $id, $participant_id);
        } else {
            // Add new
            $stmt = $conn->prepare("INSERT INTO mood_checkins (participant_id, counsellor_id, mood, notes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $participant_id, $counsellor_id, $mood, $notes);
        }
        $stmt->execute();
        header("Location: mood_checkins.php");
        exit;
    }
}

/* Fetch all mood check-ins */
$stmt = $conn->prepare("SELECT * FROM mood_checkins WHERE participant_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $participant_id);
$stmt->execute();
$checkins_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Mood Check-ins</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-5">
    <h2>My Mood Check-ins</h2>
    <hr>

    <button class="btn btn-success mb-3" data-toggle="modal" data-target="#checkinModal" id="addCheckinBtn">Add Mood Check-in</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Mood</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($checkins_result->num_rows > 0): ?>
                <?php while ($checkin = $checkins_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('M d, Y H:i', strtotime($checkin['created_at'])) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $checkin['mood'])) ?></td>
                        <td><?= htmlspecialchars($checkin['notes']) ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm editBtn" 
                                    data-id="<?= $checkin['id'] ?>" 
                                    data-mood="<?= $checkin['mood'] ?>" 
                                    data-notes="<?= htmlspecialchars($checkin['notes']) ?>"
                                    data-toggle="modal" data-target="#checkinModal">
                                Edit
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No mood check-ins yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Form -->
<div class="modal fade" id="checkinModal" tabindex="-1" role="dialog" aria-labelledby="checkinModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="post" id="checkinForm">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="checkinModalLabel">Add Mood Check-in</h5>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
                <input type="hidden" name="checkin_id" id="checkin_id" value="">
                <div class="form-group">
                    <label for="mood">Mood</label>
                    <select name="mood" id="mood" class="form-control" required>
                        <option value="">Select Mood</option>
                        <option value="very_low">Very Low</option>
                        <option value="low">Low</option>
                        <option value="neutral">Neutral</option>
                        <option value="good">Good</option>
                        <option value="excellent">Excellent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="4"></textarea>
                </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Save</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          </div>
        </div>
    </form>
  </div>
</div>

<script>
$(document).ready(function(){
    // When Add button clicked
    $('#addCheckinBtn').click(function(){
        $('#checkinModalLabel').text('Add Mood Check-in');
        $('#checkin_id').val('');
        $('#mood').val('');
        $('#notes').val('');
    });

    // When Edit button clicked
    $('.editBtn').click(function(){
        $('#checkinModalLabel').text('Edit Mood Check-in');
        $('#checkin_id').val($(this).data('id'));
        $('#mood').val($(this).data('mood'));
        $('#notes').val($(this).data('notes'));
    });
});
</script>

</body>
</html>
