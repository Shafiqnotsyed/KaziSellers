<?php
session_start();
include("../includes/db.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// FIXED: Handle cart operations with proper POST-redirect-GET pattern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // Handle add to cart
    if ($_POST['action'] === 'add_to_cart' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        
        // Check if product exists, is available, and is not user's own
        $checkQuery = "SELECT seller_id, availability FROM products WHERE id = ? AND seller_id != ? AND availability = 'available'";
        $checkStmt = mysqli_prepare($conn, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "ii", $product_id, $user_id);
        mysqli_stmt_execute($checkStmt);
        
        if (mysqli_num_rows(mysqli_stmt_get_result($checkStmt)) > 0) {
            $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) 
                           ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)";
            $insertStmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($insertStmt, "iii", $user_id, $product_id, $quantity);
            
            if (mysqli_stmt_execute($insertStmt)) {
                $_SESSION['cart_success'] = "Item added to cart!";
            } else {
                $_SESSION['cart_error'] = "Failed to add item to cart.";
            }
        } else {
            $_SESSION['cart_error'] = "Cannot add this item to cart.";
        }
        
        // Always redirect to prevent resubmission
        header("Location: cart.php");
        exit;
    }

    // Handle update cart quantity
    if ($_POST['action'] === 'update_quantity' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = max(1, (int)$_POST['quantity']);
        
        $updateQuery = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $updateStmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($updateStmt, "iii", $quantity, $user_id, $product_id);
        
        if (mysqli_stmt_execute($updateStmt)) {
            $_SESSION['cart_success'] = "Cart updated!";
        } else {
            $_SESSION['cart_error'] = "Failed to update cart.";
        }
        
        // Redirect to prevent resubmission
        header("Location: cart.php");
        exit;
    }

    // Handle remove from cart
    if ($_POST['action'] === 'remove_from_cart' && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        
        $deleteQuery = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteQuery);
        mysqli_stmt_bind_param($deleteStmt, "ii", $user_id, $product_id);
        
        if (mysqli_stmt_execute($deleteStmt)) {
            $_SESSION['cart_success'] = "Item removed from cart!";
        } else {
            $_SESSION['cart_error'] = "Failed to remove item from cart.";
        }
        
        // Redirect to prevent resubmission
        header("Location: cart.php");
        exit;
    }

    // Handle clear cart
    if ($_POST['action'] === 'clear_cart') {
        $clearQuery = "DELETE FROM cart WHERE user_id = ?";
        $clearStmt = mysqli_prepare($conn, $clearQuery);
        mysqli_stmt_bind_param($clearStmt, "i", $user_id);
        
        if (mysqli_stmt_execute($clearStmt)) {
            $_SESSION['cart_success'] = "Cart cleared!";
        } else {
            $_SESSION['cart_error'] = "Failed to clear cart.";
        }
        
        // Redirect to prevent resubmission
        header("Location: cart.php");
        exit;
    }
}

// FIXED: Get and clear session messages
$success_message = $_SESSION['cart_success'] ?? null;
$error_message = $_SESSION['cart_error'] ?? null;
unset($_SESSION['cart_success'], $_SESSION['cart_error']);

// Get cart items
$cartQuery = "SELECT c.*, p.title, p.price, p.availability, p.condition_type, 
                     pi.image_path, u.username as seller_name, u.first_name, u.last_name
              FROM cart c
              JOIN products p ON c.product_id = p.id
              LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
              JOIN users u ON p.seller_id = u.id
              WHERE c.user_id = ?
              ORDER BY c.added_date DESC";
$cartStmt = mysqli_prepare($conn, $cartQuery);
mysqli_stmt_bind_param($cartStmt, "i", $user_id);
mysqli_stmt_execute($cartStmt);
$cartItems = mysqli_stmt_get_result($cartStmt);

// Calculate totals
$totalItems = 0;
$totalPrice = 0;
$cartItemsArray = [];

