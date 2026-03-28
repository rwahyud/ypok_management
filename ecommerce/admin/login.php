<?php
session_start();

// Database Connection
$conn = mysqli_connect('localhost', 'root', '', 'ecommerce_db');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];
    
    if (!empty($username) && !empty($password)) {
        $query = "SELECT * FROM admin WHERE username = '$username'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $admin = mysqli_fetch_assoc($result);
            
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['message'] = 'Welcome back, ' . $admin['username'] . '!';
                header("Location: index.php");
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        $error = 'Please fill in all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - FashionStore</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            position: relative;
            overflow: hidden;
        }
        .login-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="text-center mb-4">
                        <a href="../index.php" class="logo-text text-white text-decoration-none" style="font-size: 2.5rem;">
                            <span class="logo-fashion">Fashion</span><span class="logo-store">Store</span>
                        </a>
                    </div>
                    
                    <div class="card login-card">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <div class="bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
                                    <i class="fas fa-user-shield fa-2x text-white"></i>
                                </div>
                                <h2 class="fw-bold mb-2">Admin Login</h2>
                                <p class="text-muted">Sign in to manage your store</p>
                            </div>

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-user me-2" style="color: var(--primary-color);"></i>Username
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           name="username" 
                                           placeholder="Enter your username"
                                           required 
                                           autofocus>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-lock me-2" style="color: var(--primary-color);"></i>Password
                                    </label>
                                    <input type="password" 
                                           class="form-control form-control-lg" 
                                           name="password" 
                                           placeholder="Enter your password"
                                           required>
                                </div>

                                <div class="d-grid gap-2 mb-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard
                                    </button>
                                </div>

                                <div class="text-center">
                                    <a href="../index.php" class="text-decoration-none" style="color: var(--primary-color);">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Shop
                                    </a>
                                </div>
                            </form>

                            <hr class="my-4">

                            <div class="alert mb-0 small" style="background-color: var(--pink-light); border: none;">
                                <i class="fas fa-info-circle me-2" style="color: var(--primary-color);"></i>
                                <strong>Demo Credentials:</strong><br>
                                Username: <code>admin</code> | Password: <code>admin123</code>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-white">
                            <i class="fas fa-shield-alt me-2"></i>
                            Secure admin access protected by encryption
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
