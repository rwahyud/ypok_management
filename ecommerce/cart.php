<?php
require_once 'config.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id]['quantity'] = (int)$quantity;
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
        $_SESSION['message'] = 'Cart updated successfully!';
    }
    
    if (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['message'] = 'Item removed from cart!';
    }
    
    if (isset($_POST['clear_cart'])) {
        unset($_SESSION['cart']);
        $_SESSION['message'] = 'Cart cleared!';
    }
    
    redirect('cart.php');
}

$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - FashionStore</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main style="background-color: #fafafa; padding: 40px 0;">
        <div class="container">
            <h2 class="fw-bold mb-4">Shopping Cart</h2>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                        <h4 class="mb-3">Your cart is empty</h4>
                        <p class="text-muted mb-4">Add some products to your cart to continue shopping</p>
                        <a href="index.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <form method="POST">
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead style="background-color: #fafafa;">
                                                <tr>
                                                    <th class="border-0">Product</th>
                                                    <th class="border-0">Price</th>
                                                    <th class="border-0">Quantity</th>
                                                    <th class="border-0">Subtotal</th>
                                                    <th class="border-0">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($cart_items as $product_id => $item): 
                                                    $subtotal = $item['price'] * $item['quantity'];
                                                    $total += $subtotal;
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <?php if (!empty($item['image']) && file_exists('uploads/' . $item['image'])): ?>
                                                                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" 
                                                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;"
                                                                         class="me-3">
                                                                <?php else: ?>
                                                                    <div class="bg-light d-flex align-items-center justify-content-center me-3" 
                                                                         style="width: 80px; height: 80px; border-radius: 8px;">
                                                                        <i class="fas fa-image text-muted"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                                <div>
                                                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="fw-bold"><?php echo format_rupiah($item['price']); ?></td>
                                                        <td>
                                                            <input type="number" class="form-control" 
                                                                   name="quantity[<?php echo $product_id; ?>]" 
                                                                   value="<?php echo $item['quantity']; ?>"
                                                                   min="1" max="<?php echo $item['stock']; ?>"
                                                                   style="width: 80px;">
                                                        </td>
                                                        <td class="fw-bold" style="color: var(--primary-color);">
                                                            <?php echo format_rupiah($subtotal); ?>
                                                        </td>
                                                        <td>
                                                            <button type="submit" name="remove_item" 
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    onclick="return confirm('Remove this item?')">
                                                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="p-3 border-top d-flex justify-content-between">
                                        <button type="submit" name="clear_cart" class="btn btn-outline-danger"
                                                onclick="return confirm('Clear all items from cart?')">
                                            <i class="fas fa-trash me-2"></i>Clear Cart
                                        </button>
                                        <button type="submit" name="update_cart" class="btn btn-primary">
                                            <i class="fas fa-sync me-2"></i>Update Cart
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white py-3">
                                <h5 class="mb-0 fw-bold">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Subtotal:</span>
                                    <span class="fw-bold"><?php echo format_rupiah($total); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
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
                                    <a href="checkout.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                                    </a>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0" style="background-color: var(--pink-light);">
                            <div class="card-body text-center">
                                <i class="fas fa-shield-alt fa-2x mb-2" style="color: var(--primary-color);"></i>
                                <h6 class="fw-bold mb-2">Safe & Secure</h6>
                                <p class="small mb-0 text-muted">Your payment information is encrypted and secure</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
