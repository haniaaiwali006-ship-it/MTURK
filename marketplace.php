<?php
require_once 'db.php';
session_start();

$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT t.*, u.username as requester_name FROM tasks t JOIN users u ON t.requester_id = u.id WHERE t.status = 'open'";
$params = [];

if ($category_filter) {
    $sql .= " AND t.category = ?";
    $params[] = $category_filter;
}

if ($search_query) {
    $sql .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$sql .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// Get unique categories for filter
$cat_stmt = $pdo->query("SELECT DISTINCT category FROM tasks");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Marketplace - MicroTask</title>
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
            margin: 0;
            background-color: var(--white);
            color: var(--text-dark);
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
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
        }

        .filters {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 8px;
            height: fit-content;
        }

        .filters h3 {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .filter-group {
            margin-bottom: 1.5rem;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .filter-group select, .filter-group input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .marketplace-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .task-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .task-row {
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            border-radius: 8px;
            display: grid;
            grid-template-columns: 1fr 150px 120px;
            align-items: center;
            transition: background 0.2s;
        }

        .task-row:hover {
            background-color: #fcfcfc;
        }

        .task-info h4 {
            margin-bottom: 0.3rem;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .task-info p {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .task-reward {
            text-align: right;
            font-weight: 700;
            color: #28a745;
            font-size: 1.1rem;
        }

        .task-action {
            text-align: right;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            border: none;
            display: inline-block;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        @media (max-width: 992px) {
            .container {
                grid-template-columns: 1fr;
            }
            .task-row {
                grid-template-columns: 1fr 100px;
            }
            .task-reward {
                grid-row: 1;
                grid-column: 2;
            }
            .task-action {
                grid-column: 1 / span 2;
                margin-top: 1rem;
            }
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
            <?php endif; ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php" class="btn btn-primary">Sign Up</a>
        <?php endif; ?>
    </nav>
</header>

<div class="container">
    <aside class="filters">
        <h3>Filter Tasks</h3>
        <form method="GET">
            <div class="filter-group">
                <label>Search Keyword</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="e.g. Survey">
            </div>
            <div class="filter-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $category_filter == $cat ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Apply Filters</button>
            <a href="marketplace.php" style="display: block; text-align: center; margin-top: 1rem; font-size: 0.9rem; color: var(--text-muted);">Clear All</a>
        </form>
    </aside>

    <main>
        <div class="marketplace-header">
            <h2>Available Tasks (HITS)</h2>
            <span style="color: var(--text-muted);"><?= count($tasks) ?> open tasks found</span>
        </div>

        <div class="task-list">
            <?php if(empty($tasks)): ?>
                <div style="text-align: center; padding: 3rem; background: var(--bg-light); border-radius: 8px;">
                    <p>No tasks match your criteria. Try adjusting your filters.</p>
                </div>
            <?php else: ?>
                <?php foreach($tasks as $task): ?>
                    <div class="task-row">
                        <div class="task-info">
                            <h4><?= htmlspecialchars($task['title']) ?></h4>
                            <p>📁 <?= htmlspecialchars($task['category']) ?> • 👤 <?= htmlspecialchars($task['requester_name']) ?></p>
                            <p style="margin-top: 0.5rem;"><?= htmlspecialchars(substr($task['description'], 0, 150)) ?>...</p>
                        </div>
                        <div class="task-reward">
                            $<?= number_format($task['payment'], 2) ?>
                        </div>
                        <div class="task-action">
                            <a href="task_details.php?id=<?= $task['id'] ?>" class="btn btn-primary">Accept & Work</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>
