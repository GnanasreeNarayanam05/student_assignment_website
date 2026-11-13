<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';

// Get assignment ID
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
} else {
    header("Location: view_assignments.php");
    exit();
}

$message = "";

// Update assignment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_name = trim($_POST['assignment_name'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $due_date = $_POST['due_date'] ?? '';

    if ($assignment_name == "" || $subject == "" || $due_date == "") {
        $message = "Please fill in all fields.";
    } else {
        // Check that the assignment belongs to this student
        $check = $conn->prepare(
            "SELECT id FROM assignments WHERE id = ? AND user_id = ?"
        );
        $check->bind_param("ii", $id, $user_id);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows == 0) {
            $message = "Assignment not found or you do not have permission to edit it.";
            $check->close();
        } else {
            $check->close();

            $stmt = $conn->prepare(
                "UPDATE assignments
                 SET assignment_name = ?, subject = ?, due_date = ?
                 WHERE id = ? AND user_id = ?"
            );

            $stmt->bind_param(
                "sssii",
                $assignment_name,
                $subject,
                $due_date,
                $id,
                $user_id
            );

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: view_assignments.php");
                exit();
            } else {
                $message = "Unable to update assignment. Please try again.";
            }

            $stmt->close();
        }
    }
}

// Fetch this student's assignment
$stmt = $conn->prepare(
    "SELECT * FROM assignments WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    $conn->close();

    echo "Assignment not found or you do not have permission to edit it.";
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Assignment</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
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

        h2 {
            text-align: center;
            color: #6a1b9a;
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

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #6a1b9a;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .message {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
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
    <p>Manage your assignments and deadlines</p>
</div>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="add_assignment.php">Add Assignment</a>
    <a href="view_assignments.php">View Assignments</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">

    <h2>Edit Assignment</h2>

    <p class="welcome">
        Welcome, <?php echo htmlspecialchars($user_name); ?>!
        Update your assignment details below.
    </p>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST" action="edit.php">

        <input type="hidden" name="id"
               value="<?php echo (int)$row['id']; ?>">

        <label for="assignment_name">Assignment Name</label>
        <input
            type="text"
            id="assignment_name"
            name="assignment_name"
            maxlength="255"
            value="<?php echo htmlspecialchars($row['assignment_name'] ?? ''); ?>"
            required
        >

        <label for="subject">Subject</label>
        <input
            type="text"
            id="subject"
            name="subject"
            maxlength="255"
            value="<?php echo htmlspecialchars($row['subject'] ?? ''); ?>"
            required
        >

        <label for="due_date">Due Date</label>
        <input
            type="date"
            id="due_date"
            name="due_date"
            value="<?php echo htmlspecialchars($row['due_date'] ?? ''); ?>"
            required
        >

        <button type="submit">Save Changes</button>

    </form>

    <a class="back-link" href="view_assignments.php">
        ← Back to Assignments
    </a>

</div>

<div class="footer">
    Student Assignment Management System
</div>

</body>
</html>

<?php
$conn->close();
?>
