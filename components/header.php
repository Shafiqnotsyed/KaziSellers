
<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? ($_SESSION['username'] ?? '') : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'KaziSellers - Student Marketplace'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo isset($cssPath) ? $cssPath : '../assets/css/styles.css'; ?>">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="<?php echo isset($cssPath) ? str_replace('css/styles.css', 'js/app.js', $cssPath) : '../assets/js/app.js'; ?>" as="script">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
</head>
<body>

<header>
    <div class="container clearfix">
        <div id="logo">
            <?php if (isset($isInPages) && $isInPages): ?>
                <!-- We're in pages/ directory -->
                <a href="<?php echo $isLoggedIn ? 'home.php' : '../index.php'; ?>" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-store"></i> KaziSellers
                </a>
            <?php else: ?>
                <!-- We're in root directory -->
                <a href="<?php echo $isLoggedIn ? 'pages/home.php' : 'index.php'; ?>" style="text-decoration: none; color: inherit;">
                    <i class="fas fa-store"></i> KaziSellers
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ($isLoggedIn): ?>
        <nav>
            <ul class="nav-links">
                <?php if (isset($isInPages) && $isInPages): ?>
                    <!-- We're in the pages/ directory -->
                    <li><a href="home.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="sell.php"><i class="fas fa-plus-circle"></i> Sell Something</a></li>
                    <li><a href="categories.php"><i class="fas fa-th-large"></i> Categories</a></li>
                    <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
                    <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                    
                    <!-- Account Dropdown -->
                    <li class="nav-dropdown">
                        <a href="#" class="dropdown-toggle">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
                            <li><a href="my-listings.php"><i class="fas fa-list"></i> My Listings</a></li>
                            <li><a href="messages.php"><i class="fas fa-comments"></i> Messages</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- We're in the root directory (URL)-->
                    <li><a href="pages/home.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="pages/sell.php"><i class="fas fa-plus-circle"></i> Sell Something</a></li>
                    <li><a href="pages/categories.php"><i class="fas fa-th-large"></i> Categories</a></li>
                    <li><a href="pages/wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
                    <li><a href="pages/cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                    
                    <!-- Account Dropdown -->
                    <li class="nav-dropdown">
                        <a href="#" class="dropdown-toggle">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($username); ?>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="pages/profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
                            <li><a href="pages/my-listings.php"><i class="fas fa-list"></i> My Listings</a></li>
                            <li><a href="pages/messages.php"><i class="fas fa-comments"></i> Messages</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a href="pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php else: ?>
        <nav>
            <ul class="nav-links">
                <?php if (isset($isInPages) && $isInPages): ?>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                <?php else: ?>
                    <li><a href="pages/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="pages/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</header>

<main class="container-fluid">
