<?php
$servername = "localhost";
$username = "root";
$password = "root";
$database = "student_assignment_db";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
