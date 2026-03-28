<!-- Top Header -->
<div class="top-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-3">
                <a href="index.php" class="logo-text">
                    <span class="logo-fashion">Fashion</span><span class="logo-store">Store</span>
                </a>
            </div>
            <div class="col-lg-6">
                <form action="index.php" method="GET" class="search-box">
                    <input type="text" name="search" placeholder="Search for products..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <div class="col-lg-3">
                <div class="header-icons justify-content-end">
                    <?php if (is_user_logged_in()): 
                        $user = get_logged_user();
                    ?>
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="far fa-user"></i>
                                <span><?php echo htmlspecialchars($user['name']); ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="my_orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php">
                            <i class="far fa-user"></i>
                            <span>Login</span>
                        </a>
                    <?php endif; ?>
                    <a href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Cart</span>
                        <?php 
                        $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
                        if ($cart_count > 0): ?>
                            <span class="icon-badge"><?php echo $cart_count; ?></span>
                        <?php else: ?>
                            <span class="icon-badge">0</span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Navigation -->
<nav class="main-navbar">
    <div class="container">
        <ul class="navbar-nav d-flex flex-row justify-content-center">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>" href="about.php">About</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    Category
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="index.php">All Products</a></li>
                    <?php 
                    $categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                    while ($cat = mysqli_fetch_assoc($categories)): 
                    ?>
                        <li><a class="dropdown-item" href="index.php?category=<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a></li>
                    <?php endwhile; ?>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>" href="contact.php">Contact</a>
            </li>
            <?php if (is_admin_logged_in()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- Promo Banner -->
<div class="promo-banner">
    🚚 Free shipping on orders over $50
</div>
