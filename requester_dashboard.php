<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requester') {
    header("Location: login.php");
    exit;
}

$requester_id = $_SESSION['user_id'];

// Handle Approval/Rejection
if (isset($_POST['action']) && isset($_POST['submission_id'])) {
    $sub_id = $_POST['submission_id'];
    $action = $_POST['action']; // approved or rejected
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    $pdo->beginTransaction();
    try {
        // Update submission status
        $stmt = $pdo->prepare("UPDATE submissions SET status = ? WHERE id = ?");
        $stmt->execute([$action, $sub_id]);

        // If approved, update worker balance (simulated logic)
        // In a real app, we'd add to user balance here

        // Add review if provided
        if ($action == 'approved' && $rating) {
            $stmt = $pdo->prepare("INSERT INTO reviews (submission_id, rating, comment) VALUES (?, ?, ?)");
            $stmt->execute([$sub_id, $rating, $comment]);
        }

        $pdo->commit();
        $success_msg = "Submission $action successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_msg = "Error updating submission.";
    }
}

// Fetch posted tasks
$stmt = $pdo->prepare("SELECT t.*, 
    (SELECT COUNT(*) FROM submissions WHERE task_id = t.id) as sub_count,
    (SELECT COUNT(*) FROM submissions WHERE task_id = t.id AND status = 'pending') as pending_count
    FROM tasks t WHERE t.requester_id = ? ORDER BY t.created_at DESC");
$stmt->execute([$requester_id]);
$tasks = $stmt->fetchAll();

// Fetch pending submissions if a specific task is selected or show all pending
$selected_task_id = isset($_GET['task_id']) ? $_GET['task_id'] : null;
$sub_query = "SELECT s.*, t.title as task_title, u.username as worker_name 
              FROM submissions s 
              JOIN tasks t ON s.task_id = t.id 
              JOIN users u ON s.worker_id = u.id 
              WHERE t.requester_id = ? AND s.status = 'pending'";
$sub_params = [$requester_id];

if ($selected_task_id) {
    $sub_query .= " AND s.task_id = ?";
    $sub_params[] = $selected_task_id;
}

$stmt = $pdo->prepare($sub_query);
$stmt->execute($sub_params);
$pending_submissions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requester Dashboard - MicroTask</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0066c0;
            --bg-light: #f6f9fc;
            --text-dark: #333;
            --white: #ffffff;
            --border-color: #e1e4e8;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; }

        header {
            background: var(--white);
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo { font-size: 1.5rem; font-weight: 700; color: var(--primary-color); text-decoration: none; }
        nav a { text-decoration: none; color: var(--text-dark); margin-left: 1.5rem; font-weight: 500; }

        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }

        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 2rem; }

        .card { background: var(--white); padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        h2 { margin-bottom: 1.5rem; font-size: 1.3rem; }

        .task-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background 0.2s;
        }
        .task-item:hover { background: #fafafa; }
        .task-item.active { border-left: 4px solid var(--primary-color); background: #f0f7ff; }

        .submission-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .sub-header { display: flex; justify-content: space-between; margin-bottom: 1rem; }
        .sub-data { background: #f9f9f9; padding: 1rem; border-radius: 4px; border: 1px inset #eee; margin-bottom: 1rem; white-space: pre-wrap; }

        .review-form { display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; }
        .form-group { display: flex; flex-direction: column; gap: 0.3rem; }
        input, select, textarea { padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; }

        .btn { padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; font-weight: 600; cursor: pointer; border: none; }
        .btn-approve { background: #28a745; color: white; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-post { background: var(--primary-color); color: white; display: block; text-align: center; margin-bottom: 1rem; }

        .badge { background: #0066c0; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem; }

        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">💼 MicroTask</a>
    <nav>
        <a href="marketplace.php">Marketplace</a>
        <a href="requester_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1>Requester Dashboard</h1>
    
    <?php if(isset($success_msg)): ?>
        <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;"><?= $success_msg ?></div>
    <?php endif; ?>

    <div class="grid">
        <aside>
            <a href="post_task.php" class="btn btn-post">Post New HIT</a>
            <div class="card">
                <h2>Your Posted Tasks</h2>
                <div class="task-list">
                    <div class="task-item <?= !$selected_task_id ? 'active' : '' ?>" onclick="location.href='requester_dashboard.php'">
                        <strong>Show All Pending</strong>
                    </div>
                    <?php foreach($tasks as $t): ?>
                        <div class="task-item <?= $selected_task_id == $t['id'] ? 'active' : '' ?>" onclick="location.href='requester_dashboard.php?task_id=<?= $t['id'] ?>'">
                            <div><?= htmlspecialchars($t['title']) ?></div>
                            <small class="badge"><?= $t['pending_count'] ?> pending</small>
                            <small style="color: var(--text-muted);">$<?= number_format($t['payment'], 2) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <main>
            <div class="card">
                <h2>Pending Submissions <?= $selected_task_id ? "for Selected Task" : "(All Tasks)" ?></h2>
                
                <?php if(empty($pending_submissions)): ?>
                    <p style="text-align: center; padding: 2rem; color: var(--text-muted);">No pending submissions to review.</p>
                <?php else: ?>
                    <?php foreach($pending_submissions as $sub): ?>
                        <div class="submission-card">
                            <div class="sub-header">
                                <div>
                                    <strong>Worker: <?= htmlspecialchars($sub['worker_name']) ?></strong><br>
                                    <small>Task: <?= htmlspecialchars($sub['task_title']) ?></small>
                                </div>
                                <div style="color: var(--text-muted); font-size: 0.9rem;">
                                    Submitted: <?= date('M j, Y H:i', strtotime($sub['created_at'])) ?>
                                </div>
                            </div>
                            
                            <div class="sub-data"><?= htmlspecialchars($sub['submission_data']) ?></div>

                            <form method="POST" class="review-form">
                                <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                
                                <div class="form-group">
                                    <label>Worker Rating (1-5)</label>
                                    <select name="rating">
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Good</option>
                                        <option value="3">3 - Average</option>
                                        <option value="2">2 - Poor</option>
                                        <option value="1">1 - Very Poor</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" style="flex: 1;">
                                    <label>Optional Feedback</label>
                                    <input type="text" name="comment" placeholder="Well done!">
                                </div>

                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="submit" name="action" value="approved" class="btn btn-approve">Approve</button>
                                    <button type="submit" name="action" value="rejected" class="btn btn-reject">Reject</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

</body>
</html>
