<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';

// Total assignments
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total FROM assignments WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$totalAssignments = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Pending assignments
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM assignments
     WHERE user_id = ? AND status = 'Pending'"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pendingAssignments = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Completed assignments
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM assignments
     WHERE user_id = ? AND status = 'Completed'"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completedAssignments = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Assignment Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f7f4fc;
            margin: 0;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #6a1b9a, #8e24aa);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 30px;
        }

        .header p {
            margin-bottom: 0;
            color: #f3e5f5;
        }

        .navbar {
            background: white;
            padding: 16px;
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            box-shadow: 0 2px 8px #e5dced;
        }

        .navbar a {
            color: #6a1b9a;
            text-decoration: none;
            font-weight: bold;
        }

        .navbar a:hover {
            color: #ab47bc;
        }

        .container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .welcome {
            margin-bottom: 30px;
        }

        .welcome h2 {
            color: #4a148c;
            margin-bottom: 8px;
        }

        .welcome p {
            color: #666;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 22px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 5px 18px rgba(80, 30, 110, 0.09);
            border-left: 6px solid #8e24aa;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-4px);
        }

        .card h3 {
            color: #666;
            font-size: 16px;
            margin-top: 0;
        }

        .number {
            font-size: 38px;
            font-weight: bold;
            margin: 15px 0;
            color: #6a1b9a;
        }

        .pending {
            border-left-color: #ef6c00;
        }

        .pending .number {
            color: #ef6c00;
        }

        .completed {
            border-left-color: #2e7d32;
        }

        .completed .number {
            color: #2e7d32;
        }

        .card a {
            display: inline-block;
            margin-top: 8px;
            color: #6a1b9a;
            font-weight: bold;
            text-decoration: none;
        }

        .footer {
            text-align: center;
            padding: 25px 15px;
            color: #777;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .header h1 {
                font-size: 23px;
            }

            .container {
                margin-top: 25px;
            }

            .navbar {
                gap: 18px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>Student Assignment Management System</h1>
    <p>Organize your assignments. Manage your deadlines. Track your progress.</p>
</div>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="add_assignment.php">+ Add Assignment</a>
    <a href="view_assignments.php">View Assignments</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">

    <div class="welcome">
        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
        <p>Here is a summary of your assignments and progress.</p>
    </div>

    <div class="dashboard">

        <div class="card">
            <h3>Total Assignments</h3>
            <div class="number">
                <?php echo (int)$totalAssignments; ?>
            </div>
            <p>All your assignments</p>
            <a href="view_assignments.php">View assignments &rarr;</a>
        </div>

        <div class="card pending">
            <h3>Pending Assignments</h3>
            <div class="number">
                <?php echo (int)$pendingAssignments; ?>
            </div>
            <p>Assignments to complete</p>
            <a href="view_assignments.php">Check pending work &rarr;</a>
        </div>

        <div class="card completed">
            <h3>Completed Assignments</h3>
            <div class="number">
                <?php echo (int)$completedAssignments; ?>
            </div>
            <p>Assignments finished</p>
            <a href="view_assignments.php">View progress &rarr;</a>
        </div>

    </div>

</div>

<div class="footer">
    Student Assignment Management System | Stay organized and meet your deadlines.
</div>

</body>
</html>
