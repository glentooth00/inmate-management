<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inmate_id'])) {
    $inmate_id = (int) $_POST['inmate_id'];

    // Delete inmate
    $stmt = $conn->prepare("DELETE FROM inmates WHERE id = ?");
    if ($stmt->execute([$inmate_id])) {
        $_SESSION['success'] = "Inmate deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete inmate.";
    }
}

header("Location: manage_inmates.php");
exit;
?>
