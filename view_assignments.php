<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Student';

// Delete assignment
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $conn->prepare(
        "DELETE FROM assignments WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: view_assignments.php");
    exit();
}

// Update assignment status
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];

    if (in_array($status, ['Pending', 'Completed'], true)) {
        $stmt = $conn->prepare(
            "UPDATE assignments
             SET status = ?
             WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param("sii", $status, $id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: view_assignments.php");
    exit();
}

// Search assignments
$search = trim($_GET['search'] ?? '');
$searchValue = "%" . $search . "%";

$stmt = $conn->prepare(
    "SELECT * FROM assignments
     WHERE user_id = ?
     AND (assignment_name LIKE ? OR subject LIKE ?)
     ORDER BY due_date ASC"
);

$stmt->bind_param("iss", $user_id, $searchValue, $searchValue);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>View Assignments</title>

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
            max-width: 1200px;
            width: calc(100% - 30px);
            margin: 30px auto;
        }

        .welcome {
            text-align: center;
            color: #666;
            margin-bottom: 20px;
        }

        .section-title {
            text-align: center;
            color: #6a1b9a;
            margin-bottom: 25px;
        }

        .search-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px #d1c4e9;
            margin-bottom: 25px;
        }

        .search-form {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-form input {
            flex: 1;
            min-width: 220px;
            max-width: 450px;
            padding: 12px;
            border: 1px solid #d1c4e9;
            border-radius: 7px;
            font-size: 14px;
        }

        button, .clear-btn {
            display: inline-block;
            padding: 10px 14px;
            border: none;
            border-radius: 6px;
            color: white;
            background: #8e24aa;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }

        button:hover, .clear-btn:hover {
            background: #6a1b9a;
        }

        .table-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px #d1c4e9;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        th {
            background: #7b1fa2;
            color: white;
            padding: 14px 10px;
        }

        td {
            padding: 12px 10px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        tbody tr:nth-child(even) {
            background: #faf7ff;
        }

        tbody tr:hover {
            background: #f3e5f5;
        }

        select {
            padding: 8px;
            border: 1px solid #d1c4e9;
            border-radius: 5px;
            margin-bottom: 7px;
        }

        .edit-btn {
            display: inline-block;
            background: #6a1b9a;
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .delete-btn {
            background: #d32f2f;
        }

        .delete-btn:hover {
            background: #b71c1c;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }

        .pending {
            background: #fff3cd;
            color: #856404;
        }

        .completed {
            background: #d4edda;
            color: #155724;
        }

        .overdue {
            background: #f8d7da;
            color: #721c24;
        }

        .upcoming {
            background: #dbeafe;
            color: #1e40af;
        }

        .empty {
            padding: 25px;
            color: #777;
        }

        .footer {
            text-align: center;
            color: #777;
            padding: 20px;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .header h1 {
                font-size: 22px;
            }

            .navbar {
                gap: 15px;
            }

            .table-box {
                padding: 10px;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>Student Assignment Management System</h1>
    <p>Keep track of your assignments and deadlines</p>
</div>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="add_assignment.php">Add Assignment</a>
    <a href="view_assignments.php">View Assignments</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">

    <p class="welcome">
        Welcome, <?php echo htmlspecialchars($user_name); ?>!
        Here are your assignments.
    </p>

    <h2 class="section-title">My Assignments</h2>

    <div class="search-box">
        <form class="search-form" method="GET"
              action="view_assignments.php">

            <input
                type="text"
                name="search"
                placeholder="Search by assignment name or subject..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">Search</button>

            <a class="clear-btn" href="view_assignments.php">
                Clear Search
            </a>

        </form>
    </div>

    <div class="table-box">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Assignment Name</th>
                    <th>Subject</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($result->num_rows > 0) { ?>

                <?php while ($row = $result->fetch_assoc()) { ?>

                    <?php
                    $currentStatus = strtolower($row['status'] ?? 'pending');

                    if ($currentStatus == 'completed') {
                        $deadlineText = 'Completed';
                        $deadlineClass = 'completed';
                    } elseif (empty($row['due_date'])) {
                        $deadlineText = 'No date';
                        $deadlineClass = 'pending';
                    } elseif ($row['due_date'] < date('Y-m-d')) {
                        $deadlineText = 'Overdue';
                        $deadlineClass = 'overdue';
                    } else {
                        $deadlineText = 'Upcoming';
                        $deadlineClass = 'upcoming';
                    }
                    ?>

                    <tr>
                        <td>
                            <?php echo (int)$row['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['assignment_name'] ?? ''); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['subject'] ?? ''); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row['due_date'] ?? ''); ?>
                        </td>

                        <td>
                            <form method="POST"
                                  action="view_assignments.php">

                                <input type="hidden" name="id"
                                       value="<?php echo (int)$row['id']; ?>">

                                <select name="status">
                                    <option value="Pending"
                                        <?php if ($currentStatus == 'pending') echo 'selected'; ?>>
                                        Pending
                                    </option>

                                    <option value="Completed"
                                        <?php if ($currentStatus == 'completed') echo 'selected'; ?>>
                                        Completed
                                    </option>
                                </select>

                                <br>

                                <button type="submit"
                                        name="update_status">
                                    Update
                                </button>

                            </form>
                        </td>

                        <td>
                            <span class="status <?php echo $deadlineClass; ?>">
                                <?php echo htmlspecialchars($deadlineText); ?>
                            </span>
                        </td>

                        <td>
                            <a class="edit-btn"
                               href="edit.php?id=<?php echo (int)$row['id']; ?>">
                                Edit
                            </a>

                            <br><br>

                            <a href="view_assignments.php?delete=<?php echo (int)$row['id']; ?>"
                               onclick="return confirm('Are you sure you want to delete this assignment?');">
                                <button type="button" class="delete-btn">
                                    Delete
                                </button>
                            </a>
                        </td>
                    </tr>

                <?php } ?>

            <?php } else { ?>

                <tr>
                    <td colspan="7" class="empty">
                        No assignments found. Add a new assignment to get started!
                    </td>
                </tr>

            <?php } ?>

            </tbody>
        </table>

    </div>

</div>

<div class="footer">
    Student Assignment Management System
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
