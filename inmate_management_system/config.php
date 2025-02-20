<?php
$host = "localhost"; // Change if needed
$dbname = "inmate_management"; // Replace with your DB name
$username = "root"; // Your MySQL username
$password = ""; // Your MySQL password

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
