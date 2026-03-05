<?php
require_once 'db.php';
session_start();

// Fetch some featured tasks
$stmt = $pdo->query("SELECT t.*, u.username as requester_name FROM tasks t JOIN users u ON t.requester_id = u.id WHERE t.status = 'open' ORDER BY t.created_at DESC LIMIT 6");
$featured_tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MicroTask - Earn Money Completing Small Tasks</title>
    <meta name="description" content="MicroTask is a platform for workers to earn money by completing small tasks and for requesters to get things done efficiently.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0066c0;
            --primary-hover: #004a8c;
            --bg-light: #f6f9fc;
            --text-dark: #333;
            --text-muted: #666;
            --white: #ffffff;
            --border-color: #e1e4e8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--white);
            color: var(--text-dark);
            line-height: 1.6;
        }

        header {
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        nav a {
            text-decoration: none;
            color: var(--text-dark);
            margin-left: 1.5rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        nav a:hover {
            color: var(--primary-color);
        }

        .hero {
            padding: 5rem 5%;
            text-align: center;
            background: linear-gradient(to bottom, var(--bg-light), var(--white));
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 800px;
            margin: 0 auto 2.5rem;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.8rem 2rem;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .section {
            padding: 4rem 5%;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--bg-light);
            padding: 2.5rem;
            border-radius: 12px;
            text-align: center;
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .task-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .task-card {
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            border-radius: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .task-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .task-card h3 {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .task-meta {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .task-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .payment {
            font-weight: 700;
            color: #28a745;
            font-size: 1.1rem;
        }

        footer {
            background: var(--bg-light);
            padding: 3rem 5%;
            text-align: center;
            border-top: 1px solid var(--border-color);
        }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            header { flex-direction: column; gap: 1rem; }
            nav { display: flex; flex-wrap: wrap; justify-content: center; }
            nav a { margin: 0.5rem; }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">💼 MicroTask</a>
    <nav>
        <a href="marketplace.php">Marketplace</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] == 'worker'): ?>
                <a href="worker_dashboard.php">Dashboard</a>
            <?php else: ?>
                <a href="requester_dashboard.php">Manage Tasks</a>
                <a href="post_task.php">Post a Task</a>
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php" class="btn btn-primary" style="padding: 0.5rem 1rem;">Sign Up</a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <section class="hero">
        <h1>Work on HITS and earn rewards</h1>
        <p>Get paid for small tasks that take minutes to complete. From data entry and transcription to surveys and content moderation.</p>
        <div class="cta-buttons">
            <a href="signup.php?role=worker" class="btn btn-primary">Start Earning Money</a>
            <a href="signup.php?role=requester" class="btn btn-outline">Post Microtasks</a>
        </div>
    </section>

    <section class="section" id="how-it-works">
        <div class="section-title">
            <h2>How It Works</h2>
            <p>Simple steps to get started on your micro-tasking journey.</p>
        </div>
        <div class="features">
            <div class="feature-card">
                <h3>1. Choose a Task</h3>
                <p>Browse through thousands of available HITS in our marketplace. Filter by category or payment amount.</p>
            </div>
            <div class="feature-card">
                <h3>2. Complete Work</h3>
                <p>Follow the instructions provided by the requester and submit your work through our secure platform.</p>
            </div>
            <div class="feature-card">
                <h3>3. Get Paid</h3>
                <p>Once your work is approved by the requester, your earnings are added to your account balance instantly.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-title">
            <h2>Featured Tasks</h2>
            <p>Latest opportunities available right now.</p>
        </div>
        <div class="task-grid">
            <?php if(empty($featured_tasks)): ?>
                <p style="text-align: center; grid-column: 1/-1;">No tasks available at the moment. Check back later!</p>
            <?php else: ?>
                <?php foreach($featured_tasks as $task): ?>
                    <div class="task-card">
                        <div>
                            <h3><?= htmlspecialchars($task['title']) ?></h3>
                            <div class="task-meta">
                                <span>📁 <?= htmlspecialchars($task['category']) ?></span> • 
                                <span>👤 <?= htmlspecialchars($task['requester_name']) ?></span>
                            </div>
                            <p><?= htmlspecialchars(substr($task['description'], 0, 100)) ?>...</p>
                        </div>
                        <div class="task-footer">
                            <span class="payment">$<?= number_format($task['payment'], 2) ?></span>
                            <a href="task_details.php?id=<?= $task['id'] ?>" class="btn btn-outline" style="padding: 0.4rem 1rem; font-size: 0.9rem;">View Task</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div style="text-align: center; margin-top: 3rem;">
            <a href="marketplace.php" class="btn btn-primary">Browse All Tasks</a>
        </div>
    </section>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> MicroTask Clone. All rights reserved.</p>
    <div style="margin-top: 1rem;">
        <a href="#" style="color: var(--text-muted); margin: 0 10px; text-decoration: none;">Terms of Service</a>
        <a href="#" style="color: var(--text-muted); margin: 0 10px; text-decoration: none;">Privacy Policy</a>
        <a href="#" style="color: var(--text-muted); margin: 0 10px; text-decoration: none;">Contact Support</a>
    </div>
</footer>

<script>
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>

</body>
</html>
