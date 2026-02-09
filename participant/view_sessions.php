<?php
session_start();
include('../config.php');

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle Search (Requirement 9)
$search = "";
$where_clause = "WHERE cs.approval_status='approved' AND cs.status='scheduled'";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clause .= " AND (cs.title LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.specialization LIKE '%$search%')";
}

// Fetch Sessions (Requirement 7)
$query = "
    SELECT cs.*, u.full_name as counsellor_name, u.specialization 
    FROM counselling_sessions cs
    JOIN users u ON cs.counsellor_id = u.id
    $where_clause
    ORDER BY cs.session_date ASC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wellness Programs | Participant</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        .page-header { background: #fff; padding: 30px 0; border-bottom: 1px solid #eef0f2; margin-bottom: 30px; }
        .search-bar { border-radius: 50px; padding: 25px 20px; border: 2px solid #eef0f2; transition: 0.3s; }
        .search-bar:focus { border-color: #00b894; box-shadow: none; }
        
        .session-card {
            border: none; border-radius: 20px; transition: 0.3s;
            background: white; overflow: hidden; height: 100%;
        }
        .session-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .session-date-tag {
            background: #e8f4fd; color: #0984e3;
            padding: 5px 15px; border-radius: 50px; font-weight: 700; font-size: 0.8rem;
        }
        .counsellor-info { border-top: 1px solid #f1f3f5; padding-top: 15px; margin-top: 15px; }
        .btn-join { border-radius: 50px; font-weight: 600; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="page-header shadow-sm">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="font-weight-bold mb-0">Wellness Programs</h2>
                <p class="text-muted mb-0">Explore and join upcoming virtual sessions.</p>
            </div>
            <div class="col-md-6">
                <form action="" method="GET" class="mt-3 mt-md-0">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control search-bar" placeholder="Search by topic or counsellor..." value="<?= htmlspecialchars($search) ?>">
                        <div class="input-group-append">
                            <button class="btn btn-success px-4" style="border-radius: 0 50px 50px 0;" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($session = mysqli_fetch_assoc($result)): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="session-card shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="session-date-tag">
                                    <i class="far fa-calendar-alt mr-1"></i> <?= date('M d, Y', strtotime($session['session_date'])) ?>
                                </span>
                                <span class="badge badge-success px-3 py-2" style="border-radius: 50px;">Live Link</span>
                            </div>
                            
                            <h5 class="font-weight-bold mb-2"><?= htmlspecialchars($session['title']) ?></h5>
                            <p class="text-muted small">
                                <i class="far fa-clock mr-1"></i> <?= date('h:i A', strtotime($session['start_time'])) ?> - <?= date('h:i A', strtotime($session['end_time'])) ?>
                            </p>

                            <div class="counsellor-info d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($session['counsellor_name']) ?>&background=random" class="rounded-circle mr-3" width="40">
                                <div>
                                    <h6 class="mb-0 small font-weight-bold"><?= htmlspecialchars($session['counsellor_name']) ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($session['specialization']) ?></small>
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="request_session.php?session_id=<?= $session['id'] ?>" class="btn btn-primary btn-block btn-join">
                                    Join Program
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-4x text-light mb-3"></i>
                <h4 class="text-muted">No programs found matching your criteria.</h4>
                <a href="view_programs.php" class="btn btn-link">View all programs</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>