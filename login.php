<?php
session_start();
include 'db.php';

// Redirect logged-in students to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT id, name, email, password FROM users WHERE email = ?"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            $stmt->close();
            header("Location: dashboard.php");
            exit();
        }
    }

    $message = "Incorrect email or password.";
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Login</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f0ff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #6a1b9a, #8e24aa);
            color: white;
            text-align: center;
            padding: 25px 15px;
        }

        .header h1 {
            margin: 0 0 8px;
            font-size: 27px;
        }

        .header p {
            margin: 0;
            font-size: 15px;
        }

        .container {
            background: white;
            width: calc(100% - 30px);
            max-width: 420px;
            margin: 45px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 18px #d1c4e9;
        }

        h2 {
            text-align: center;
            color: #6a1b9a;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .description {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 18px;
            margin-bottom: 7px;
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
            padding: 13px;
            margin-top: 25px;
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
            background: #ffebee;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
        }

        .register {
            text-align: center;
            margin-top: 22px;
            color: #555;
            line-height: 1.6;
        }

        .register a {
            color: #6a1b9a;
            font-weight: bold;
            text-decoration: none;
        }

        .register a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            color: #777;
            padding: 15px;
            margin-top: auto;
            font-size: 13px;
        }

        @media (max-width: 500px) {
            .header h1 {
                font-size: 22px;
            }

            .container {
                padding: 23px;
                margin: 30px auto;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>Student Assignment Management System</h1>
    <p>Organize your assignments and manage your deadlines</p>
</div>

<div class="container">

    <h2>Student Login</h2>

    <p class="description">
        Welcome back! Log in to manage your assignments.
    </p>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST" action="login.php">

        <label for="email">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your email"
            autocomplete="email"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            autocomplete="current-password"
            required
        >

        <button type="submit">Login</button>

    </form>

    <p class="register">
        Don't have an account?
        <a href="register.php">Register here</a>
    </p>

</div>

<div class="footer">
    Student Assignment Management System
</div>

</body>
</html>

<?php
$conn->close();
?>
