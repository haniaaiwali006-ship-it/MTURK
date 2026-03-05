<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'requester') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $payment = (float)$_POST['payment'];
    $deadline = $_POST['deadline'];

    if (empty($title) || empty($description) || empty($category) || $payment <= 0 || empty($deadline)) {
        $error = "Please fill in all fields correctly.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tasks (requester_id, title, description, category, payment, deadline) VALUES (?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$_SESSION['user_id'], $title, $description, $category, $payment, $deadline]);
            $success = "Task posted successfully!";
        } catch (PDOException $e) {
            $error = "Failed to post task. please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Task - MicroTask</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0066c0;
            --bg-light: #f6f9fc;
            --text-dark: #333;
            --white: #ffffff;
            --border-color: #e1e4e8;
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
            max-width: 800px;
            margin: 3rem auto;
            background: var(--white);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        h2 { margin-bottom: 2rem; color: var(--primary-color); }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
        }
        .form-group textarea { height: 150px; resize: vertical; }

        .btn {
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }

        .alert { padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #4ade80; }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">💼 MicroTask</a>
    <nav>
        <a href="marketplace.php">Marketplace</a>
        <a href="requester_dashboard.php">Manage Tasks</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Post a New Task (HIT)</h2>

    <?php if($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Task Title</label>
            <input type="text" name="title" required placeholder="e.g., Categorize these 10 images">
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category" required>
                <option value="">Select a category</option>
                <option value="Survey">Survey</option>
                <option value="Data Entry">Data Entry</option>
                <option value="Transcription">Transcription</option>
                <option value="Content Moderation">Content Moderation</option>
                <option value="Translation">Translation</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label>Payment Per Task ($)</label>
            <input type="number" step="0.01" name="payment" required placeholder="0.50">
        </div>
        <div class="form-group">
            <label>Deadline</label>
            <input type="datetime-local" name="deadline" required>
        </div>
        <div class="form-group">
            <label>Instructions & Description</label>
            <textarea name="description" required placeholder="Detailed steps for the worker..."></textarea>
        </div>
        <button type="submit" class="btn">Publish Task</button>
    </form>
</div>

</body>
</html>
