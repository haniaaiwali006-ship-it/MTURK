<?php
// dashboard.php - Worker Dashboard
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'worker') {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

// Handle completing task
if (isset($_GET['complete'])) {
    $assignment_id = $_GET['complete'];

    // Update assignment to completed
    $sql = "UPDATE task_assignments SET status = 'completed', completion_date = NOW() WHERE id = ? AND worker_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $assignment_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    // Update task to completed
    $get_task_sql = "SELECT task_id, rating, review FROM task_assignments WHERE id = ?";
    $get_stmt = $conn->prepare($get_task_sql);
    $get_stmt->bind_param("i", $assignment_id);
    $get_stmt->execute();
    $result = $get_stmt->get_result();
    $assignment = $result->fetch_assoc();
    $get_stmt->close();

    if ($assignment) {
        $task_id = $assignment['task_id'];
        $update_task_sql = "UPDATE tasks SET status = 'completed' WHERE id = ?";
        $update_stmt = $conn->prepare($update_task_sql);
        $update_stmt->bind_param("i", $task_id);
        $update_stmt->execute();
        $update_stmt->close();

        // Credit payment to worker (simple: add to balance)
        $get_payment_sql = "SELECT payment FROM tasks WHERE id = ?";
        $pay_stmt = $conn->prepare($get_payment_sql);
        $pay_stmt->bind_param("i", $task_id);
        $pay_stmt->execute();
        $pay_result = $pay_stmt->get_result();
        $task = $pay_result->fetch_assoc();
        $pay_stmt->close();

        $payment = $task['payment'];
        $update_balance_sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
        $bal_stmt = $conn->prepare($update_balance_sql);
        $bal_stmt->bind_param("di", $payment, $_SESSION['user_id']);
        $bal_stmt->execute();
        $bal_stmt->close();
    }
}

// Handle withdrawal (dummy)
if (isset($_POST['withdraw'])) {
    // In real app, integrate payment gateway. Here, just reset balance.
    $sql = "UPDATE users SET balance = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: white; color: #333; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .section { background-color: #f0f0f0; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #e0e0e0; }
        .btn { background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        @media (max-width: 768px) { table { font-size: 14px; } }
    </style>
</head>
<body>
    <div class="container">
        <h1>Worker Dashboard</h1>
        <div class="section">
            <h2>Earnings Summary</h2>
            <?php
            $sql = "SELECT balance FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            echo '<p>Current Balance: $' . $user['balance'] . '</p>';
            $stmt->close();
            ?>
            <form method="POST">
                <button type="submit" name="withdraw" class="btn">Withdraw Earnings (Dummy)</button>
            </form>
        </div>
        <div class="section">
            <h2>Your Tasks</h2>
            <table>
                <thead>
                    <tr>
                        <th>Task Title</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT ta.id as assignment_id, t.title, ta.status, t.payment 
                            FROM task_assignments ta 
                            JOIN tasks t ON ta.task_id = t.id 
                            WHERE ta.worker_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                            echo '<td>' . $row['status'] . '</td>';
                            echo '<td>$' . $row['payment'] . '</td>';
                            if ($row['status'] == 'applied' || $row['status'] == 'accepted') {
                                echo '<td><a href="?complete=' . $row['assignment_id'] . '" class="btn">Complete Task</a></td>';
                            } else {
                                echo '<td>Completed</td>';
                            }
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4">No tasks assigned.</td></tr>';
                    }
                    $stmt->close();
                    ?>
                </tbody>
            </table>
        </div>
        <a href="marketplace.php" class="btn">Go to Marketplace</a>
        <a href="index.php" class="btn">Back to Home</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>
