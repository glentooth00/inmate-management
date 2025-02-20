<?php
session_start();
require 'config.php'; // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=Please fill in all fields.");
        exit;
    }

    // Check if the admin exists
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !password_verify($password, $admin['password'])) {
        header("Location: login.php?error=Invalid email or password.");
        exit;
    }

    // Store user data in session and redirect to dashboard
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    header("Location: dashboard.php");
    exit;
}
?>
