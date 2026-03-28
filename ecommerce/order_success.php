<?php
require_once 'config.php';

if (!isset($_SESSION['order_id'])) {
    redirect('index.php');
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_id']);

$query = "SELECT * FROM orders WHERE id = '$order_id'";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

$items_query = "SELECT oi.*, p.name as product_name 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - FashionStore</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <style>
        .success-icon {
            animation: scaleIn 0.5s ease-in-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="py-5" style="background-color: #fafafa;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Success Message -->
                    <div class="card border-0 shadow-sm mb-4" style="background-color: #fff;">
                        <div class="card-body text-center py-5">
                            <div class="success-icon mb-4">
                                <i class="fas fa-check-circle fa-5x" style="color: var(--success-color);"></i>
                            </div>
                            <h1 class="fw-bold mb-3" style="font-size: 2rem;">Order Placed Successfully!</h1>
                            <p class="text-muted mb-4" style="font-size: 1.1rem;">
                                Thank you for your order. We've received it and will process it soon.
                            </p>
                            <div class="alert d-inline-block mb-0" style="background-color: var(--pink-light); border: none;">
                                <strong>Order ID:</strong> <span style="color: var(--primary-color); font-size: 1.2rem; font-weight: 700;">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="card border-0 shadow-sm mb-4" style="background-color: #fff;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-info-circle me-2" style="color: var(--primary-color);"></i>Order Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="mb-2 fw-bold">Customer Name:</p>
                                    <p class="text-muted"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2 fw-bold">Email:</p>
                                    <p class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2 fw-bold">Phone:</p>
                                    <p class="text-muted"><?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2 fw-bold">Order Date:</p>
                                    <p class="text-muted"><?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="col-12">
                                    <p class="mb-2 fw-bold">Shipping Address:</p>
                                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
                                </div>
                                <?php if (!empty($order['notes'])): ?>
                                    <div class="col-12">
                                        <p class="mb-2 fw-bold">Order Notes:</p>
                                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card border-0 shadow-sm mb-4" style="background-color: #fff;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-box me-2" style="color: var(--primary-color);"></i>Order Items
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead style="background-color: #fafafa;">
                                        <tr>
                                            <th class="border-0">Product</th>
                                            <th class="border-0">Price</th>
                                            <th class="border-0">Quantity</th>
                                            <th class="border-0">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td><?php echo format_rupiah($item['price']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td class="fw-bold"><?php echo format_rupiah($item['price'] * $item['quantity']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot style="background-color: #fafafa;">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold border-0">Total:</td>
                                            <td class="fw-bold border-0" style="color: var(--primary-color); font-size: 1.3rem;">
                                                <?php echo format_rupiah($order['total_amount']); ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="card border-0 shadow-sm" style="background-color: var(--pink-light);">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">
                                <i class="fas fa-lightbulb me-2" style="color: var(--warning-color);"></i>What's Next?
                            </h5>
                            <ul class="mb-4">
                                <li class="mb-2">You'll receive a confirmation email at <strong><?php echo htmlspecialchars($order['customer_email']); ?></strong></li>
                                <li class="mb-2">We'll process your order and prepare it for shipping</li>
                                <li class="mb-2">You'll be notified when your order is on the way</li>
                                <li class="mb-0">Payment will be collected upon delivery (COD)</li>
                            </ul>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                <a href="index.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-home me-2"></i>Back to Home
                                </a>
                                <a href="javascript:window.print()" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-print me-2"></i>Print Receipt
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
