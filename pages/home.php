<?php
session_start();
include("../includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "Home - KaziSellers";
$cssPath = "../assets/css/styles.css";
$isInPages = true;

$userId = $_SESSION['user_id'];

// Get user stats
$userStatsQuery = "SELECT 
    (SELECT COUNT(*) FROM products WHERE seller_id = $userId) as total_listings,
    (SELECT COUNT(*) FROM products WHERE seller_id = $userId AND availability = 'available') as active_listings,
    (SELECT COUNT(*) FROM products WHERE seller_id = $userId AND availability = 'sold') as sold_items,
    (SELECT COUNT(*) FROM messages WHERE receiver_id = $userId AND is_read = 0) as unread_messages";
$userStatsResult = mysqli_query($conn, $userStatsQuery);
$userStats = mysqli_fetch_assoc($userStatsResult);

// Get featured products
$featuredQuery = "SELECT p.*, pi.image_path, u.username as seller_name
                  FROM products p 
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                  LEFT JOIN users u ON p.seller_id = u.id
                  WHERE p.availability = 'available' AND p.seller_id != $userId
                  ORDER BY p.created_at DESC
                  LIMIT 6";
$featuredResult = mysqli_query($conn, $featuredQuery);

// Get categories
$categoryQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name LIMIT 6";
$categories = mysqli_query($conn, $categoryQuery);

include("../components/header.php");
?>

<!-- Enhanced Dashboard Container -->
<div class="dashboard-wrapper">
    <div class="container-fluid px-4">
        
        <!-- Welcome Header with Glassmorphism Effect -->
        <div class="welcome-section mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="welcome-content">
                        <h1 class="welcome-greeting">
                            <span class="greeting-text">Good <?php 
                                $hour = date('H');
                                if ($hour < 12) echo 'Morning';
                                elseif ($hour < 17) echo 'Afternoon'; 
                                else echo 'Evening';
                            ?></span>,
                            <span class="username-highlight"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</span>
                        </h1>
                        <p class="welcome-subtitle">
                            <i class="fas fa-store me-2"></i>
                            Welcome to your KaziSellers dashboard
                        </p>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="quick-stats-mini">
                        <?php if ($userStats['unread_messages'] > 0): ?>
                        <div class="stat-badge unread-messages">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger"><?php echo $userStats['unread_messages']; ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="welcome-avatar">
                            <i class="fas fa-user-circle fa-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card stats-primary">
                    <div class="stats-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number"><?php echo $userStats['active_listings']; ?></h3>
                        <p class="stats-label">Active Listings</p>
                        <span class="stats-sublabel">of <?php echo $userStats['total_listings']; ?> total</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card stats-success">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number"><?php echo $userStats['sold_items']; ?></h3>
                        <p class="stats-label">Items Sold</p>
                        <span class="stats-sublabel">this month</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card stats-info">
                    <div class="stats-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number"><?php echo $userStats['unread_messages']; ?></h3>
                        <p class="stats-label">New Messages</p>
                        <span class="stats-sublabel">unread</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card stats-warning">
                    <div class="stats-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <div class="stats-content">
                        <h3 class="stats-number"><?php echo $userStats['total_listings']; ?></h3>
                        <p class="stats-label">Total Listings</p>
                        <span class="stats-sublabel">all time</span>
                    </div>
                </div>
            </div>
        </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <h3>Quick Actions</h3>
        </div>
        <div class="col-md-3 mb-2">
            <a href="sell.php" class="btn btn-success btn-block w-100">Sell Something</a>
        </div>
        <div class="col-md-3 mb-2">
            <a href="my-listings.php" class="btn btn-info btn-block w-100">My Listings</a>
        </div>
        <div class="col-md-3 mb-2">
            <a href="messages.php" class="btn btn-warning btn-block w-100">Messages</a>
        </div>
        <div class="col-md-3 mb-2">
            <a href="profile.php" class="btn btn-secondary btn-block w-100">My Profile</a>
        </div>
    </div>

    <!-- Categories -->
    <div class="row mb-4">
        <div class="col-12">
            <h3>Browse Categories</h3>
        </div>
        <?php if ($categories && mysqli_num_rows($categories) > 0): ?>
            <?php while ($category = mysqli_fetch_assoc($categories)): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h5><?php echo htmlspecialchars($category['name']); ?></h5>
                        <a href="categories.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-primary">Browse</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p>No categories available</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Featured Products -->
    <div class="row">
        <div class="col-12">
            <h3>Latest Products</h3>
        </div>
        <?php if ($featuredResult && mysqli_num_rows($featuredResult) > 0): ?>
            <?php while ($product = mysqli_fetch_assoc($featuredResult)): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <?php 
                        $imagePath = 'https://via.placeholder.com/300x200?text=No+Image';
                        if (!empty($product['image_path'])) {
                            if (str_starts_with($product['image_path'], 'uploads/')) {
                                $imagePath = '../' . $product['image_path'];
                            } else {
                                $imagePath = '../uploads/' . $product['image_path'];
                            }
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             class="card-img-top" 
                             style="height: 200px; object-fit: cover;"
                             alt="<?php echo htmlspecialchars($product['title']); ?>"
                             onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                            <p class="card-text">R<?php echo number_format($product['price'], 2); ?></p>
                            <small class="text-muted">by <?php echo htmlspecialchars($product['seller_name']); ?></small>
                            <br>
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary mt-2">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center">
                    <p>No products available yet. Be the first to sell something!</p>
                    <a href="sell.php" class="btn btn-primary">List Your First Item</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include("../components/footer.php"); ?>
