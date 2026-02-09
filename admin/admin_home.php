<?php
session_start();
include('config.php');

// Redirect if not admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

/** 1. DATA AGGREGATION **/

// Count Users (Requirement 3)
$total_participants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='participant'"))['count'];
$total_counsellors  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='counsellor'"))['count'];

// Sessions & Resources (Requirement 8)
$pending_sessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM counselling_sessions WHERE approval_status='disapproved'"))['count'];
$total_resources  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM wellness_resources"))['count'];

// Mood Trends (Requirement 7)
// Aggregating global mood data for the pie chart
$mood_stats_query = mysqli_query($conn, "SELECT mood, COUNT(*) as count FROM mood_checkins GROUP BY mood");
$mood_labels = [];
$mood_counts = [];
while ($row = mysqli_fetch_assoc($mood_stats_query)) {
    $mood_labels[] = ucfirst(str_replace('_', ' ', $row['mood']));
    $mood_counts[] = $row['count'];
}

// Recent Pending Approvals (Requirement 8)
$recent_pending = mysqli_query($conn, "
    SELECT cs.*, u.full_name 
    FROM counselling_sessions cs 
    JOIN users u ON cs.counsellor_id = u.id 
    WHERE cs.approval_status='disapproved' 
    ORDER BY cs.created_at DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Wellness Program</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        :root { --primary: #6c5ce7; --success: #00b894; --warning: #fdcb6e; --danger: #ff7675; }
        
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), #a29bfe);
            color: white; border-radius: 20px; padding: 40px; margin-bottom: 30px;
        }

        .stat-card {
            border: none; border-radius: 15px; transition: 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .stat-card:hover { transform: translateY(-5px); }
        .icon-shape {
            width: 50px; height: 50px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; color: white;
        }
        
        .activity-feed { max-height: 400px; overflow-y: auto; }
        .chart-card { border-radius: 15px; border: none; padding: 20px; }
    </style>
</head>
<body>

<?php include('admin_navbar.php'); ?>

<div class="container-fluid py-4 px-lg-5">
    <div class="welcome-banner shadow-lg">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="display-5 font-weight-bold text-white">System Oversight</h1>
                <p class="lead mb-0">Manage users, approve wellness programs, and monitor community health trends.</p>
            </div>
            <div class="col-md-4 text-right d-none d-md-block">
                <i class="fas fa-user-shield fa-5x opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-primary mr-3"><i class="fas fa-users"></i></div>
                    <div>
                        <p class="text-muted mb-0 small uppercase">Participants</p>
                        <h3 class="font-weight-bold mb-0"><?= $total_participants ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-success mr-3"><i class="fas fa-user-md"></i></div>
                    <div>
                        <p class="text-muted mb-0 small uppercase">Counsellors</p>
                        <h3 class="font-weight-bold mb-0"><?= $total_counsellors ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-warning mr-3"><i class="fas fa-clock"></i></div>
                    <div>
                        <p class="text-muted mb-0 small uppercase">Pending Approval</p>
                        <h3 class="font-weight-bold mb-0"><?= $pending_sessions ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-info mr-3"><i class="fas fa-book-medical"></i></div>
                    <div>
                        <p class="text-muted mb-0 small uppercase">Resources</p>
                        <h3 class="font-weight-bold mb-0"><?= $total_resources ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card chart-card shadow-sm h-100">
                <h5 class="font-weight-bold mb-4">Aggregated Wellness Trends</h5>
                <canvas id="moodTrendChart" height="150"></canvas>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card chart-card shadow-sm h-100">
                <h5 class="font-weight-bold mb-4">Pending Approvals</h5>
                <div class="activity-feed">
                    <?php if (mysqli_num_rows($recent_pending) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($recent_pending)): ?>
                            <div class="p-3 mb-2 bg-light rounded d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 font-weight-bold"><?= htmlspecialchars($row['title']) ?></h6>
                                    <small class="text-muted">By: <?= htmlspecialchars($row['full_name']) ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                            <p class="text-muted">All sessions reviewed!</p>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="view_sessions.php" class="btn btn-block btn-light mt-3">View All Sessions</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Requirement 7: Visualizing Mood Distribution
    const ctx = document.getElementById('moodTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($mood_labels) ?>,
            datasets: [{
                label: 'Number of Check-ins',
                data: <?= json_encode($mood_counts) ?>,
                backgroundColor: ['#ff7675', '#fab1a0', '#ffeaa7', '#55efc4', '#00b894'],
                borderRadius: 10
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { display: false } } }
        }
    });
</script>
</body>
</html>