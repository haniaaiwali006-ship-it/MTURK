<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'worker') {
    header("Location: login.php");
    exit;
}

$worker_id = $_SESSION['user_id'];

// Fetch stats
$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN s.status = 'approved' THEN t.payment ELSE 0 END) as total_earnings,
    COUNT(*) as total_tasks,
    COUNT(CASE WHEN s.status = 'approved' THEN 1 END) as approved_tasks,
    COUNT(CASE WHEN s.status = 'pending' THEN 1 END) as pending_tasks
    FROM submissions s
    JOIN tasks t ON s.task_id = t.id
    WHERE s.worker_id = ?");
$stmt->execute([$worker_id]);
$stats = $stmt->fetch();

// Fetch submissions
$stmt = $pdo->prepare("SELECT s.*, t.title, t.payment, r.rating, r.comment as review_comment 
    FROM submissions s 
    JOIN tasks t ON s.task_id = t.id 
    LEFT JOIN reviews r ON s.id = r.submission_id
    WHERE s.worker_id = ? 
    ORDER BY s.created_at DESC");
$stmt->execute([$worker_id]);
$submissions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - MicroTask</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0066c0;
            --bg-light: #f6f9fc;
            --text-dark: #333;
            --text-muted: #666;
            --white: #ffffff;
            --border-color: #e1e4e8;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
        }

        header {
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        nav a {
            text-decoration: none;
            color: var(--text-dark);
            margin-left: 1.5rem;
            font-weight: 500;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            text-align: center;
        }

        .stat-card h3 {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .dashboard-content {
            background: var(--white);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        h2 { margin-bottom: 1.5rem; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background: #fafafa;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.3rem 0.6rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        .btn-withdraw {
            background: var(--success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            float: right;
        }

        .review {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-style: italic;
            margin-top: 0.3rem;
        }

        .rating {
            color: #ffc107;
            font-weight: 700;
        }

        @media (max-width: 768px) {
            table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">💼 MicroTask</a>
    <nav>
        <a href="marketplace.php">Marketplace</a>
        <a href="worker_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Worker Dashboard</h1>
        <a href="marketplace.php" class="btn-withdraw" style="background: var(--primary-color);">Find More HITS</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Earnings</h3>
            <div class="value">$<?= number_format($stats['total_earnings'] ?? 0, 2) ?></div>
        </div>
        <div class="stat-card">
            <h3>Submitted Tasks</h3>
            <div class="value"><?= $stats['total_tasks'] ?></div>
        </div>
        <div class="stat-card">
            <h3>Approved HITS</h3>
            <div class="value"><?= $stats['approved_tasks'] ?></div>
        </div>
        <div class="stat-card">
            <h3>Pending Review</h3>
            <div class="value"><?= $stats['pending_tasks'] ?></div>
        </div>
    </div>

    <div class="dashboard-content">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Your Task History</h2>
            <a href="#" class="btn-withdraw" onclick="alert('Withdrawal request submitted! Minimum $10.00 required.')">Withdraw Funds</a>
        </div>
        
        <?php if(empty($submissions)): ?>
            <p style="text-align: center; padding: 2rem; color: var(--text-muted);">You haven't completed any tasks yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Submitted On</th>
                        <th>Reward</th>
                        <th>Status</th>
                        <th>Review</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($submissions as $sub): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($sub['title']) ?></strong>
                            </td>
                            <td><?= date('M j, Y', strtotime($sub['created_at'])) ?></td>
                            <td>$<?= number_format($sub['payment'], 2) ?></td>
                            <td>
                                <span class="status-badge status-<?= $sub['status'] ?>">
                                    <?= ucfirst($sub['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($sub['rating']): ?>
                                    <span class="rating">★ <?= $sub['rating'] ?></span>
                                    <div class="review"><?= htmlspecialchars($sub['review_comment']) ?></div>
                                <?php elseif($sub['status'] == 'approved'): ?>
                                    <span style="color: #ccc;">No review yet</span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
