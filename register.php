<?php
session_start();
include 'db.php';

// Redirect logged-in students to the dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name == "" || $email == "" || $password == "") {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";
        $messageType = "error";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO users (name, email, password)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            $message = "Registration successful! You can now log in.";
            $messageType = "success";
        } elseif ($stmt->errno == 1062) {
            $message = "This email is already registered.";
            $messageType = "error";
        } else {
            $message = "Registration failed. Please try again.";
            $messageType = "error";
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

    <title>Student Registration</title>

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
            margin: 40px auto;
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
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            line-height: 1.5;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .error {
            background: #ffebee;
            color: #c62828;
        }

        .login {
            text-align: center;
            margin-top: 22px;
            color: #555;
            line-height: 1.6;
        }

        .login a {
            color: #6a1b9a;
            font-weight: bold;
            text-decoration: none;
        }

        .login a:hover {
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
    <p>Create an account to manage your assignments</p>
</div>

<div class="container">

    <h2>Student Registration</h2>

    <p class="description">
        Create your account to get started.
    </p>

    <?php if ($message != "") { ?>
        <p class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <form method="POST" action="register.php">

        <label for="name">Full Name</label>
        <input
            type="text"
            id="name"
            name="name"
            placeholder="Enter your name"
            maxlength="100"
            autocomplete="name"
            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
            required
        >

        <label for="email">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="Enter your email"
            maxlength="255"
            autocomplete="email"
            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
            required
        >

        <label for="password">Create Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="At least 8 characters"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <button type="submit">Create Account</button>

    </form>

    <p class="login">
        Already have an account?
        <a href="login.php">Login here</a>
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
