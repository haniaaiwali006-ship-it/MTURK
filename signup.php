<?php
require_once 'db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or Email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            try {
                $stmt->execute([$username, $email, $hashedPassword, $role]);
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            } catch (PDOException $e) {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

$pre_role = isset($_GET['role']) ? $_GET['role'] : 'worker';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - MicroTask</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .auth-card {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 450px;
        }

        .auth-card h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #004a8c;
        }

        .alert {
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }

        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #f87171;
        }

        .alert-success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #4ade80;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }

        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <a href="index.php" style="text-decoration: none; color: var(--primary-color); font-weight: 700; display: block; text-align: center; margin-bottom: 1rem;">💼 MicroTask</a>
    <h2>Create your account</h2>

    <?php if($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required placeholder="Choose a unique username">
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required placeholder="your@email.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Minimum 6 characters">
        </div>
        <div class="form-group">
            <label>I want to</label>
            <select name="role" required>
                <option value="worker" <?= $pre_role == 'worker' ? 'selected' : '' ?>>Work on tasks (Worker)</option>
                <option value="requester" <?= $pre_role == 'requester' ? 'selected' : '' ?>>Post tasks (Requester)</option>
            </select>
        </div>
        <button type="submit" class="btn">Sign Up</button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

</body>
</html>
