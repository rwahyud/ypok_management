<?php
require_once '../config.php';

if (!is_admin_logged_in()) {
    redirect('../login.php');
}

// Handle Status Update
if (isset($_POST['update_status'])) {
    $order_id = clean_input($_POST['order_id']);
    $status = clean_input($_POST['status']);
    
    mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id='$order_id'");
    $_SESSION['message'] = 'Order status updated successfully!';
    redirect('orders.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = clean_input($_GET['delete']);
    
    // Delete order items first
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id = '$id'");
    // Delete order
    mysqli_query($conn, "DELETE FROM orders WHERE id = '$id'");
    
    $_SESSION['message'] = 'Order deleted successfully!';
    redirect('orders.php');
}

// Get all orders
$orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Panel</title>
    
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
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags me-1"></i>Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="orders.php">
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

            <h1 class="display-5 fw-bold mb-4">
                <i class="fas fa-shopping-cart me-3"></i>Manage Orders
            </h1>

            <!-- Orders Table -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($orders) > 0): ?>
                                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                                        <tr>
                                            <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($order['customer_email']); ?><br>
                                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($order['customer_phone']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($order['customer_address'], 0, 50)); ?>...</small>
                                            </td>
                                            <td class="fw-bold text-success"><?php echo format_rupiah($order['total_amount']); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm" 
                                                            onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="d-none"></button>
                                                </form>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-info me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#orderModal<?php echo $order['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="?delete=<?php echo $order['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Delete this order?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>

                                        <!-- Order Details Modal -->
                                        <div class="modal fade" id="orderModal<?php echo $order['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            Order Details #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-4">
                                                            <div class="col-md-6">
                                                                <h6 class="fw-bold">Customer Information</h6>
                                                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                                                <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6 class="fw-bold">Order Information</h6>
                                                                <p class="mb-1"><strong>Date:</strong> <?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
                                                                <p class="mb-1"><strong>Status:</strong> 
                                                                    <span class="badge bg-<?php 
                                                                        echo $order['status'] == 'pending' ? 'warning' : 
                                                                            ($order['status'] == 'completed' ? 'success' : 'info'); 
                                                                    ?>">
                                                                        <?php echo ucfirst($order['status']); ?>
                                                                    </span>
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <div class="mb-4">
                                                            <h6 class="fw-bold">Shipping Address</h6>
                                                            <p><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                                                        </div>

                                                        <?php if (!empty($order['notes'])): ?>
                                                            <div class="mb-4">
                                                                <h6 class="fw-bold">Order Notes</h6>
                                                                <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                                                            </div>
                                                        <?php endif; ?>

                                                        <h6 class="fw-bold mb-3">Order Items</h6>
                                                        <?php
                                                        $items = mysqli_query($conn, "SELECT oi.*, p.name as product_name 
                                                                                     FROM order_items oi 
                                                                                     JOIN products p ON oi.product_id = p.id 
                                                                                     WHERE oi.order_id = '{$order['id']}'");
                                                        ?>
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Product</th>
                                                                    <th>Price</th>
                                                                    <th>Quantity</th>
                                                                    <th>Subtotal</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                                                                    <tr>
                                                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                                        <td><?php echo format_rupiah($item['price']); ?></td>
                                                                        <td><?php echo $item['quantity']; ?></td>
                                                                        <td><?php echo format_rupiah($item['price'] * $item['quantity']); ?></td>
                                                                    </tr>
                                                                <?php endwhile; ?>
                                                            </tbody>
                                                            <tfoot>
                                                                <tr>
                                                                    <td colspan="3" class="text-end fw-bold">Total:</td>
                                                                    <td class="fw-bold text-success">
                                                                        <?php echo format_rupiah($order['total_amount']); ?>
                                                                    </td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <i class="fas fa-shopping-cart fa-3x mb-3 d-block"></i>
                                            No orders found
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
