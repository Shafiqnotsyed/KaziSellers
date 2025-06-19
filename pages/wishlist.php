<?php
session_start();
include("../includes/db.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// FIXED: Handle wishlist operations with proper POST-redirect-GET pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Handle add to wishlist
    if ($_POST['action'] === 'add_to_wishlist' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        
        // Check if product exists and is not user's own
        $checkQuery = "SELECT seller_id FROM products WHERE id = ? AND seller_id != ?";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "ii", $product_id, $user_id);
        mysqli_stmt_execute($checkStmt);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($checkStmt)) > 0) {
            $insertQuery = "INSERT IGNORE INTO favorites (user_id, product_id) VALUES (?, ?)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "ii", $user_id, $product_id);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $_SESSION['wishlist_success'] = "Item added to wishlist!";
            } else {
                $_SESSION['wishlist_error'] = "Failed to add item to wishlist.";
            }
        } else {
            $_SESSION['wishlist_error'] = "Cannot add this item to wishlist.";
        }
        
        // Always redirect to prevent resubmission
        header("Location: wishlist.php");
        exit;
    }

    // Handle remove from wishlist
    if ($_POST['action'] === 'remove_from_wishlist' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        
        $deleteQuery = "DELETE FROM favorites WHERE user_id = ? AND product_id = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($deleteStmt, "ii", $user_id, $product_id);
        
        if (mysqli_stmt_execute($deleteStmt)) {
            $_SESSION['wishlist_success'] = "Item removed from wishlist!";
        } else {
            $_SESSION['wishlist_error'] = "Failed to remove item from wishlist.";
        }
        
        // Redirect to prevent resubmission
        header("Location: wishlist.php");
        exit;
    }
}

// FIXED: Get and clear session messages
$success_message = $_SESSION['wishlist_success'] ?? null;
$error_message = $_SESSION['wishlist_error'] ?? null;
unset($_SESSION['wishlist_success'], $_SESSION['wishlist_error']);

// Get wishlist items
$wishlistQuery = "SELECT p.*, pi.image_path, u.username as seller_name, u.first_name, u.last_name, f.created_at as added_date
                  FROM favorites f
                  JOIN products p ON f.product_id = p.id
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
                  JOIN users u ON p.seller_id = u.id
                  WHERE f.user_id = ?
                  ORDER BY f.created_at DESC";
