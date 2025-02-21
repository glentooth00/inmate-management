<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php';

// Check if 'id' is set in URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Request");
}

$id = $_GET['id'];

// Fetch inmate details
$stmt = $conn->prepare("SELECT * FROM inmates WHERE id = ?");
$stmt->execute([$id]);
$inmate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inmate) {
    die("Inmate not found!");
}

// Handle form submission (update inmate details)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $crime = $_POST['crime'];
    $image = $_FILES['image'];

    // Handle image upload
    if (!empty($image['name'])) {
        $targetDir = "uploads/";
        $imagePath = $targetDir . basename($image["name"]);
        move_uploaded_file($image["tmp_name"], $imagePath);
    } else {
        $imagePath = $inmate['image']; // Keep old image if not updated
    }

    // Update query
    $updateStmt = $conn->prepare("UPDATE inmates SET name = ?, crime = ?, image = ? WHERE id = ?");
    if ($updateStmt->execute([$name, $crime, $imagePath, $id])) {
        $_SESSION['success'] = "Inmate updated successfully!";
        header("Location: manage_inmates.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to update inmate.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Inmate</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container mt-5">
        <h2>Edit Inmate</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($inmate['name']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Crime</label>
                <input type="text" name="crime" value="<?= htmlspecialchars($inmate['crime']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Image</label>
                <input type="file" name="image" class="form-control">
                <img src="<?= htmlspecialchars($inmate['image']); ?>" class="img-thumbnail mt-2" width="150">
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="manage_inmates.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
