<?php
require_once 'config.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
        // In a real application, you would send email here
        // For demo, just show success message
        $success_message = "Thank you for contacting us! We'll get back to you soon.";
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - FashionStore</title>
    
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
            <h1 class="fw-bold mb-2" style="font-size: 3rem;">Contact Us</h1>
            <p class="mb-0" style="font-size: 1.1rem;">We'd love to hear from you</p>
        </div>
    </div>

    <!-- Contact Content -->
    <main style="background-color: #fafafa; padding: 80px 0;">
        <div class="container">
            <div class="row g-4">
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm" style="background-color: #fff;">
                        <div class="card-body p-5">
                            <div class="mb-4">
                                <h2 class="fw-bold mb-2">Send Us a Message</h2>
                                <p class="text-muted">Fill out the form below and we'll get back to you as soon as possible.</p>
                            </div>

                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo $success_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $error_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Your Name</label>
                                        <input type="text" class="form-control form-control-lg" name="name" placeholder="John Doe" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Your Email</label>
                                        <input type="email" class="form-control form-control-lg" name="email" placeholder="john@example.com" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Subject</label>
                                        <input type="text" class="form-control form-control-lg" name="subject" placeholder="How can we help you?" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Message</label>
                                        <textarea class="form-control form-control-lg" name="message" rows="6" placeholder="Write your message here..." required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-lg px-5">
                                            <i class="fas fa-paper-plane me-2"></i>Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4">
                    <!-- Contact Details -->
                    <div class="card border-0 shadow-sm mb-4" style="background-color: #fff;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Contact Information</h5>
                            
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle" 
                                         style="width: 50px; height: 50px; background-color: var(--pink-light);">
                                        <i class="fas fa-map-marker-alt" style="color: var(--primary-color);"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold mb-1">Address</h6>
                                    <p class="text-muted mb-0 small">123 Fashion Street, Jakarta 12345, Indonesia</p>
                                </div>
                            </div>

                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle" 
                                         style="width: 50px; height: 50px; background-color: var(--pink-light);">
                                        <i class="fas fa-phone" style="color: var(--primary-color);"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold mb-1">Phone</h6>
                                    <p class="text-muted mb-0 small">+62 812-3456-7890</p>
                                </div>
                            </div>

                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle" 
                                         style="width: 50px; height: 50px; background-color: var(--pink-light);">
                                        <i class="fas fa-envelope" style="color: var(--primary-color);"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold mb-1">Email</h6>
                                    <p class="text-muted mb-0 small">support@fashionstore.com</p>
                                </div>
                            </div>

                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle" 
                                         style="width: 50px; height: 50px; background-color: var(--pink-light);">
                                        <i class="fas fa-clock" style="color: var(--primary-color);"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="fw-bold mb-1">Working Hours</h6>
                                    <p class="text-muted mb-0 small">Mon - Fri: 9:00 AM - 6:00 PM<br>Sat - Sun: 10:00 AM - 4:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
                        <div class="card-body p-4 text-center text-white">
                            <h5 class="fw-bold mb-3">Follow Us</h5>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="btn btn-light rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fab fa-facebook-f" style="color: var(--primary-color);"></i>
                                </a>
                                <a href="#" class="btn btn-light rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fab fa-twitter" style="color: var(--primary-color);"></i>
                                </a>
                                <a href="#" class="btn btn-light rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fab fa-instagram" style="color: var(--primary-color);"></i>
                                </a>
                                <a href="#" class="btn btn-light rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                    <i class="fab fa-linkedin-in" style="color: var(--primary-color);"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Section -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126920.24154132634!2d106.68942995!3d-6.2293867!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945e34b9d%3A0x5371bf0fdad786a2!2sJakarta%2C%20Indonesia!5e0!3m2!1sen!2sus!4v1609459200000!5m2!1sen!2sus" 
                                width="100%" 
                                height="450" 
                                style="border:0; border-radius: 10px;" 
                                allowfullscreen="" 
                                loading="lazy">
                            </iframe>
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