$wishlistStmt = mysqli_prepare($conn, $wishlistQuery);
mysqli_stmt_bind_param($wishlistStmt, "i", $user_id);
mysqli_stmt_execute($wishlistStmt);
$wishlistItems = mysqli_stmt_get_result($wishlistStmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - KaziSellers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <div class="wishlist-page-wrapper">
        <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-heart text-danger"></i> My Wishlist
                        </h1>
                        <p class="text-muted">Items you've saved for later</p>
                    </div>
                    <div>
                        <a href="home.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Back to Browse
                        </a>
                        <a href="cart.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> View Cart
                        </a>
                    </div>
                </div>

                <!-- FIXED: Success/Error Messages from Session -->
                <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Wishlist Items -->
                <?php if (mysqli_num_rows($wishlistItems) > 0): ?>
                <div class="row">
                    <?php while ($item = mysqli_fetch_assoc($wishlistItems)): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card wishlist-item h-100">
                            <!-- Product Image -->
                            <div class="card-img-top position-relative">
                                <?php 
                                // FIXED: Proper image path construction for wishlist
                                $wishlistImagePath = 'https://via.placeholder.com/300x200?text=No+Image';
                                if (!empty($item['image_path'])) {
                                    $imagePath = $item['image_path'];
                                    if (str_starts_with($imagePath, 'http')) {
                                        $wishlistImagePath = $imagePath;
                                    } else {
                                        if (str_starts_with($imagePath, 'uploads/')) {
                                            $wishlistImagePath = $imagePath;
                                        } else {
                                            $wishlistImagePath = '../uploads/' . $imagePath;
                                        }
                                    }
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($wishlistImagePath); ?>" 
                                     class="img-fluid" 
                                     alt="<?php echo htmlspecialchars($item['title']); ?>"
                                     style="height: 200px; object-fit: cover;"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                                
                                <!-- Status Badge -->
                                <div class="position-absolute top-0 end-0 m-2">
                                    <?php if ($item['availability'] === 'available'): ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php elseif ($item['availability'] === 'reserved'): ?>
                                        <span class="badge bg-warning">Reserved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Sold</span>
                                    <?php endif; ?>
                                </div>

                                <!-- Remove Button -->
                                <div class="position-absolute top-0 start-0 m-2">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove_from_wishlist">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Remove from wishlist?')"
                                                title="Remove from wishlist">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <a href="product-details.php?id=<?php echo $item['id']; ?>" 
                                       class="text-decoration-none">
                                        <?php echo htmlspecialchars($item['title']); ?>
                                    </a>
                                </h5>
                                
                                <p class="card-text text-muted small">
                                    <?php echo htmlspecialchars(substr($item['description'], 0, 100)); ?>
                                    <?php echo strlen($item['description']) > 100 ? '...' : ''; ?>
                                </p>

                                <!-- Price -->
                                <div class="price-section mb-2">
                                    <span class="price-tag">R<?php echo number_format($item['price'], 2); ?></span>
                                    <?php if ($item['condition_type'] !== 'good'): ?>
                                    <small class="text-muted">
                                        (<?php echo ucfirst(str_replace('_', ' ', $item['condition_type'])); ?>)
                                    </small>
                                    <?php endif; ?>
                                </div>

                                <!-- Seller Info -->
                                <div class="seller-info mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> 
                                        by <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                    </small>
                                </div>

                                <!-- Added Date -->
                                <div class="added-date mb-3 mt-auto">
                                    <small class="text-muted">
                                        <i class="fas fa-heart"></i> 
                                        Added <?php echo date('M j, Y', strtotime($item['added_date'])); ?>
                                    </small>
                                </div>

                                <!-- Action Buttons -->
                                <div class="action-buttons mt-auto">
                                    <?php if ($item['availability'] === 'available'): ?>
                                    <div class="d-grid gap-2">
                                        <a href="product-details.php?id=<?php echo $item['id']; ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                        <a href="chat.php?product_id=<?php echo $item['id']; ?>&recipient_id=<?php echo $item['seller_id']; ?>" 
                                           class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-comment"></i> Contact Seller
                                        </a>
                                    </div>
                                    <?php else: ?>
                                    <div class="d-grid">
                                        <button class="btn btn-secondary" disabled>
                                            <i class="fas fa-ban"></i> Not Available
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <!-- Empty Wishlist -->
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mb-4">
                        <i class="fas fa-heart fa-5x text-muted"></i>
                    </div>
                    <h3 class="text-muted">Your Wishlist is Empty</h3>
                    <p class="text-muted mb-4">Save items you love to buy them later</p>
                    <div class="empty-actions">
                        <a href="home.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-search"></i> Browse Products
                        </a>
                        <a href="categories.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-th-large"></i> View Categories
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
    /* Wishlist Page Enhanced Styling */
    body {
        background: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .wishlist-page-wrapper {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 2rem 0;
    }

    .page-title {
        color: #2c3e50;
        font-weight: 800;
        margin-bottom: 0.5rem;
        font-size: 2.5rem;
    }

    .page-title i {
        color: #dc3545;
        margin-right: 0.5rem;
    }

    .wishlist-page-wrapper .text-muted {
        color: #6c757d !important;
        font-size: 1.1rem;
    }

    .wishlist-item {
        transition: all 0.3s ease;
        border: none;
        border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        background: white;
        position: relative;
    }

    .wishlist-item:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .wishlist-item .card-img-top {
        position: relative;
        overflow: hidden;
        height: 220px;
    }

    .wishlist-item .card-img-top img {
        border-radius: 20px 20px 0 0;
        transition: transform 0.3s ease;
        height: 100%;
        width: 100%;
        object-fit: cover;
    }

    .wishlist-item:hover .card-img-top img {
        transform: scale(1.1);
    }

    .wishlist-item .card-body {
        padding: 1.5rem;
        background: linear-gradient(to bottom, white 0%, #f8f9fa 100%);
    }

    .card-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1rem;
        line-height: 1.3;
    }

    .card-title a {
        color: #2c3e50;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .card-title a:hover {
        color: #ff6b6b;
    }

    .price-section {
        background: linear-gradient(45deg, #ff6b6b, #feca57);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1rem;
    }

    .price-tag {
        font-size: 1.5rem;
        font-weight: 800;
        text-shadow: 0 2px 4px rgba(255, 107, 107, 0.3);
    }

    .seller-info,
    .added-date {
        background: rgba(255, 107, 107, 0.1);
        border-radius: 12px;
        padding: 0.75rem;
        margin-bottom: 1rem;
        border-left: 4px solid #ff6b6b;
    }

    .seller-info i,
    .added-date i {
        color: #ff6b6b;
        margin-right: 0.5rem;
    }

    /* Action Buttons */
    .action-buttons .btn {
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        padding: 0.75rem 1.5rem;
    }

    .action-buttons .btn-primary {
        background: linear-gradient(45deg, #ff6b6b, #feca57);
        border: none;
        color: white;
    }

    .action-buttons .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        background: linear-gradient(45deg, #ff5722, #ff9800);
    }

    .action-buttons .btn-outline-success {
        border: 2px solid #28a745;
        color: #28a745;
        background: transparent;
    }

    .action-buttons .btn-outline-success:hover {
        background: #28a745;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
    }

    /* Status Badges */
    .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        backdrop-filter: blur(10px);
    }

    .badge.bg-success {
        background: linear-gradient(45deg, #28a745, #20c997) !important;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    }

    .badge.bg-warning {
        background: linear-gradient(45deg, #ffc107, #fd7e14) !important;
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    }

    .badge.bg-danger {
        background: linear-gradient(45deg, #dc3545, #c82333) !important;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
    }

    /* Remove Button */
    .wishlist-item .btn-danger {
        background: linear-gradient(45deg, #dc3545, #c82333);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .wishlist-item .btn-danger:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    }

    /* Header Actions */
    .wishlist-page-wrapper .btn-outline-primary {
        border-radius: 8px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .wishlist-page-wrapper .btn-primary {
        border-radius: 8px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .wishlist-page-wrapper .btn-outline-primary:hover,
    .wishlist-page-wrapper .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Empty State Styling */
    .empty-state {
        min-height: 500px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: white;
        border-radius: 24px;
        margin: 2rem 0;
        padding: 4rem 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .empty-icon {
        opacity: 0.2;
        margin-bottom: 2rem;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }

    .empty-state h3 {
        font-size: 2rem;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .empty-state p {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 2rem;
    }

    .empty-actions .btn {
        border-radius: 12px;
        padding: 1rem 2rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        margin: 0.5rem;
    }

    .empty-actions .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .empty-actions .btn-primary {
        background: linear-gradient(45deg, #ff6b6b, #feca57);
        border: none;
    }

    .empty-actions .btn-outline-primary {
        border: 2px solid #ff6b6b;
        color: #ff6b6b;
    }

    .empty-actions .btn-outline-primary:hover {
        background: #ff6b6b;
        color: white;
    }

    /* Alert Styling */
    .alert {
        border: none;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
        font-weight: 500;
    }

    .alert-success {
        background: linear-gradient(45deg, #d4edda, #c3e6cb);
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background: linear-gradient(45deg, #f8d7da, #f5c6cb);
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    /* Card Text Enhancement */
    .card-text {
        font-size: 0.9rem;
        line-height: 1.6;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .wishlist-page-wrapper {
            padding: 1rem 0;
        }

        .page-title {
            font-size: 2rem;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
        
        .empty-actions .btn {
            display: block;
            width: 100%;
            margin: 0.5rem 0;
        }

        .empty-state {
            margin: 1rem 0;
            padding: 2rem 1rem;
        }

        .wishlist-item {
            margin-bottom: 1.5rem;
        }

        .wishlist-item .card-body {
            padding: 1rem;
        }
    }

    @media (max-width: 576px) {
        .page-title {
            font-size: 1.75rem;
        }

        .wishlist-page-wrapper {
            padding: 0.5rem 0;
        }

        .container {
            padding: 0 0.5rem;
        }

        .price-tag {
            font-size: 1.25rem;
        }
    }

    /* Additional Visual Enhancements */
    .wishlist-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(45deg, #ff6b6b, #feca57);
        border-radius: 20px 20px 0 0;
    }

    .card-body {
        position: relative;
        z-index: 1;
    }

    .seller-info small,
    .added-date small {
        font-weight: 500;
        color: #495057;
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
