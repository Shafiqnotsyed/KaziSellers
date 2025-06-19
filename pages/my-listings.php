<?php
session_start();
include("../includes/db.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "My Listings - KaziSellers";
$cssPath = "../assets/css/styles.css";
$isInPages = true;

// Handle listing status updates
if ($_POST['action'] ?? '' === 'update_status' && isset($_POST['product_id']) && isset($_POST['status'])) {
    $product_id = (int)$_POST['product_id'];
    $status = $_POST['status'];
    $user_id = $_SESSION['user_id'];
    
    if (in_array($status, ['available', 'sold', 'reserved'])) {
        $updateQuery = "UPDATE products SET availability = ? WHERE id = ? AND seller_id = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "sii", $status, $product_id, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Listing status updated successfully!";
        } else {
            $error_message = "Failed to update listing status.";
        }
    }
}

// Get user's products with category info and image count
$query = "SELECT p.*, c.name as category_name, c.icon as category_icon,
          COUNT(pi.id) as image_count,
          (SELECT COUNT(*) FROM messages m WHERE m.product_id = p.id AND m.receiver_id = p.seller_id AND m.is_read = 0) as unread_messages
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN product_images pi ON p.id = pi.product_id
          WHERE p.seller_id = ? 
          GROUP BY p.id
          ORDER BY p.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get statistics
$statsQuery = "SELECT 
               COUNT(*) as total_listings,
               COUNT(CASE WHEN availability = 'available' THEN 1 END) as available_listings,
               COUNT(CASE WHEN availability = 'sold' THEN 1 END) as sold_listings,
               SUM(views) as total_views
               FROM products 
               WHERE seller_id = ?";
$statsStmt = mysqli_prepare($conn, $statsQuery);
mysqli_stmt_bind_param($statsStmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($statsStmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($statsStmt));

include("../components/header.php");
?>

<div class="container my-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="text-white">
                    <i class="fas fa-list-ul"></i> My Listings
                </h1>
                <a href="sell.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add New Listing
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-boxes fa-2x mb-2"></i>
                    <h4 class="mb-1"><?php echo $stats['total_listings']; ?></h4>
                    <small>Total Listings</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-store fa-2x mb-2"></i>
                    <h4 class="mb-1"><?php echo $stats['available_listings']; ?></h4>
                    <small>Available</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-handshake fa-2x mb-2"></i>
                    <h4 class="mb-1"><?php echo $stats['sold_listings']; ?></h4>
                    <small>Sold</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <i class="fas fa-eye fa-2x mb-2"></i>
                    <h4 class="mb-1"><?php echo number_format($stats['total_views']); ?></h4>
                    <small>Total Views</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Listings -->
    <?php if (mysqli_num_rows($result) > 0): ?>
    <div class="row">
        <?php while ($product = mysqli_fetch_assoc($result)): ?>
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="<?php echo $product['category_icon']; ?> text-primary me-2"></i>
                        <small class="text-muted"><?php echo htmlspecialchars($product['category_name']); ?></small>
                    </div>
                    <span class="badge bg-<?php 
                        echo $product['availability'] === 'available' ? 'success' : 
                            ($product['availability'] === 'sold' ? 'danger' : 'warning'); 
                    ?>">
                        <?php echo ucfirst($product['availability']); ?>
                    </span>
                </div>
                
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['title']); ?></h5>
                    <p class="card-text text-muted small mb-2">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                    </p>
                    
                    <div class="row align-items-center mb-3">
                        <div class="col">
                            <h4 class="text-primary mb-0">R<?php echo number_format($product['price'], 2); ?></h4>
                        </div>
                        <div class="col-auto">
                            <small class="text-muted">
                                <i class="fas fa-eye"></i> <?php echo $product['views']; ?> views
                            </small>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> 
                            <?php echo date('M j, Y', strtotime($product['created_at'])); ?>
                        </small>
                        <?php if ($product['unread_messages'] > 0): ?>
                        <span class="badge bg-danger">
                            <i class="fas fa-envelope"></i> <?php echo $product['unread_messages']; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer bg-white border-0">
                    <div class="btn-group w-100" role="group">
                        <a href="product-details.php?id=<?php echo $product['id']; ?>&preview=customer" 
                           class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="fas fa-eye"></i> Preview as Customer
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" 
                                onclick="editProduct(<?php echo $product['id']; ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" 
                                    data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i> Status
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" 
                                       onclick="updateStatus(<?php echo $product['id']; ?>, 'available')">
                                    <i class="fas fa-store text-success"></i> Available
                                </a></li>
                                <li><a class="dropdown-item" href="#" 
                                       onclick="updateStatus(<?php echo $product['id']; ?>, 'reserved')">
                                    <i class="fas fa-clock text-warning"></i> Reserved
                                </a></li>
                                <li><a class="dropdown-item" href="#" 
                                       onclick="updateStatus(<?php echo $product['id']; ?>, 'sold')">
                                    <i class="fas fa-handshake text-danger"></i> Sold
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">No listings yet</h4>
                    <p class="text-muted mb-4">Start selling by creating your first listing!</p>
                    <a href="sell.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Create Your First Listing
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Hidden form for status updates -->
<form id="statusForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="product_id" id="statusProductId">
    <input type="hidden" name="status" id="statusValue">
</form>

<script>
function updateStatus(productId, status) {
    if (confirm('Are you sure you want to update the status of this listing?')) {
        document.getElementById('statusProductId').value = productId;
        document.getElementById('statusValue').value = status;
        document.getElementById('statusForm').submit();
    }
}

function viewProduct(productId) {
    // Navigate to product details page in same directory
    window.location.href = 'product-details.php?id=' + productId;
}

function editProduct(productId) {
    // Navigate to edit page
    window.location.href = 'edit-product.php?id=' + productId;
}
</script>

<?php include("../components/footer.php"); ?>
