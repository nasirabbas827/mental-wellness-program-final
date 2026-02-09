<?php
session_start();
include('config.php');

// Redirect if not admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}


// ────────────────────────────────────────────────
// Handle search/filter
// ────────────────────────────────────────────────
$keyword    = trim($_GET['keyword']    ?? '');
$start_date = trim($_GET['start_date'] ?? '');
$end_date   = trim($_GET['end_date']   ?? '');

$where = [];
$params = [];
$types  = '';

if ($keyword !== '') {
    $where[] = "(cs.title LIKE ? OR wr.title LIKE ? OR wr.content LIKE ?)";
    $like = "%$keyword%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}

if ($start_date !== '' && $end_date !== '') {
    $where[] = "cs.session_date BETWEEN ? AND ?";
    $params[] = $start_date; $params[] = $end_date;
    $types .= 'ss';
}

$where_sql = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

// ────────────────────────────────────────────────
// Main Query - Aggregating Trends (Requirement 7)
// ────────────────────────────────────────────────
$sql = "
    SELECT 
        cs.id AS session_id, cs.title AS session_title, cs.session_date, cs.start_time, cs.end_time, cs.meet_link, cs.status,
        u.full_name AS counsellor_name,
        wr.id AS resource_id, wr.title AS resource_title, wr.resource_type, wr.content, wr.file,
        (SELECT ROUND(AVG(CASE mc.mood
                WHEN 'very_low'   THEN 1 WHEN 'low' THEN 2 WHEN 'neutral' THEN 3
                WHEN 'good' THEN 4 WHEN 'excellent' THEN 5 END), 1)
         FROM mood_checkins mc WHERE mc.counsellor_id = cs.counsellor_id
        ) AS counsellor_avg_mood
    FROM counselling_sessions cs
    INNER JOIN users u ON u.id = cs.counsellor_id
    LEFT JOIN wellness_resources wr ON wr.counsellor_id = cs.counsellor_id
    $where_sql
    ORDER BY cs.session_date DESC, cs.start_time ASC
";

$stmt = $conn->prepare($sql);
if ($types !== '') { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness Hub | Programs & Resources</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        :root { --primary: #6c5ce7; --secondary: #a29bfe; --bg: #f9f9fb; }
        .main-header { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; padding: 40px 0; margin-bottom: 30px; border-radius: 0 0 30px 30px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: 0.3s; margin-bottom: 20px; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .btn-link { font-weight: 600; color: #2d3436; text-decoration: none !important; }
        .trend-badge { background: #e1f5fe; color: #0288d1; font-weight: bold; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; }
        .resource-section { background: #fdfdfd; border-left: 4px solid var(--primary); padding: 15px; margin-top: 15px; border-radius: 0 10px 10px 0; }
        .share-btn { border-radius: 50px; padding: 5px 15px; font-size: 0.8rem; transition: 0.2s; }
        .meet-btn { background: #00b894; color: white; border-radius: 50px; font-weight: bold; }
        .meet-btn:hover { background: #00a082; color: white; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>


<div class="container mt-4 mb-4">
    <div class="card mb-5 p-3">
        <form method="get" class="form-row align-items-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span></div>
                    <input type="text" name="keyword" class="form-control border-left-0" placeholder="Search keywords..." value="<?= htmlspecialchars($keyword) ?>">
                </div>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <div class="col-md-2 d-flex">
                <button type="submit" class="btn btn-primary btn-block font-weight-bold">Filter</button>
                <a href="view_programs.php" class="btn btn-light ml-2 text-muted" title="Reset"><i class="fa fa-undo"></i></a>
            </div>
        </form>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="accordion" id="programsAccordion">
            <?php 
            $prev_session_id = null;
            while ($row = $result->fetch_assoc()): 
                if ($row['session_id'] !== $prev_session_id):
                    if ($prev_session_id !== null) echo '</div></div></div>'; 
            ?>
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3" id="heading<?= $row['session_id'] ?>">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <button class="btn btn-link text-left p-0" type="button" data-toggle="collapse" data-target="#collapse<?= $row['session_id'] ?>">
                            <span class="h5 mb-0 text-dark"><?= htmlspecialchars($row['session_title']) ?></span>
                            <div class="text-muted small mt-1">
                                <i class="far fa-calendar-alt mr-1"></i> <?= date('M d, Y', strtotime($row['session_date'])) ?> 
                                <i class="far fa-clock ml-2 mr-1"></i> <?= date('h:i A', strtotime($row['start_time'])) ?>
                            </div>
                        </button>
                        <div class="mt-2 mt-md-0">
                            <span class="trend-badge mr-2">
                                <i class="fa fa-chart-line mr-1"></i> Mood Trend: <?= $row['counsellor_avg_mood'] ? $row['counsellor_avg_mood'] : 'N/A' ?>
                            </span>
                            <span class="badge badge-pill badge-<?= $row['status']==='completed'?'success':($row['status']==='archived'?'secondary':'primary') ?> px-3 py-2">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div id="collapse<?= $row['session_id'] ?>" class="collapse <?= $prev_session_id === null ? 'show' : '' ?>" data-parent="#programsAccordion">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <h6 class="text-muted text-uppercase small font-weight-bold">Counsellor</h6>
                                <p class="mb-3 font-weight-bold"><i class="fa fa-user-md mr-2 text-primary"></i><?= htmlspecialchars($row['counsellor_name']) ?></p>
                            </div>
                            <div class="col-md-6 px-md-4">
                                <h6 class="text-muted text-uppercase small font-weight-bold">Virtual Access</h6>
                                <?php if ($row['meet_link']): ?>
                                    <a href="<?= htmlspecialchars($row['meet_link']) ?>" target="_blank" class="btn btn-sm meet-btn px-4"><i class="fa fa-video mr-2"></i>Enter Session</a>
                                <?php else: ?>
                                    <span class="text-muted italic">Link not generated</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php $prev_session_id = $row['session_id']; endif; ?>

                        <?php if ($row['resource_id']): ?>
                            <div class="resource-section">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6><i class="fa fa-book-open mr-2 text-primary"></i><?= htmlspecialchars($row['resource_title']) ?> <small class="text-muted">(<?= ucfirst($row['resource_type']) ?>)</small></h6>
                                </div>
                                <p class="text-secondary"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                                
                                <div class="d-flex align-items-center flex-wrap">
                                    <?php if ($row['file']): ?>
                                        <a href="../uploads/<?= htmlspecialchars($row['file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary share-btn mr-3 mb-2"><i class="fa fa-download mr-1"></i> Download</a>
                                    <?php endif; ?>

                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'participant'): 
                                        $share_url = urlencode('http://' . $_SERVER['HTTP_HOST'] . '/uploads/' . $row['file']);
                                        $share_text = urlencode('Found this wellness resource helpful: ' . $row['resource_title']);
                                    ?>
                                        <div class="mb-2">
                                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $share_url ?>" target="_blank" class="btn btn-sm btn-outline-primary share-btn mr-1"><i class="fab fa-facebook-f"></i></a>
                                            <a href="https://twitter.com/intent/tweet?text=<?= $share_text ?>&url=<?= $share_url ?>" target="_blank" class="btn btn-sm btn-outline-info share-btn mr-1"><i class="fab fa-twitter"></i></a>
                                            <a href="https://wa.me/?text=<?= $share_text . ' ' . $share_url ?>" target="_blank" class="btn btn-sm btn-outline-success share-btn"><i class="fab fa-whatsapp"></i></a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

            <?php endwhile; 
            if ($prev_session_id !== null) echo '</div></div></div>'; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" style="width: 100px; opacity: 0.5;">
            <h4 class="text-muted mt-3">No programs found matching your filters.</h4>
        </div>
    <?php endif; ?>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>