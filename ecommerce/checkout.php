<?php
require_once 'config.php';

// Check if user is logged in
if (!is_user_logged_in()) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    $_SESSION['message'] = 'Please login to continue with checkout';
    redirect('login.php');
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$user = get_logged_user();
$cart_items = $_SESSION['cart'];
$total = 0;

foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $customer_name = clean_input($_POST['customer_name']);
    $customer_email = clean_input($_POST['customer_email']);
    $customer_phone = clean_input($_POST['customer_phone']);
    $customer_address = clean_input($_POST['customer_address']);
    $notes = clean_input($_POST['notes']);
    
    if (!empty($customer_name) && !empty($customer_email) && !empty($customer_phone) && !empty($customer_address)) {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert order
            $user_id = $_SESSION['user_id'];
            $insert_order = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, total_amount, notes, status) 
                            VALUES ('$user_id', '$customer_name', '$customer_email', '$customer_phone', '$customer_address', '$total', '$notes', 'pending')";
            
            if (mysqli_query($conn, $insert_order)) {
                $order_id = mysqli_insert_id($conn);
                
                // Insert order items
                foreach ($cart_items as $product_id => $item) {
                    $quantity = $item['quantity'];
                    $price = $item['price'];
                    
                    $insert_item = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                   VALUES ('$order_id', '$product_id', '$quantity', '$price')";
                    mysqli_query($conn, $insert_item);
                    
                    // Update stock
                    $update_stock = "UPDATE products SET stock = stock - $quantity WHERE id = '$product_id'";
                    mysqli_query($conn, $update_stock);
                }
                
                // Commit transaction
                mysqli_commit($conn);
                
                // Clear cart
                unset($_SESSION['cart']);
                
                // Redirect to success page
                $_SESSION['order_id'] = $order_id;
                redirect('order_success.php');
            } else {
                throw new Exception('Failed to create order');
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Failed to process order. Please try again.';
        }
    } else {
        $error = 'Please fill in all required fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - FashionStore</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main style="background-color: #fafafa; padding: 40px 0;">
        <div class="container">
            <h2 class="fw-bold mb-4">Checkout</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-user me-2" style="color: var(--primary-color);"></i>Customer Information
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST" id="checkoutForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Full Name *</label>
                                        <input type="text" class="form-control form-control-lg" name="customer_name" 
                                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Email Address *</label>
                                        <input type="email" class="form-control form-control-lg" name="customer_email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Phone Number *</label>
                                        <input type="tel" class="form-control form-control-lg" name="customer_phone" 
                                               value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Shipping Address *</label>
                                        <textarea class="form-control form-control-lg" name="customer_address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Order Notes (Optional)</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Any special instructions for delivery..."></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-credit-card me-2" style="color: var(--primary-color);"></i>Payment Method
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert mb-0" style="background-color: var(--pink-light); border: none;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave fa-2x me-3" style="color: var(--primary-color);"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Cash on Delivery (COD)</h6>
                                        <p class="mb-0 small text-muted">Pay with cash when you receive your order</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold">Order Summary</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <?php foreach ($cart_items as $product_id => $item): 
                                    $subtotal = $item['price'] * $item['quantity'];
                                ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <?php if (!empty($item['image']) && file_exists('uploads/' . $item['image'])): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                                 class="me-3">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 60px; height: 60px; border-radius: 8px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 small"><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <small class="text-muted"><?php echo $item['quantity']; ?> × <?php echo format_rupiah($item['price']); ?></small>
                                        </div>
                                        <span class="fw-bold"><?php echo format_rupiah($subtotal); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span class="fw-bold"><?php echo format_rupiah($total); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span class="fw-bold text-success">FREE</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold" style="color: var(--primary-color); font-size: 1.5rem;">
                                    <?php echo format_rupiah($total); ?>
                                </span>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" form="checkoutForm" name="place_order" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Place Order
                                </button>
                                <a href="cart.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Cart
                                </a>
                            </div>

                            <div class="alert mt-3 mb-0 small" style="background-color: var(--pink-light); border: none;">
                                <i class="fas fa-info-circle me-2" style="color: var(--primary-color);"></i>
                                By placing your order, you agree to our terms and conditions.
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
