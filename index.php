<?php
session_start();
include('config.php');

// Fetch active counsellors/mentors
$mentors_result = mysqli_query($conn, "
    SELECT id, full_name, specialization AS expertise, bio
    FROM users 
    WHERE role='counsellor' AND status='active'
    ORDER BY created_at DESC
    LIMIT 3
");

// Fetch upcoming approved counselling sessions
$sessions_result = mysqli_query($conn, "
    SELECT cs.*, u.full_name AS counsellor_name
    FROM counselling_sessions cs
    JOIN users u ON cs.counsellor_id = u.id
    WHERE cs.approval_status='approved' AND cs.status='scheduled'
    ORDER BY cs.session_date ASC, cs.start_time ASC
    LIMIT 6
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mental Wellness Hub | Find Your Peace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">

    <style>
        :root { --primary-soft: #6c5ce7; --secondary-soft: #a29bfe; --success-soft: #00b894; }

        /* Hero Styling */
        .hero { 
            height: 90vh; 
            background: linear-gradient(rgba(45, 52, 54, 0.7), rgba(45, 52, 54, 0.7)), 
                        url('https://images.unsplash.com/photo-1527137342181-19aab11a8ee8?w=1600&q=80') center/cover no-repeat; 
            color: white; 
            display: flex; 
            align-items: center; 
            border-bottom-left-radius: 100px;
        }
        .hero h1 { font-size: 3.5rem; font-weight: 700; line-height: 1.2; }
        .hero p { font-size: 1.2rem; opacity: 0.9; }

        /* Feature Cards */
        .feature-icon { 
            width: 70px; height: 70px; background: var(--secondary-soft); 
            color: white; border-radius: 20px; display: flex; 
            align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 20px;
        }

        /* Card Styling */
        .card { border: none; border-radius: 20px; transition: 0.4s; overflow: hidden; }
        .card-hover:hover { transform: translateY(-12px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
        
        .section-title { font-weight: 700; color: #2d3436; margin-bottom: 40px; }
        .badge-time { background: rgba(108, 92, 231, 0.1); color: var(--primary-soft); font-weight: 600; padding: 5px 15px; border-radius: 50px; }

        /* Counsellor Section */
        .mentor-card { background: white; padding: 30px; border-radius: 20px; text-align: center; }
        .mentor-avatar { width: 80px; height: 80px; background: #eee; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #aaa; }

        footer { background: #2d3436; color: #dfe6e9; padding: 60px 0; border-top-right-radius: 100px; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<section class="hero">
    <div class="container text-center text-md-left">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="mb-4 text-white">Your Mental Health <br><span style="color: var(--secondary-soft);">Matters Most.</span></h1>
                <p class="mb-5 lead">Join a supportive community dedicated to mental wellness. Track your moods, book personal sessions with expert counsellors, and access curated resources.</p>
                <?php if (isset($_SESSION['id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary btn-lg px-5 py-3 shadow-lg" style="border-radius: 50px; background: var(--primary-soft);">
                        <i class="fas fa-home mr-2"></i> My Dashboard
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-success btn-lg px-5 py-3 shadow-lg mr-3" style="border-radius: 50px; background: var(--success-soft);">Get Started</a>
                    <a href="login.php" class="btn btn-outline-light btn-lg px-5 py-3" style="border-radius: 50px;">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="py-5 mt-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="feature-icon mx-auto"><i class="fas fa-smile"></i></div>
                <h4>Mood Tracking</h4>
                <p class="text-muted">Perform daily check-ins to monitor your emotional well-being over time.</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-icon mx-auto" style="background: var(--success-soft);"><i class="fas fa-video"></i></div>
                <h4>Virtual Sessions</h4>
                <p class="text-muted">Connect with experts via secure Google Meet sessions from anywhere.</p>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-icon mx-auto" style="background: #fab1a0;"><i class="fas fa-book-heart"></i></div>
                <h4>Wellness Resources</h4>
                <p class="text-muted">Browse articles and tools curated specifically for your mental growth.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="section-title mb-0">Upcoming Sessions</h2>
                <p class="text-muted">Approved programs ready for you to join.</p>
            </div>
            <a href="view_programs.php" class="btn btn-link text-primary font-weight-bold">View All <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
        
        <div class="row">
            <?php if (mysqli_num_rows($sessions_result) > 0): ?>
                <?php while ($session = mysqli_fetch_assoc($sessions_result)): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 card-hover shadow-sm">
                            <div class="card-body p-4">
                                <span class="badge-time mb-3 d-inline-block">
                                    <i class="far fa-calendar-alt mr-1"></i> <?= date('M d', strtotime($session['session_date'])) ?>
                                </span>
                                <h5 class="font-weight-bold mb-2"><?= htmlspecialchars($session['title']) ?></h5>
                                <p class="small text-muted mb-4"><i class="fa fa-user-md mr-1"></i> <?= htmlspecialchars($session['counsellor_name']) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small font-weight-bold"><?= date('h:i A', strtotime($session['start_time'])) ?></span>
                                    <a href="login.php" class="btn btn-sm btn-primary px-3 shadow-sm" style="border-radius: 10px;">Book Spot</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5"><p class="text-muted">No scheduled sessions found.</p></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="text-center section-title">Meet Our Counsellors</h2>
        <div class="row">
            <?php while ($mentor = mysqli_fetch_assoc($mentors_result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="mentor-card card-hover shadow-sm border">
                        <div class="mentor-avatar"><i class="fas fa-user-tie"></i></div>
                        <h5 class="font-weight-bold mb-1"><?= htmlspecialchars($mentor['full_name']) ?></h5>
                        <p class="text-primary small font-weight-bold mb-3"><?= htmlspecialchars($mentor['expertise']) ?></p>
                        <p class="text-muted small mb-4"><?= substr(htmlspecialchars($mentor['bio']), 0, 100) ?>...</p>
                        <a href="register.php" class="btn btn-outline-primary btn-sm btn-block" style="border-radius: 50px;">View Profile</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<footer class="text-center text-md-left">
    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <h4 class="font-weight-bold mb-3 text-white">Mental Wellness Program</h4>
                <p class="opacity-75">Fostering a supportive environment for workplaces and educational institutions through proactive care and accessibility.</p>
            </div>
            <div class="col-md-3 mb-4">
                <h6 class="font-weight-bold text-uppercase mb-3">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="view_programs.php" class="text-white-50">Programs</a></li>
                    <li><a href="login.php" class="text-white-50">Resources</a></li>
                    <li><a href="register.php" class="text-white-50">Join as Counsellor</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4 text-md-right">
                <h6 class="font-weight-bold text-uppercase mb-3">Connect</h6>
                <div class="h4">
                    <i class="fab fa-facebook-square mr-3"></i>
                    <i class="fab fa-twitter mr-3"></i>
                    <i class="fab fa-instagram"></i>
                </div>
            </div>
        </div>
        <hr class="border-secondary mt-5">
        <p class="text-center text-white-50 mb-0 pt-3 text-white small">© <?= date('Y') ?> Mental Wellness Program Management System. All rights reserved.</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>