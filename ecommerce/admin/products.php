<?php
require_once '../config.php';

if (!is_admin_logged_in()) {
    redirect('../login.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    
    // Get image filename
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM products WHERE id = '$id'"));
    
    // Delete product
    if (mysqli_query($conn, "DELETE FROM products WHERE id = '$id'")) {
        // Delete image file
        if (!empty($product['image']) && file_exists('../uploads/' . $product['image'])) {
            unlink('../uploads/' . $product['image']);
        }
        $_SESSION['message'] = 'Product deleted successfully!';
    }
    redirect('products.php');
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    $price = clean_input($_POST['price']);
    $stock = clean_input($_POST['stock']);
    $category_id = clean_input($_POST['category_id']);
    $id = isset($_POST['id']) ? clean_input($_POST['id']) : '';
    
    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $image = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/' . $image);
        }
    }
    
    if (!empty($id)) {
        // Update
        $query = "UPDATE products SET name='$name', description='$description', 
                 price='$price', stock='$stock', category_id='$category_id'";
        
        if (!empty($image)) {
            // Delete old image
            $old_product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM products WHERE id = '$id'"));
            if (!empty($old_product['image']) && file_exists('../uploads/' . $old_product['image'])) {
                unlink('../uploads/' . $old_product['image']);
            }
            $query .= ", image='$image'";
        }
        
        $query .= " WHERE id='$id'";
        mysqli_query($conn, $query);
        $_SESSION['message'] = 'Product updated successfully!';
    } else {
        // Insert
        $query = "INSERT INTO products (name, description, price, stock, category_id, image) 
                 VALUES ('$name', '$description', '$price', '$stock', '$category_id', '$image')";
        mysqli_query($conn, $query);
        $_SESSION['message'] = 'Product added successfully!';
    }
    
    redirect('products.php');
}

// Get product for editing
$edit_product = null;
if (isset($_GET['edit'])) {
    $id = clean_input($_GET['edit']);
    $edit_product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id = '$id'"));
}

// Get all products
$products = mysqli_query($conn, "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");

// Get categories for dropdown
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
    
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
                        <a class="nav-link active" href="products.php">
                            <i class="fas fa-box me-1"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
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
                    <i class="fas fa-box me-3"></i>Manage Products
                </h1>
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#productModal">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </button>
            </div>

            <!-- Products Table -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Created</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($products) > 0): ?>
                                    <?php while ($product = mysqli_fetch_assoc($products)): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($product['image']) && file_exists('../uploads/' . $product['image'])): ?>
                                                    <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                                         class="img-fluid rounded" 
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                                <small class="text-muted"><?php echo substr(htmlspecialchars($product['description']), 0, 50); ?>...</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                            </td>
                                            <td class="fw-bold text-success"><?php echo format_rupiah($product['price']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['stock'] > 10 ? 'success' : ($product['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                                    <?php echo $product['stock']; ?> units
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($product['created_at'])); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $product['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $product['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Delete this product?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                                            No products found. Add your first product!
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-<?php echo $edit_product ? 'edit' : 'plus'; ?> me-2"></i>
                        <?php echo $edit_product ? 'Edit Product' : 'Add New Product'; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_product['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo $edit_product ? htmlspecialchars($edit_product['name']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php mysqli_data_seek($categories, 0); ?>
                                    <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                        <option value="<?php echo $category['id']; ?>"
                                                <?php echo ($edit_product && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price (Rp)</label>
                                <input type="number" class="form-control" name="price" 
                                       value="<?php echo $edit_product ? $edit_product['price'] : ''; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock</label>
                                <input type="number" class="form-control" name="stock" 
                                       value="<?php echo $edit_product ? $edit_product['stock'] : ''; ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" required><?php echo $edit_product ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Product Image</label>
                                <input type="file" class="form-control" name="image" accept="image/*">
                                <?php if ($edit_product && !empty($edit_product['image'])): ?>
                                    <small class="text-muted">Current image: <?php echo htmlspecialchars($edit_product['image']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?php echo $edit_product ? 'Update' : 'Add'; ?> Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($edit_product): ?>
    <script>
        // Auto open modal if editing
        var myModal = new bootstrap.Modal(document.getElementById('productModal'));
        myModal.show();
    </script>
    <?php endif; ?>
</body>
</html>
