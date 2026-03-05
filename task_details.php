<?php
require_once 'db.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: marketplace.php");
    exit;
}

$task_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT t.*, u.username as requester_name FROM tasks t JOIN users u ON t.requester_id = u.id WHERE t.id = ?");
$stmt->execute([$task_id]);
$task = $stmt->fetch();

if (!$task) {
    die("Task not found.");
}

$error = '';
$success = '';

// Handle submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['role'] == 'worker') {
    $submission_data = trim($_POST['submission_data']);
    
    if (empty($submission_data)) {
        $error = "Submission cannot be empty.";
    } else {
        // Check if already submitted
        $check = $pdo->prepare("SELECT id FROM submissions WHERE task_id = ? AND worker_id = ?");
        $check->execute([$task_id, $_SESSION['user_id']]);
        if ($check->fetch()) {
            $error = "You have already submitted work for this task.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO submissions (task_id, worker_id, submission_data) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$task_id, $_SESSION['user_id'], $submission_data]);
                $success = "Work submitted successfully! Waiting for approval.";
            } catch (PDOException $e) {
                $error = "Failed to submit. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($task['title']) ?> - MicroTask</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0066c0;
            --bg-light: #f6f9fc;
            --text-dark: #333;
            --text-muted: #666;
            --white: #ffffff;
            --border-color: #e1e4e8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--white);
            color: var(--text-dark);
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
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .task-header {
            background: var(--bg-light);
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .task-header h1 {
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }

        .task-meta {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .payment-box {
            background: var(--white);
            padding: 1rem 1.5rem;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            text-align: center;
        }

        .payment-box .amount {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #28a745;
        }

        .section {
            margin-bottom: 2rem;
        }

        .section h3 {
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--bg-light);
            padding-bottom: 0.5rem;
        }

        .content {
            white-space: pre-line;
            line-height: 1.8;
            background: #fff;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
        }

        .submission-form {
            background: var(--bg-light);
            padding: 2rem;
            border-radius: 8px;
            margin-top: 3rem;
        }

        .form-group textarea {
            width: 100%;
            height: 200px;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
            margin-bottom: 1.5rem;
        }

        .btn {
            padding: 0.8rem 2rem;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            display: inline-block;
        }

        .btn-primary { background: var(--primary-color); color: white; }

        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #4ade80; }

        @media (max-width: 600px) {
            .task-header { flex-direction: column; gap: 1rem; }
            .payment-box { width: 100%; }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">💼 MicroTask</a>
    <nav>
        <a href="marketplace.php">Marketplace</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="<?= $_SESSION['role'] ?>_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <div class="task-header">
        <div>
            <h1><?= htmlspecialchars($task['title']) ?></h1>
            <div class="task-meta">
                <span>📁 <?= htmlspecialchars($task['category']) ?></span> • 
                <span>👤 Posted by <?= htmlspecialchars($task['requester_name']) ?></span> • 
                <span>📅 Deadline: <?= date('M j, Y H:i', strtotime($task['deadline'])) ?></span>
            </div>
        </div>
        <div class="payment-box">
            <span class="amount">$<?= number_format($task['payment'], 2) ?></span>
            <span style="font-size: 0.8rem; color: var(--text-muted);">Reward</span>
        </div>
    </div>

    <div class="section">
        <h3>Description & Instructions</h3>
        <div class="content">
            <?= htmlspecialchars($task['description']) ?>
        </div>
    </div>

    <?php if(!isset($_SESSION['user_id'])): ?>
        <div class="submission-form" style="text-align: center;">
            <p>Please <a href="login.php">Login</a> as a Worker to complete this task.</p>
        </div>
    <?php elseif($_SESSION['role'] == 'worker'): ?>
        <div class="submission-form">
            <h3>Submit Your Work</h3>
            <?php if($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 1rem; font-weight: 500;">Provide your results or paste the required content below:</label>
                    <textarea name="submission_data" required placeholder="Enter your work here..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit HIT</button>
            </form>
        </div>
    <?php else: ?>
        <div class="submission-form" style="text-align: center;">
            <p>You are logged in as a Requester. You can view tasks but not submit work.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