while ($item = mysqli_fetch_assoc($cartItems)) {
    $cartItemsArray[] = $item;
    $totalItems += $item['quantity'];
    $totalPrice += ($item['price'] * $item['quantity']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - KaziSellers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>
<body>
    <div class="cart-page-wrapper">
        <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-shopping-cart text-primary"></i> My Cart
                        </h1>
                        <p class="text-muted">
                            <?php echo count($cartItemsArray); ?> item<?php echo count($cartItemsArray) != 1 ? 's' : ''; ?> 
                            (<?php echo $totalItems; ?> total)
                        </p>
                    </div>
                    <div>
                        <a href="home.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                        <a href="wishlist.php" class="btn btn-outline-danger">
                            <i class="fas fa-heart"></i> View Wishlist
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

                <?php if (!empty($cartItemsArray)): ?>
                <div class="row">
                    <!-- Cart Items -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Cart Items</h5>
                                <?php if (count($cartItemsArray) > 0): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="clear_cart">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            onclick="return confirm('Clear entire cart?')">
                                        <i class="fas fa-trash"></i> Clear Cart
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($cartItemsArray as $item): ?>
                                <div class="cart-item p-3 border-bottom">
                                    <div class="row align-items-center">
                                        <!-- Product Image - FIXED path handling -->
                                        <div class="col-md-2">
                                            <?php 
                                            // FIXED: Proper image path construction
                                            $cartImagePath = 'https://via.placeholder.com/100x100?text=No+Image';
                                            if (!empty($item['image_path'])) {
                                                if (str_starts_with($item['image_path'], 'http')) {
                                                    $cartImagePath = $item['image_path'];
                                                } else {
                                                    if (str_starts_with($item['image_path'], 'uploads/')) {
                                                        $cartImagePath = $item['image_path'];
                                                    } else {
                                                        $cartImagePath = 'uploads/' . $item['image_path'];
                                                    }
                                                }
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($cartImagePath); ?>" 
                                                 class="img-fluid rounded" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                 style="height: 80px; object-fit: cover; width: 100%;"
                                                 onerror="this.src='https://via.placeholder.com/100x100?text=No+Image'">
                                        </div>

                                        <!-- Product Details -->
                                        <div class="col-md-4">
                                            <h6 class="mb-1">
                                <a href="product-details.php?id=<?php echo $item['product_id']; ?>" 
                                                   class="text-decoration-none">
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                by <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                            </small>
                                            <?php if ($item['condition_type'] !== 'good'): ?>
                                            <br><small class="text-info">
                                                Condition: <?php echo ucfirst(str_replace('_', ' ', $item['condition_type'])); ?>
                                            </small>
                                            <?php endif; ?>
                                            
                                            <!-- Availability Status -->
                                            <?php if ($item['availability'] !== 'available'): ?>
                                            <br><span class="badge bg-warning">
                                                <?php echo ucfirst($item['availability']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Price -->
                                        <div class="col-md-2 text-center">
                                            <div class="price">R<?php echo number_format($item['price'], 2); ?></div>
                                            <small class="text-muted">each</small>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="col-md-2">
                                            <?php if ($item['availability'] === 'available'): ?>
                                            <form method="POST" class="quantity-form">
                                                <input type="hidden" name="action" value="update_quantity">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <div class="text-center">
                                                    <input type="number" name="quantity" class="form-control text-center quantity-input" 
                                                           value="<?php echo $item['quantity']; ?>" min="1" max="10" style="width: 80px; margin: 0 auto;">
                                                </div>
                                                <div class="text-center mt-1">
                                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                                </div>
                                            </form>
                                            <?php else: ?>
                                            <div class="text-center">
                                                <span class="text-muted">Qty: <?php echo $item['quantity']; ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Total & Actions -->
                                        <div class="col-md-2 text-end">
                                            <div class="item-total mb-2">
                                                <strong>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                            </div>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="remove_from_cart">
                                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                        onclick="return confirm('Remove from cart?')"
                                                        title="Remove from cart">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Summary -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Items (<?php echo $totalItems; ?>):</span>
                                    <span>R<?php echo number_format($totalPrice, 2); ?></span>
                                </div>
                                <div class="summary-row d-flex justify-content-between mb-2">
                                    <span>Shipping:</span>
                                    <span class="text-muted">To be calculated</span>
                                </div>
                                <hr>
                                <div class="summary-total d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong class="text-success">R<?php echo number_format($totalPrice, 2); ?></strong>
                                </div>

                                <div class="d-grid gap-2">
                                    <button class="btn btn-success btn-lg" disabled>
                                        <i class="fas fa-credit-card"></i> Proceed to Checkout
                                    </button>
                                    <small class="text-muted text-center">
                                        Checkout functionality coming soon
                                    </small>
                                </div>

                                <hr>
                                
                                <div class="contact-sellers">
                                    <h6>Quick Actions:</h6>
                                    <div class="d-grid gap-2">
                                        <a href="messages.php" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-comments"></i> View Messages
                                        </a>
                                        <a href="home.php" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-plus"></i> Add More Items
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <!-- Empty Cart -->
                <div class="empty-state text-center py-5">
                    <div class="empty-icon mb-4">
                        <i class="fas fa-shopping-cart fa-5x text-muted"></i>
                    </div>
                    <h3 class="text-muted">Your Cart is Empty</h3>
                    <p class="text-muted mb-4">Add some items to get started</p>
                    <div class="empty-actions">
                        <a href="home.php" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-search"></i> Browse Products
                        </a>
                        <a href="wishlist.php" class="btn btn-outline-danger btn-lg">
                            <i class="fas fa-heart"></i> View Wishlist
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
    /* Cart Page Enhanced Styling */
    body {
        background: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .cart-page-wrapper {
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
        color: #007bff;
        margin-right: 0.5rem;
    }

    .cart-page-wrapper .text-muted {
        color: #6c757d !important;
        font-size: 1.1rem;
    }

    .cart-page-wrapper .card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .cart-page-wrapper .card-header {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 1.5rem;
        font-weight: 600;
        font-size: 1.2rem;
    }

    .cart-item {
        transition: all 0.3s ease;
        border-radius: 12px;
        margin: 0.5rem;
        padding: 1.5rem !important;
        background: white;
        border: 1px solid #e9ecef;
    }

    .cart-item:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        border-color: #667eea;
    }

    .cart-item img {
        border-radius: 12px;
        transition: transform 0.3s ease;
        height: 100px !important;
        width: 100% !important;
        object-fit: cover;
    }

    .cart-item:hover img {
        transform: scale(1.05);
    }

    .cart-item h6 a {
        color: #2c3e50;
        font-weight: 700;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .cart-item h6 a:hover {
        color: #667eea;
    }

    .price {
        font-size: 1.3rem;
        font-weight: 700;
        color: #28a745;
        text-shadow: 0 1px 3px rgba(40, 167, 69, 0.3);
    }

    .item-total {
        font-size: 1.3rem;
        color: #2c3e50;
        font-weight: 800;
    }

    .quantity-form .input-group {
        max-width: 120px;
        margin: 0 auto;
    }

    .quantity-form .btn {
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .quantity-form .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .quantity-input {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-weight: 600;
        text-align: center;
    }

    .quantity-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* Order Summary Styling */
    .summary-row {
        font-size: 1rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .summary-total {
        font-size: 1.3rem;
        font-weight: 800;
        padding: 1rem 0;
        background: linear-gradient(45deg, #28a745, #20c997);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .btn-success {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        border-radius: 12px;
        padding: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }

    .btn-success:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }

    .btn-success:disabled {
        background: #6c757d;
        opacity: 0.6;
    }

    /* Action Buttons */
    .btn-outline-primary,
    .btn-outline-danger,
    .btn-outline-secondary {
        border-radius: 12px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
        border-width: 2px;
    }

    .btn-outline-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
    }

    .btn-outline-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
    }

    .btn-outline-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
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
        50% { transform: translateY(-10px); }
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

    /* Badge Styling */
    .badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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

    /* Header Actions */
    .cart-page-wrapper .btn-outline-primary,
    .cart-page-wrapper .btn-outline-danger {
        border-radius: 8px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .cart-page-wrapper .btn-outline-primary:hover,
    .cart-page-wrapper .btn-outline-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .cart-page-wrapper {
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
        
        .cart-item .row {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
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

        .cart-item {
            margin: 0.25rem;
            padding: 1rem !important;
        }
    }

    @media (max-width: 576px) {
        .page-title {
            font-size: 1.75rem;
        }

        .cart-page-wrapper {
            padding: 0.5rem 0;
        }

        .container {
            padding: 0 0.5rem;
        }
    }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
