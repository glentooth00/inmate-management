<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

include 'config.php'; // Connect to database

// Pagination setup
$items_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure page is at least 1
$offset = ($page - 1) * $items_per_page;

// Fetch inmates with pagination
$stmt = $conn->prepare("SELECT * FROM inmates LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$inmates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total records for pagination
$total_stmt = $conn->query("SELECT COUNT(*) FROM inmates");
$total_rows = (int) $total_stmt->fetchColumn(); // Ensure it's an integer
$total_pages = max(ceil($total_rows / $items_per_page), 1); // Ensure at least 1 page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inmates</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="container mt-5">
        <h2>Manage Inmates</h2>

        <!-- Button to Open Modal -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addInmateModal">Add New Inmate</button>

        <!-- Inmates Display as Cards (5 per row) -->
        <div class="row">
      
        <?php foreach ($inmates as $row): ?>
    <div class="col-md-2 mb-4 d-flex align-items-stretch">
        <div class="card shadow w-100">
            <img src="<?= htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Inmate Image" style="height: 150px; object-fit: cover;">
            <div class="card-body text-center">
                <h6 class="card-title"><?= htmlspecialchars($row['name']); ?></h6>
                <p class="card-text"><strong>Crime:</strong> <?= htmlspecialchars($row['crime']); ?></p>

                <!-- View Button with Eye Icon -->
                <a href="view_inmate.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-eye"></i>
                </a>

                <!-- Edit Button with Pencil Icon -->
                <a href="edit_inmate.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil"></i>
                </a>

                <!-- Delete Button with Trash Icon -->
                <form action="delete_inmate.php" method="post" class="d-inline">
                    <input type="hidden" name="inmate_id" value="<?= $row['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this inmate?');">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>

            </div>
        </div>
    </div>
<?php endforeach; ?>


        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1; ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Add Inmate Modal -->
    <div class="modal fade" id="addInmateModal" tabindex="-1" aria-labelledby="addInmateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addInmateModalLabel">Add New Inmate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="process_add_inmate.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Image Upload -->
                                <div class="mb-3">
                                    <label>Upload Image:</label>
                                    <input type="file" name="image" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Full Name:</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Age:</label>
                                    <input type="number" name="age" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Gender:</label>
                                    <select name="gender" class="form-control" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>

                                <!-- Crime Committed Dropdown -->
                                <div class="mb-3">
                                    <label>Crime Committed:</label>
                                    <select name="crime" class="form-control" required>
                                        <option value="" disabled selected>Select a Crime</option>
                                        <optgroup label="Violent Crimes">
                                            <option value="Murder">Murder</option>
                                            <option value="Assault">Assault</option>
                                            <option value="Robbery">Robbery</option>
                                        </optgroup>
                                        <optgroup label="Property Crimes">
                                            <option value="Burglary">Burglary</option>
                                            <option value="Theft">Theft</option>
                                            <option value="Arson">Arson</option>
                                        </optgroup>
                                        <optgroup label="Drug-Related Crimes">
                                            <option value="Drug Possession">Drug Possession</option>
                                            <option value="Drug Trafficking">Drug Trafficking</option>
                                        </optgroup>
                                        <optgroup label="White Collar Crimes">
                                            <option value="Fraud">Fraud</option>
                                            <option value="Embezzlement">Embezzlement</option>
                                        </optgroup>
                                        <optgroup label="Cyber Crimes">
                                            <option value="Identity Theft">Identity Theft</option>
                                            <option value="Cyber Fraud">Cyber Fraud</option>
                                        </optgroup>
                                        <optgroup label="Other Crimes">
                                            <option value="Human Trafficking">Human Trafficking</option>
                                            <option value="Kidnapping">Kidnapping</option>
                                            <option value="Domestic Violence">Domestic Violence</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Sentence Duration (Years):</label>
                                    <input type="number" name="sentence_duration" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Date of Entry:</label>
                                    <input type="date" name="date_of_entry" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label>Expected Release Date:</label>
                                    <input type="date" name="release_date" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label>Cell Number:</label>
                                    <input type="text" name="cell_number" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label>Health Status:</label>
                                    <textarea name="health_status" class="form-control"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label>Behavior Record:</label>
                                    <textarea name="behavior_record" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer with Save Button -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
