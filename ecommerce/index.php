<?php
require_once 'config.php';

// Get categories for filter
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

// Filter by category and search
$where = "WHERE 1=1";
$category_filter = isset($_GET['category']) ? clean_input($_GET['category']) : '';
$search_query = isset($_GET['search']) ? clean_input($_GET['search']) : '';

if (!empty($category_filter)) {
    $where .= " AND p.category_id = '$category_filter'";
}

if (!empty($search_query)) {
    $where .= " AND (p.name LIKE '%$search_query%' OR p.description LIKE '%$search_query%')";
}

// Get products
$products_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   $where 
                   ORDER BY p.created_at DESC";
$products_result = mysqli_query($conn, $products_query);

// Count cart items
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FashionStore - Premium Online Shopping</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Hero Slider Section -->
    <section class="hero-section position-relative overflow-hidden">
        <div class="hero-slider">
            <!-- Slide 1: Season Sale -->
            <div class="hero-slide active">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <span class="limited-badge">Limited Time</span>
                            <h1 class="hero-title">
                                Season <span class="highlight">Sale</span> Up To<br>50% Off
                            </h1>
                            <p class="text-muted mb-4" style="font-size: 1.1rem;">
                                Quis ipsum suspendisse ultrices blandit. Nulla quis lorem ut<br>libero malesuada feugiat.
                            </p>
                            <a href="#products" class="btn btn-primary btn-lg">
                                Shop Now <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                        <div class="col-lg-6">
                            <div class="hero-image position-relative">
                                <div class="discount-badge">
                                    <div class="percentage">50%</div>
                                    <div class="off-text">OFF</div>
                                </div>
                                <img src="https://images.unsplash.com/photo-1603808033176-5a1aa78e2f01?w=600&h=600&fit=crop" 
                                     alt="Fashion Boots" style="max-width: 500px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2: New Collection -->
            <div class="hero-slide">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <span class="limited-badge" style="background-color: #ff6b6b;">New Arrival</span>
                            <h1 class="hero-title">
                                Spring <span class="highlight">Collection</span><br>2026
                            </h1>
                            <p class="text-muted mb-4" style="font-size: 1.1rem;">
                                Discover the latest trends in fashion.<br>Fresh styles for the new season.
                            </p>
                            <a href="#products" class="btn btn-primary btn-lg">
                                Explore Now <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                        <div class="col-lg-6">
                            <div class="hero-image position-relative">
                                <div class="discount-badge" style="background: linear-gradient(135deg, #ff6b6b, #ee5a6f);">
                                    <div class="percentage" style="font-size: 2rem;">NEW</div>
                                    <div class="off-text">2026</div>
                                </div>
                                <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=600&h=600&fit=crop" 
                                     alt="New Collection" style="max-width: 500px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3: Free Shipping -->
            <div class="hero-slide">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <span class="limited-badge" style="background-color: #4CAF50;">Special Offer</span>
                            <h1 class="hero-title">
                                <span class="highlight">Free Shipping</span><br>On Orders Over $50
                            </h1>
                            <p class="text-muted mb-4" style="font-size: 1.1rem;">
                                Shop your favorite items now and get<br>free delivery straight to your door!
                            </p>
                            <a href="#products" class="btn btn-primary btn-lg">
                                Start Shopping <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                        <div class="col-lg-6">
                            <div class="hero-image position-relative">
                                <div class="discount-badge" style="background: linear-gradient(135deg, #4CAF50, #45a049);">
                                    <i class="fas fa-truck fa-3x mb-2"></i>
                                    <div class="off-text">FREE</div>
                                </div>
                                <img src="https://images.unsplash.com/photo-1607522370275-f14206abe5d3?w=600&h=600&fit=crop" 
                                     alt="Free Shipping" style="max-width: 500px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 4: Featured Product -->
            <div class="hero-slide">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-6">
                            <span class="limited-badge" style="background-color: #ff9800;">Best Seller</span>
                            <h1 class="hero-title">
                                Premium <span class="highlight">Leather</span><br>Jackets
                            </h1>
                            <p class="text-muted mb-4" style="font-size: 1.1rem;">
                                Handcrafted with genuine leather.<br>Style meets durability and comfort.
                            </p>
                            <div class="mb-3">
                                <span class="text-muted" style="text-decoration: line-through; font-size: 1.2rem;">Rp 1.500.000</span>
                                <span class="fw-bold ms-3" style="color: var(--primary-color); font-size: 2rem;">Rp 1.200.000</span>
                            </div>
                            <a href="#products" class="btn btn-primary btn-lg">
                                View Details <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                        <div class="col-lg-6">
                            <div class="hero-image position-relative">
                                <div class="discount-badge" style="background: linear-gradient(135deg, #ff9800, #f57c00);">
                                    <div class="percentage">20%</div>
                                    <div class="off-text">OFF</div>
                                </div>
                                <img src="https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&h=600&fit=crop" 
                                     alt="Leather Jacket" style="max-width: 500px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slider Indicators -->
        <div class="slider-indicators">
            <button class="indicator active" data-slide="0"></button>
            <button class="indicator" data-slide="1"></button>
            <button class="indicator" data-slide="2"></button>
            <button class="indicator" data-slide="3"></button>
        </div>

        <!-- Navigation Arrows -->
        <button class="slider-arrow prev" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slider-arrow next" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </section>

    <!-- Products Section -->
    <section class="py-5" id="products" style="background-color: #fff;">
        <div class="container">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="text-center mb-5">
                <h2 class="fw-bold mb-3" style="font-size: 2.5rem;">Featured Products</h2>
                <p class="text-muted">Discover our latest collection</p>
            </div>

            <?php if (!empty($search_query) || !empty($category_filter)): ?>
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5>
                            <?php if (!empty($search_query)): ?>
                                Search results for: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong>
                            <?php endif; ?>
                        </h5>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <?php if (mysqli_num_rows($products_result) > 0): ?>
                    <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <div class="product-card">
                                <div class="product-image">
                                    <?php if (!empty($product['image']) && file_exists('uploads/' . $product['image'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-img" style="height: 280px;">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['stock'] <= 5 && $product['stock'] > 0): ?>
                                        <span class="product-badge">Low Stock</span>
                                    <?php elseif ($product['stock'] == 0): ?>
                                        <span class="product-badge bg-danger">Out of Stock</span>
                                    <?php endif; ?>
                                    
                                    <div class="product-overlay">
                                        <div class="product-actions">
                                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-light btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="product-info">
                                    <div class="product-rating mb-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <h5 class="product-title">
                                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="product-price"><?php echo format_rupiah($product['price']); ?></span>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No products found</h4>
                            <p class="text-muted">Try adjusting your search or filter criteria</p>
                            <a href="index.php" class="btn btn-primary">View All Products</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <a href="index.php" class="logo-text text-white text-decoration-none">
                        <span class="logo-fashion">Fashion</span><span class="logo-store">Store</span>
                    </a>
                    <p class="text-white-50 mt-3">
                        Your trusted partner for premium fashion. Quality products, affordable prices.
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php">Home</a></li>
                        <li class="mb-2"><a href="about.php">About</a></li>
                        <li class="mb-2"><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact Us</h5>
                    <p class="text-white-50">
                        <i class="fas fa-envelope me-2"></i>support@fashionstore.com<br>
                        <i class="fas fa-phone me-2"></i>+62 812-3456-7890<br>
                        <i class="fas fa-map-marker-alt me-2"></i>Jakarta, Indonesia
                    </p>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <div class="text-center text-white-50">
                <p class="mb-0">&copy; 2026 FashionStore. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-slide');
        const indicators = document.querySelectorAll('.indicator');
        let autoSlideInterval;

        function showSlide(index) {
            // Remove active class from all slides and indicators
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));

            // Handle wrap around
            if (index >= slides.length) {
                currentSlide = 0;
            } else if (index < 0) {
                currentSlide = slides.length - 1;
            } else {
                currentSlide = index;
            }

            // Add active class to current slide and indicator
            slides[currentSlide].classList.add('active');
            indicators[currentSlide].classList.add('active');
        }

        function changeSlide(direction) {
            showSlide(currentSlide + direction);
            resetAutoSlide();
        }

        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            startAutoSlide();
        }

        function startAutoSlide() {
            autoSlideInterval = setInterval(() => {
                showSlide(currentSlide + 1);
            }, 10000); // Change slide every 10 seconds
        }

        // Indicator click handlers
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                showSlide(index);
                resetAutoSlide();
            });
        });

        // Start auto-slide on page load
        startAutoSlide();

        // Pause on hover
        const heroSection = document.querySelector('.hero-section');
        heroSection.addEventListener('mouseenter', () => {
            clearInterval(autoSlideInterval);
        });
        heroSection.addEventListener('mouseleave', () => {
            startAutoSlide();
        });
    </script>
</body>
</html>
