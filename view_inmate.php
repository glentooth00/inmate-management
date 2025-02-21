<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php';

// Check if an ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid inmate ID.";
    exit;
}

$inmate_id = (int) $_GET['id']; // Ensure it's an integer

// Fetch inmate details
$stmt = $conn->prepare("SELECT * FROM inmates WHERE id = :id");
$stmt->bindParam(':id', $inmate_id, PDO::PARAM_INT);
$stmt->execute();
$inmate = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if inmate exists
if (!$inmate) {
    echo "Inmate not found.";
    exit;
}

// Handle form submission (update inmate details)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = in_array($_POST['gender'], ['Male', 'Female', 'Other']) ? $_POST['gender'] : 'Male';
    $crime = $_POST['crime'];
    $sentence_duration = $_POST['sentence_duration'];
    $date_of_entry = $_POST['date_of_entry'];
    $release_date = $_POST['release_date'];
    $cell_number = $_POST['cell_number'];
    $health_status = $_POST['health_status'];
    $behavior_record = $_POST['behavior_record'];
    
    // Handle image upload
    $imagePath = $inmate['image']; // Default to old image
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["image"]["name"]);
        $imagePath = $targetDir . $fileName;
        
        // Validate file type and size
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $_SESSION['error'] = "Invalid image type. Only JPG and PNG allowed.";
            header("Location: view_inmate.php?id=" . $inmate_id);
            exit;
        }
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) { // 2MB limit
            $_SESSION['error'] = "Image size too large. Max 2MB.";
            header("Location: view_inmate.php?id=" . $inmate_id);
            exit;
        }

        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
    }

    // Update query
    $updateStmt = $conn->prepare("UPDATE inmates 
        SET name = ?, age = ?, gender = ?, crime = ?, sentence_duration = ?, date_of_entry = ?, release_date = ?, cell_number = ?, health_status = ?, behavior_record = ?, image = ? 
        WHERE id = ?");
    
    if ($updateStmt->execute([$name, $age, $gender, $crime, $sentence_duration, $date_of_entry, $release_date, $cell_number, $health_status, $behavior_record, $imagePath, $inmate_id])) {
        $_SESSION['success'] = "Inmate updated successfully!";
        header("Location: view_inmate.php?id=" . $inmate_id);
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
    <title>View Inmate Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function enableEdit() {
            document.querySelectorAll('.form-control').forEach(field => field.removeAttribute('readonly'));
            document.getElementById('gender').removeAttribute('disabled');
            document.getElementById('image').removeAttribute('disabled');
            document.getElementById('saveBtn').style.display = 'inline-block';
            document.getElementById('editBtn').style.display = 'none';
        }
    </script>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container mt-5">
        <h2 class="mb-4">Inmate Details</h2>
        <a href="manage_inmates.php" class="btn btn-secondary mb-3">Back to List</a>

        <div class="card shadow p-4">
            <div class="row">
                <!-- Inmate Image -->
                <div class="col-md-4 text-center">
                    <img src="<?= htmlspecialchars($inmate['image']); ?>" class="img-fluid rounded" alt="Inmate Image" style="max-height: 300px; object-fit: cover;">
                </div>

                <!-- Inmate Details -->
                <div class="col-md-8">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($inmate['name']); ?>" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Age</label>
                                <input type="text" name="age" class="form-control" value="<?= htmlspecialchars($inmate['age']); ?>" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" id="gender" class="form-control" disabled>
                                    <option value="Male" <?= ($inmate['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?= ($inmate['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?= ($inmate['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Crime Committed</label>
                                <input type="text" name="crime" class="form-control" value="<?= htmlspecialchars($inmate['crime']); ?>" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sentence Duration (Years)</label>
                                <input type="text" name="sentence_duration" class="form-control" value="<?= htmlspecialchars($inmate['sentence_duration']); ?>" readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Entry</label>
                                <input type="date" name="date_of_entry" class="form-control" value="<?= htmlspecialchars($inmate['date_of_entry']); ?>" readonly>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Health Status</label>
                                <textarea name="health_status" class="form-control" rows="3" readonly><?= htmlspecialchars($inmate['health_status']); ?></textarea>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Upload New Image (Optional)</label>
                                <input type="file" name="image" class="form-control" id="image" disabled>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-warning" id="editBtn" onclick="enableEdit()">Edit</button>
                            <button type="submit" class="btn btn-success" id="saveBtn" style="display:none;">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
