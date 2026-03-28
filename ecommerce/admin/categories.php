<?php
require_once '../config.php';

if (!is_admin_logged_in()) {
    redirect('../login.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    mysqli_query($conn, "DELETE FROM categories WHERE id = '$id'");
    $_SESSION['message'] = 'Category deleted successfully!';
    redirect('categories.php');
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    $id = isset($_POST['id']) ? clean_input($_POST['id']) : '';
    
    if (!empty($id)) {
        // Update
        mysqli_query($conn, "UPDATE categories SET name='$name', description='$description' WHERE id='$id'");
        $_SESSION['message'] = 'Category updated successfully!';
    } else {
        // Insert
        mysqli_query($conn, "INSERT INTO categories (name, description) VALUES ('$name', '$description')");
        $_SESSION['message'] = 'Category added successfully!';
    }
    
    redirect('categories.php');
}

// Get category for editing
$edit_category = null;
if (isset($_GET['edit'])) {
    $id = clean_input($_GET['edit']);
    $edit_category = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE id = '$id'"));
}

// Get all categories with product count
$categories = mysqli_query($conn, "SELECT c.*, COUNT(p.id) as product_count 
                                   FROM categories c 
                                   LEFT JOIN products p ON c.id = p.category_id 
                                   GROUP BY c.id 
                                   ORDER BY c.name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-user-shield me-2"></i>Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-chart-line me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box me-1"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="categories.php">
                            <i class="fas fa-tags me-1"></i>Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart me-1"></i>Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php" target="_blank">
                            <i class="fas fa-store me-1"></i>View Shop
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-white-50">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['admin_username']; ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-5 bg-light min-vh-100">
        <div class="container-fluid">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-5 fw-bold">
                    <i class="fas fa-tags me-3"></i>Manage Categories
                </h1>
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#categoryModal">
                    <i class="fas fa-plus me-2"></i>Add New Category
                </button>
            </div>

            <!-- Categories Grid -->
            <div class="row g-4">
                <?php if (mysqli_num_rows($categories) > 0): ?>
                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title fw-bold mb-1">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </h5>
                                            <span class="badge bg-info">
                                                <?php echo $category['product_count']; ?> products
                                            </span>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" 
                                                    data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="?edit=<?php echo $category['id']; ?>">
                                                        <i class="fas fa-edit me-2"></i>Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" 
                                                       href="?delete=<?php echo $category['id']; ?>"
                                                       onclick="return confirm('Delete this category?')">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted">
                                        <?php echo htmlspecialchars($category['description']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-tags fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No categories found</h4>
                                <p class="text-muted">Add your first category to organize your products!</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-<?php echo $edit_category ? 'edit' : 'plus'; ?> me-2"></i>
                        <?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <?php if ($edit_category): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?php echo $edit_category ? 'Update' : 'Add'; ?> Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($edit_category): ?>
    <script>
        // Auto open modal if editing
        var myModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        myModal.show();
    </script>
    <?php endif; ?>
</body>
</html>
