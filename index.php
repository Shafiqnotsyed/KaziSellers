<?php
session_start();

// Redirect to home if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: pages/home.php");
    exit;
}

$pageTitle = "KaziSellers - Modern E-Commerce Platform";
$cssPath = "assets/css/styles.css";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo $cssPath; ?>">
</head>
<body>

<header>
    <div class="container clearfix">
        <div id="logo">
            <i class="fas fa-store"></i> KaziSellers
        </div>
        <nav>
            <ul class="nav-links">
                <li><a href="pages/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="pages/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
            </ul>
        </nav>
    </div>
</header>

<main class="container-fluid">
    <!-- Hero Section -->
    <div class="welcome-container">
        <div class="container">
            <h1 class="welcome-title">
                <i class="fas fa-store"></i> <span class="greeting-text">Welcome</span> to KaziSellers
            </h1>
            <p class="welcome-subtitle">
                South Africa's modern marketplace where you can both buy and sell with ease
            </p>
            
            <div class="row justify-content-center mt-5">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-lg" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
                        <div class="card-body p-5">
                            <div class="row text-center">
                                <div class="col-md-6 mb-4 mb-md-0">
                                    <i class="fas fa-shopping-cart fa-3x text-primary mb-3"></i>
                                    <h4 class="text-dark">Buy Anything</h4>
                                    <p class="text-muted">Browse thousands of items from fellow students across South Africa</p>
                                    <a href="pages/login.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-search"></i> Start Shopping
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <i class="fas fa-plus-circle fa-3x text-success mb-3"></i>
                                    <h4 class="text-dark">Sell Anything</h4>
                                    <p class="text-muted">List your items and reach locals nationwide in minutes</p>
                                    <a href="pages/register.php" class="btn btn-success btn-lg">
                                        <i class="fas fa-plus"></i> Start Selling
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container my-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="text-white">Why Choose KaziSellers?</h2>
                <p class="text-light">Built by students, for locals</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow h-100" style="background: rgba(255, 255, 255, 0.95);">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Safe & Secure</h5>
                        <p class="card-text text-muted">
                            Built with student safety in mind. Trade with confidence in your campus community.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow h-100" style="background: rgba(255, 255, 255, 0.95);">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-mobile-alt fa-3x text-info mb-3"></i>
                        <h5 class="card-title">Mobile Friendly</h5>
                        <p class="card-text text-muted">
                            Perfect for on-the-go trading. List items and browse from anywhere on campus.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow h-100" style="background: rgba(255, 255, 255, 0.95);">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-heart fa-3x text-danger mb-3"></i>
                        <h5 class="card-title">Student Focused</h5>
                        <p class="card-text text-muted">
                            Made specifically for the South African student community. No fees, just trading.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Categories Preview -->
    <div class="container my-5">
        <div class="row">
            <div class="col-12 text-center mb-4">
                <h3 class="text-white">Popular Categories</h3>
            </div>
        </div>
        
        <div class="row g-3 justify-content-center">
            <div class="col-6 col-md-3 col-lg-2">
                <div class="category-card text-decoration-none">
                    <div class="category-icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <div class="category-name">Electronics</div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="category-card text-decoration-none">
                    <div class="category-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="category-name">Books</div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="category-card text-decoration-none">
                    <div class="category-icon">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="category-name">Clothing</div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="category-card text-decoration-none">
                    <div class="category-icon">
                        <i class="fas fa-couch"></i>
                    </div>
                    <div class="category-name">Furniture</div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="category-card text-decoration-none">
                    <div class="category-icon">
                        <i class="fas fa-futbol"></i>
                    </div>
                    <div class="category-name">Sports</div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="category-card text-decoration-none">
                    <div class="category-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="category-name">Other</div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="pages/register.php" class="btn btn-secondary btn-lg">
                Join KaziSellers Today <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="card border-0 shadow-lg" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px);">
                    <div class="card-body p-5">
                        <h3 class="text-white mb-3">Ready to Start Trading?</h3>
                        <p class="text-light mb-4">
                            Join thousands of locals already buying and selling on KaziSellers
                        </p>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                            <a href="pages/register.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Create Account
                            </a>
                            <a href="pages/login.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="mt-5 py-4" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); color: white;">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-store"></i> KaziSellers</h5>
                <p class="mb-2">South Africa's premier locals marketplace</p>
                <p class="small">Connecting locals across the country for safe, easy trading</p>
            </div>
            <div class="col-md-3">
                <h6>Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="pages/register.php" class="text-light text-decoration-none">Join Us</a></li>
                    <li><a href="pages/login.php" class="text-light text-decoration-none">Login</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h6>Support</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-light text-decoration-none">Help Center</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Safety Tips</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Contact Us</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4" style="border-color: rgba(255, 255, 255, 0.2);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="mb-0 small">&copy; <?php echo date('Y'); ?> KaziSellers. Built by Shafiq Syed Alli</p>
            </div>
            <div class="col-md-4 text-md-end">
                <small class="text-muted">
                    <i class="fas fa-heart text-danger"></i> Made with love in South Africa
                </small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JavaScript -->
<script src="assets/js/app.js"></script>

</body>
</html>
