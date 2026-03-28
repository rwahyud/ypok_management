<?php
require_once 'config.php';

$product_id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

if (empty($product_id)) {
    redirect('index.php');
}

$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = '$product_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    redirect('index.php');
}

$product = mysqli_fetch_assoc($result);

// Get comments
$comments_query = "SELECT * FROM comments WHERE product_id = '$product_id' ORDER BY created_at DESC";
$comments_result = mysqli_query($conn, $comments_query);

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = array(
            'name' => $product['name'],
            'price' => $product['price'],
            'image' => $product['image'],
            'quantity' => $quantity,
            'stock' => $product['stock']
        );
    }
    
    $_SESSION['message'] = 'Product added to cart successfully!';
    redirect('cart.php');
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    $name = clean_input($_POST['comment_name']);
    $email = clean_input($_POST['comment_email']);
    $comment = clean_input($_POST['comment_text']);
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    
    if (!empty($name) && !empty($comment)) {
        $insert_query = "INSERT INTO comments (product_id, name, email, comment, rating) 
                        VALUES ('$product_id', '$name', '$email', '$comment', '$rating')";
        mysqli_query($conn, $insert_query);
        redirect('product_detail.php?id=' . $product_id);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - FashionStore</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main style="background-color: #fafafa; padding: 40px 0;">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="index.php?category=<?php echo $product['category_id']; ?>">
                        <?php echo htmlspecialchars($product['category_name']); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>

            <!-- Product Detail -->
            <div class="row g-4 mb-5">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <?php if (!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="img-fluid" style="width: 100%; height: 500px; object-fit: cover; border-radius: 10px;">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center bg-light" style="height: 500px; border-radius: 10px;">
                                    <i class="fas fa-image fa-5x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <span class="badge" style="background-color: var(--pink-light); color: var(--primary-color);">
                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                </span>
                            </div>

                            <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>

                            <div class="mb-3">
                                <div class="product-rating">
                                    <?php
                                    $rating_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM comments WHERE product_id = '$product_id'";
                                    $rating_result = mysqli_query($conn, $rating_query);
                                    $rating_data = mysqli_fetch_assoc($rating_result);
                                    $avg_rating = round($rating_data['avg_rating']);
                                    $total_reviews = $rating_data['total'];
                                    
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $avg_rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                    <span class="ms-2 text-muted">(<?php echo $total_reviews; ?> reviews)</span>
                                </div>
                            </div>

                            <h2 class="fw-bold mb-4" style="color: var(--primary-color); font-size: 2rem;">
                                <?php echo format_rupiah($product['price']); ?>
                            </h2>

                            <div class="mb-4">
                                <h5 class="fw-bold mb-2">Description:</h5>
                                <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                            </div>

                            <div class="mb-4">
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Stock:</strong> 
                                        <?php if ($product['stock'] > 0): ?>
                                            <span class="text-success"><?php echo $product['stock']; ?> available</span>
                                        <?php else: ?>
                                            <span class="text-danger">Out of stock</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-6">
                                        <strong>Category:</strong> <?php echo htmlspecialchars($product['category_name']); ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($product['stock'] > 0): ?>
                                <form method="POST">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Quantity:</label>
                                        <input type="number" class="form-control" name="quantity" value="1" 
                                               min="1" max="<?php echo $product['stock']; ?>" style="width: 120px;">
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i>This product is currently out of stock
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold">Customer Reviews (<?php echo $total_reviews; ?>)</h4>
                </div>
                <div class="card-body p-4">
                    <!-- Review Form -->
                    <div class="mb-4 p-4" style="background-color: var(--pink-light); border-radius: 10px;">
                        <h5 class="fw-bold mb-3">Write a Review</h5>
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="comment_name" placeholder="Your Name" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="email" class="form-control" name="comment_email" placeholder="Your Email">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold">Rating:</label>
                                    <div class="rating-input">
                                        <input type="radio" name="rating" value="5" id="star5" checked>
                                        <label for="star5"><i class="fas fa-star"></i></label>
                                        <input type="radio" name="rating" value="4" id="star4">
                                        <label for="star4"><i class="fas fa-star"></i></label>
                                        <input type="radio" name="rating" value="3" id="star3">
                                        <label for="star3"><i class="fas fa-star"></i></label>
                                        <input type="radio" name="rating" value="2" id="star2">
                                        <label for="star2"><i class="fas fa-star"></i></label>
                                        <input type="radio" name="rating" value="1" id="star1">
                                        <label for="star1"><i class="fas fa-star"></i></label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <textarea class="form-control" name="comment_text" rows="4" placeholder="Write your review..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="submit_comment" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Review
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Reviews List -->
                    <?php if (mysqli_num_rows($comments_result) > 0): ?>
                        <?php while ($comment = mysqli_fetch_assoc($comments_result)): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="bg-gradient rounded-circle d-flex align-items-center justify-content-center me-3"
                                         style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($comment['name']); ?></h6>
                                        <small class="text-muted"><?php echo date('F d, Y', strtotime($comment['created_at'])); ?></small>
                                    </div>
                                </div>
                                <div class="product-rating mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $comment['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No reviews yet. Be the first to review this product!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
