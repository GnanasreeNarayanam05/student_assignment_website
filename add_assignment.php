<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'Student';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_name = trim($_POST['assignment_name']);
    $subject = trim($_POST['subject']);
    $due_date = $_POST['due_date'];
    $status = "Pending";
    $user_id = $_SESSION['user_id'];

    if ($assignment_name == "" || $subject == "" || $due_date == "") {
        $message = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO assignments
            (assignment_name, subject, due_date, status, user_id)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssi",
            $assignment_name,
            $subject,
            $due_date,
            $status,
            $user_id
        );

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: view_assignments.php");
            exit();
        } else {
            $message = "Unable to add assignment. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Assignment</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f5f0ff;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #6a1b9a, #8e24aa);
            color: white;
            text-align: center;
            padding: 25px 15px;
        }

        .header h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .header p {
            margin: 0;
            font-size: 15px;
        }

        .navbar {
            background: white;
            padding: 16px;
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            box-shadow: 0 2px 8px #e1d5ee;
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
            background: white;
            max-width: 480px;
            width: calc(100% - 30px);
            margin: 35px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 18px #d1c4e9;
        }

        .container h2 {
            color: #6a1b9a;
            text-align: center;
            margin-top: 0;
        }

        .welcome {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 18px;
            margin-bottom: 7px;
            font-weight: bold;
            color: #444;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1c4e9;
            border-radius: 7px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #8e24aa;
            box-shadow: 0 0 5px #d1c4e9;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 13px;
            background: #8e24aa;
            color: white;
            border: none;
            border-radius: 7px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #6a1b9a;
        }

        .message {
            text-align: center;
            color: #c62828;
            margin-top: 15px;
        }

        .footer {
            text-align: center;
            color: #777;
            padding: 15px;
            font-size: 13px;
        }

        @media (max-width: 500px) {
            .header h1 {
                font-size: 22px;
            }

            .navbar {
                gap: 15px;
            }

            .container {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>Student Assignment Management System</h1>
    <p>Organize your assignments and manage your deadlines</p>
</div>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="add_assignment.php">Add Assignment</a>
    <a href="view_assignments.php">View Assignments</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">

    <h2>Add New Assignment</h2>

    <p class="welcome">
        Welcome, <?php echo htmlspecialchars($user_name); ?>!
        Enter your assignment details below.
    </p>

    <form method="POST" action="add_assignment.php">

        <label for="assignment_name">Assignment Name</label>
        <input
            type="text"
            id="assignment_name"
            name="assignment_name"
            placeholder="Enter assignment name"
            maxlength="255"
            required
        >

        <label for="subject">Subject</label>
        <input
            type="text"
            id="subject"
            name="subject"
            placeholder="Enter subject name"
            maxlength="255"
            required
        >

        <label for="due_date">Due Date</label>
        <input
            type="date"
            id="due_date"
            name="due_date"
            min="<?php echo date('Y-m-d'); ?>"
            required
        >

        <button type="submit">Add Assignment</button>

    </form>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

</div>

<div class="footer">
    Student Assignment Management System
</div>

</body>
</html>
