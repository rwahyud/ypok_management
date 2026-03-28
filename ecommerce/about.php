<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - FashionStore</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <!-- Page Header -->
    <div class="page-header" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); padding: 80px 0;">
        <div class="container text-center text-white">
            <h1 class="fw-bold mb-2" style="font-size: 3rem;">About Us</h1>
            <p class="mb-0" style="font-size: 1.1rem;">Learn more about FashionStore</p>
        </div>
    </div>

    <!-- About Content -->
    <main style="background-color: #fafafa;">
        <!-- Our Story -->
        <section class="py-5">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100" style="background-color: #fff;">
                            <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800" alt="Our Store" class="card-img-top" style="height: 400px; object-fit: cover;">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="pe-lg-4">
                            <div class="badge mb-3" style="background-color: var(--pink-light); color: var(--primary-color); font-weight: 600; padding: 8px 20px; border-radius: 50px;">
                                Our Story
                            </div>
                            <h2 class="fw-bold mb-4" style="font-size: 2.5rem; line-height: 1.2;">Welcome to FashionStore</h2>
                            <p class="text-muted mb-3" style="font-size: 1.05rem; line-height: 1.8;">
                                FashionStore was founded with a simple mission: to make fashion accessible to everyone. We believe that style should be effortless, affordable, and sustainable.
                            </p>
                            <p class="text-muted mb-4" style="font-size: 1.05rem; line-height: 1.8;">
                                Since our launch, we've been committed to curating the latest trends and timeless classics, bringing you a carefully selected collection that speaks to every style and occasion.
                            </p>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="p-3 text-center rounded" style="background-color: var(--pink-light);">
                                        <h3 class="fw-bold mb-1" style="color: var(--primary-color); font-size: 2rem;">500+</h3>
                                        <p class="mb-0 small text-muted">Products</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 text-center rounded" style="background-color: var(--pink-light);">
                                        <h3 class="fw-bold mb-1" style="color: var(--primary-color); font-size: 2rem;">10K+</h3>
                                        <p class="mb-0 small text-muted">Happy Customers</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Values -->
        <section class="py-5" style="background-color: #fff;">
            <div class="container">
                <div class="text-center mb-5">
                    <div class="badge mb-3" style="background-color: var(--pink-light); color: var(--primary-color); font-weight: 600; padding: 8px 20px; border-radius: 50px;">
                        Why Choose Us
                    </div>
                    <h2 class="fw-bold mb-3" style="font-size: 2.5rem;">Our Values</h2>
                    <p class="text-muted">What makes FashionStore different</p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-card">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    <i class="fas fa-award fa-2x text-white"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-3">Premium Quality</h4>
                            <p class="text-muted mb-0">We source only the finest materials and work with trusted brands to ensure every product meets our high standards.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-card">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    <i class="fas fa-shipping-fast fa-2x text-white"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-3">Fast Shipping</h4>
                            <p class="text-muted mb-0">Get your orders delivered quickly and safely. Free shipping on orders over $50 across the country.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-card">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    <i class="fas fa-headset fa-2x text-white"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-3">24/7 Support</h4>
                            <p class="text-muted mb-0">Our customer service team is always here to help. Reach out anytime for assistance with your orders.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-card">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    <i class="fas fa-undo-alt fa-2x text-white"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-3">Easy Returns</h4>
                            <p class="text-muted mb-0">Not satisfied? No problem. We offer hassle-free returns within 30 days of purchase.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-card">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    <i class="fas fa-lock fa-2x text-white"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-3">Secure Payment</h4>
                            <p class="text-muted mb-0">Shop with confidence. All transactions are encrypted and your data is protected.</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 text-center p-4 hover-card">
                            <div class="mb-3">
                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" 
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    <i class="fas fa-leaf fa-2x text-white"></i>
                                </div>
                            </div>
                            <h4 class="fw-bold mb-3">Eco-Friendly</h4>
                            <p class="text-muted mb-0">We're committed to sustainability. Our packaging is recyclable and we partner with eco-conscious brands.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Team -->
        <section class="py-5" style="background-color: #fafafa;">
            <div class="container">
                <div class="text-center mb-5">
                    <div class="badge mb-3" style="background-color: var(--pink-light); color: var(--primary-color); font-weight: 600; padding: 8px 20px; border-radius: 50px;">
                        Meet Our Team
                    </div>
                    <h2 class="fw-bold mb-3" style="font-size: 2.5rem;">The People Behind FashionStore</h2>
                    <p class="text-muted">Passionate individuals dedicated to bringing you the best fashion</p>
                </div>

                <div class="row g-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm text-center hover-card">
                            <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Team Member" class="card-img-top">
                            <div class="card-body">
                                <h5 class="fw-bold mb-1">Sarah Johnson</h5>
                                <p class="text-muted small mb-3">Founder & CEO</p>
                                <div class="social-links">
                                    <a href="#" class="text-decoration-none me-2" style="color: var(--primary-color);"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="text-decoration-none me-2" style="color: var(--primary-color);"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="text-decoration-none" style="color: var(--primary-color);"><i class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm text-center hover-card">
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Team Member" class="card-img-top">
                            <div class="card-body">
                                <h5 class="fw-bold mb-1">Michael Chen</h5>
                                <p class="text-muted small mb-3">Creative Director</p>
                                <div class="social-links">
                                    <a href="#" class="text-decoration-none me-2" style="color: var(--primary-color);"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="text-decoration-none me-2" style="color: var(--primary-color);"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="text-decoration-none" style="color: var(--primary-color);"><i class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm text-center hover-card">
                            <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Team Member" class="card-img-top">
                            <div class="card-body">
                                <h5 class="fw-bold mb-1">Emma Davis</h5>
                                <p class="text-muted small mb-3">Head of Marketing</p>
                                <div class="social-links">
                                    <a href="#" class="text-decoration-none me-2" style="color: var(--primary-color);"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="text-decoration-none me-2" style="color: var(--primary-color);"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="text-decoration-none" style="color: var(--primary-color);"><i class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="card border-0 shadow-sm text-center hover-card">
                            <img src="https://randomuser.me/api/portraits/men/46.jpg" alt="Team Member" class="card-img-top">
                            <div class="card-body">
                                <h5 class="fw-bold mb-1">David Wilson</h5>
                                <p class="text-muted small mb-3">Customer Service Lead</p>
                                <div class="social-links">
                                    <a href="#" class="text-decoration-none me-2" style="color: var(--primary-color);"><i class="fab fa-facebook-f"></i></a>
                                    <a href="#" class="text-decoration-none me-2" style="color: var(--primary-color);"><i class="fab fa-twitter"></i></a>
                                    <a href="#" class="text-decoration-none" style="color: var(--primary-color);"><i class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-5" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
            <div class="container text-center text-white">
                <h2 class="fw-bold mb-3" style="font-size: 2.5rem;">Ready to Shop?</h2>
                <p class="mb-4" style="font-size: 1.1rem;">Discover our latest collection and find your perfect style</p>
                <a href="index.php" class="btn btn-lg px-5" style="background-color: #fff; color: var(--primary-color); border-radius: 50px; font-weight: 600;">
                    Browse Products
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .hover-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
        }
    </style>
</body>
</html>
