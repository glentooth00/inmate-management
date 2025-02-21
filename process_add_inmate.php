<?php
session_start();
include 'config.php'; // Connect to the database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $crime = $_POST['crime'];
    $sentence_duration = $_POST['sentence_duration'];
    $date_of_entry = $_POST['date_of_entry'];
    $release_date = $_POST['release_date'];
    $cell_number = $_POST['cell_number'];
    $health_status = $_POST['health_status'];
    $behavior_record = $_POST['behavior_record'];

    // Image upload handling
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create directory if it doesn't exist
    }

    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . time() . "_" . $image_name; // Unique file name
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if it's an actual image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO inmates 
            (name, age, gender, crime, sentence_duration, date_of_entry, release_date, cell_number, health_status, behavior_record, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $age, $gender, $crime, $sentence_duration, $date_of_entry, $release_date, $cell_number, $health_status, $behavior_record, $target_file]);

        $_SESSION['success'] = "New inmate added successfully.";
    } else {
        $_SESSION['error'] = "Error uploading image.";
    }

    header("Location: manage_inmates.php");
    exit;
}
?>
