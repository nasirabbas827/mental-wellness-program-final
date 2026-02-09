<?php
session_start();
include('../config.php');

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'participant') {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/** 1. DATA FETCHING **/

// Fetch User Profile and Preferred Counsellor Info
$user_query = mysqli_query($conn, "
    SELECT u.*, c.full_name as counsellor_name, c.specialization 
    FROM users u 
    LEFT JOIN users c ON u.preferred_counsellor_id = c.id 
    WHERE u.id = $user_id
");
$user_data = mysqli_fetch_assoc($user_query);

// Fetch Latest Mood Check-in (Requirement 5)
$latest_mood_query = mysqli_query($conn, "SELECT mood, created_at FROM mood_checkins WHERE participant_id = $user_id ORDER BY created_at DESC LIMIT 1");
$latest_mood = mysqli_fetch_assoc($latest_mood_query);

// Fetch Upcoming Approved Sessions (Requirement 7)
$sessions_query = mysqli_query($conn, "
    SELECT cs.*, u.full_name as counsellor_name 
    FROM counselling_sessions cs
    JOIN users u ON cs.counsellor_id = u.id
    WHERE cs.status = 'scheduled' AND cs.approval_status = 'approved'
    ORDER BY cs.session_date ASC LIMIT 3
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wellness Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">

    <style>
        :root { --wellness-blue: #0984e3; --wellness-green: #00b894; --wellness-purple: #6c5ce7; }
        
        .header-gradient {
            background: linear-gradient(135deg, var(--wellness-blue), var(--wellness-purple));
            color: white; padding: 50px 0; border-radius: 0 0 30px 30px;
        }

        .action-card {
            border: none; border-radius: 15px; transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;
            padding: 30px 20px; background: white; height: 100%;
        }
        .action-card:hover { transform: translateY(-10px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .action-icon { width: 60px; height: 60px; line-height: 60px; border-radius: 50%; margin: 0 auto 20px; font-size: 1.5rem; color: white; }

        .counsellor-box {
            background: #fff; border-left: 5px solid var(--wellness-green);
            border-radius: 10px; padding: 20px;
        }
        
        .mood-indicator {
            display: inline-block; padding: 8px 20px; border-radius: 50px;
            font-weight: 600; background: #e8f4fd; color: var(--wellness-blue);
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<header class="header-gradient shadow">
    <div class="container text-center">
        <h1 class="font-weight-bold text-white">Welcome back, <?= htmlspecialchars($_SESSION['full_name']); ?>!</h1>
        <p class="lead">“Self-care is how you take your power back.”</p>
        
        <?php if($latest_mood): ?>
            <div class="mood-indicator mt-3">
                <i class="fas fa-heart mr-2"></i> Last Check-in: <strong><?= ucfirst(str_replace('_', ' ', $latest_mood['mood'])) ?></strong>
            </div>
        <?php else: ?>
            <a href="mood_checkins.php" class="btn btn-light rounded-pill px-4 mt-2">How are you feeling today?</a>
        <?php endif; ?>
    </div>
</header>

<div class="container mt-n4">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="action-card">
                <div class="action-icon bg-primary"><i class="fas fa-smile"></i></div>
                <h5>Mood Check-In</h5>
                <p class="text-muted small">Share your current feelings with your counsellor.</p>
                <a href="mood_checkins.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Log Mood</a>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="action-card">
                <div class="action-icon bg-success"><i class="fas fa-video"></i></div>
                <h5>Book Session</h5>
                <p class="text-muted small">Schedule a 1-on-1 Google Meet consultation.</p>
                <a href="view_programs.php" class="btn btn-outline-success btn-sm rounded-pill px-4">Find Time</a>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="action-card">
                <div class="action-icon bg-info"><i class="fas fa-book-open"></i></div>
                <h5>Resources</h5>
                <p class="text-muted small">Read articles and watch wellness videos.</p>
                <a href="view_programs.php" class="btn btn-outline-info btn-sm rounded-pill px-4">Explore</a>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-5 mb-4">
            <h5 class="font-weight-bold mb-3">Your Support Network</h5>
            <div class="counsellor-box shadow-sm">
                <?php if($user_data['preferred_counsellor_id']): ?>
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_data['counsellor_name']) ?>&background=00b894&color=fff" class="rounded-circle" width="60">
                        </div>
                        <div>
                            <h6 class="mb-0 font-weight-bold">Dr. <?= htmlspecialchars($user_data['counsellor_name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($user_data['specialization']) ?></small>
                        </div>
                    </div>
                    <hr>
                    <a href="view_requested_session.php" class="btn btn-block btn-sm btn-light font-weight-bold">Contact Counsellor</a>
                <?php else: ?>
                    <p class="text-muted small">You haven't selected a preferred counsellor yet.</p>
                    <a href="view_counsellors.php" class="btn btn-primary btn-sm btn-block">Choose a Counsellor</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <h5 class="font-weight-bold mb-3">Upcoming Sessions</h5>
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="list-group list-group-flush">
                    <?php if(mysqli_num_rows($sessions_query) > 0): ?>
                        <?php while($session = mysqli_fetch_assoc($sessions_query)): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <div>
                                    <h6 class="mb-1 font-weight-bold"><?= htmlspecialchars($session['title']) ?></h6>
                                    <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> <?= $session['session_date'] ?> at <?= $session['start_time'] ?></small>
                                </div>
                                <a href="view_programs.php" class="btn btn-sm btn-link font-weight-bold">View Link</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">No sessions joined yet. Check available programs!</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="mt-5 py-4 text-center text-muted border-top">
    <div class="container">
        <p class="mb-0">Mental Wellness Program &copy; <?= date('Y') ?></p>
        <div class="mt-2">
            <a href="#" class="text-muted mr-3 small"><i class="fab fa-facebook"></i> Share</a>
            <a href="#" class="text-muted small"><i class="fab fa-twitter"></i> Tweet Progress</a>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>