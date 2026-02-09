<?php
session_start();
include('../config.php');

// Security Check (Requirement 1 & 2)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'counsellor') {
    header("Location: ../login.php");
    exit;
}

$counsellor_id = $_SESSION['user_id'];

/** 1. DATA FETCHING **/

// Get count of assigned participants
$participants_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE preferred_counsellor_id = $counsellor_id"))['count'];

// Get pending session requests (Requirement 5)
$pending_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM session_requests WHERE counsellor_id = $counsellor_id AND status='pending'"))['count'];

// Get total wellness resources uploaded (Requirement 4)
$resources_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM wellness_resources WHERE counsellor_id = $counsellor_id"))['count'];

// Fetch Recent Mood Check-ins from assigned participants (Requirement 4 & 5)
$mood_query = "
    SELECT u.full_name, mc.mood, mc.created_at, mc.notes 
    FROM mood_checkins mc 
    JOIN users u ON mc.participant_id = u.id 
    WHERE mc.counsellor_id = $counsellor_id 
    ORDER BY mc.created_at DESC LIMIT 5
";
$recent_moods = mysqli_query($conn, $mood_query);

// Fetch upcoming sessions created by this counsellor
$sessions_query = "SELECT * FROM counselling_sessions WHERE counsellor_id = $counsellor_id AND status='scheduled' ORDER BY session_date ASC LIMIT 3";
$upcoming_sessions = mysqli_query($conn, $sessions_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counsellor Dashboard | Wellness Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        :root { --primary: #00b894; --secondary: #00cec9; --dark: #2d3436; }
        
        /* Modern Sidebar/Header vibe */
        .welcome-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; padding: 40px; border-radius: 0 0 50px 50px; margin-bottom: 30px;
        }

        .stat-card {
            border: none; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: 0.3s; background: white;
        }
        .stat-card:hover { transform: translateY(-5px); }
        
        .mood-badge { padding: 5px 12px; border-radius: 50px; font-size: 0.8rem; font-weight: bold; }
        .mood-excellent { background: #d1fae5; color: #059669; }
        .mood-good { background: #e0f2fe; color: #0284c7; }
        .mood-neutral { background: #f3f4f6; color: #4b5563; }
        .mood-low { background: #fff7ed; color: #d97706; }
        .mood-very_low { background: #fee2e2; color: #dc2626; }

        .btn-action { border-radius: 50px; font-weight: 600; padding: 10px 20px; transition: 0.3s; }
        .card-title-icon { width: 40px; height: 40px; background: #f0f2f5; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="welcome-section shadow">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h1 class="font-weight-bold text-white">Hello, Dr. <?= explode(' ', $_SESSION['full_name'])[0]; ?>!</h1>
                <p class="lead">Your support makes a difference. You have <strong><?= $pending_requests ?></strong> new session requests to review today.</p>
            </div>
            <div class="col-md-5 text-md-right mt-3 mt-md-0">
                <a href="add_session.php" class="btn btn-light btn-action mr-2 text-primary shadow-sm">
                    <i class="fas fa-plus mr-2"></i>New Session
                </a>
                <a href="manage_resources.php" class="btn btn-dark btn-action shadow-sm">
                    <i class="fas fa-file-upload mr-2"></i>Post Resource
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stat-card p-4 text-center">
                <h6 class="text-muted text-uppercase small font-weight-bold">Assigned Participants</h6>
                <h2 class="font-weight-bold text-dark"><?= $participants_count ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-4 text-center">
                <h6 class="text-muted text-uppercase small font-weight-bold">Pending Requests</h6>
                <h2 class="font-weight-bold text-warning"><?= $pending_requests ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card p-4 text-center">
                <h6 class="text-muted text-uppercase small font-weight-bold">Total Resources</h6>
                <h2 class="font-weight-bold text-info"><?= $resources_count ?></h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold mb-4">
                        <div class="card-title-icon"><i class="fas fa-heartbeat text-danger"></i></div>
                        Recent Mood Check-ins
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <thead class="text-muted small uppercase">
                                <tr>
                                    <th>Participant</th>
                                    <th>Mood</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($mood = mysqli_fetch_assoc($recent_moods)): ?>
                                <tr>
                                    <td class="font-weight-bold"><?= htmlspecialchars($mood['full_name']) ?></td>
                                    <td>
                                        <span class="mood-badge mood-<?= $mood['mood'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $mood['mood'])) ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= date('M d, H:i', strtotime($mood['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="view_participants.php" class="btn btn-link btn-sm text-primary p-0 mt-2 font-weight-bold">Review all participant profiles →</a>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold mb-4">
                        <div class="card-title-icon"><i class="fas fa-calendar-check text-primary"></i></div>
                        My Schedule
                    </h5>
                    <?php if(mysqli_num_rows($upcoming_sessions) > 0): ?>
                        <?php while($session = mysqli_fetch_assoc($upcoming_sessions)): ?>
                            <div class="mb-3 p-3 bg-light rounded shadow-sm border-left border-primary" style="border-width: 4px !important;">
                                <h6 class="font-weight-bold mb-1"><?= htmlspecialchars($session['title']) ?></h6>
                                <p class="small text-muted mb-2">
                                    <i class="far fa-clock mr-1"></i> <?= date('M d • h:i A', strtotime($session['session_date'] . ' ' . $session['start_time'])) ?>
                                </p>
                                <a href="<?= $session['meet_link'] ?>" target="_blank" class="btn btn-xs btn-primary py-0 px-2 small">Link</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-day fa-3x text-light mb-2"></i>
                            <p class="text-muted">No sessions scheduled.</p>
                        </div>
                    <?php endif; ?>
                    <a href="view_sessions.php" class="btn btn-outline-primary btn-block btn-sm mt-3 border-0 bg-light">Manage All Sessions</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>