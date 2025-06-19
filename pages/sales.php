<?php
session_start();
include("../includes/db.php");

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "My Sales - KaziSellers";
$cssPath = "../assets/css/styles.css";
$isInPages = true;

// Get user's sold products and sales statistics
$query = "SELECT p.*, c.name as category_name, c.icon as category_icon,
          pi.image_path as image,
          u.username as buyer_name, u.email as buyer_email
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
          LEFT JOIN messages m ON p.id = m.product_id AND m.sender_id != p.seller_id
          LEFT JOIN users u ON m.sender_id = u.id
          WHERE p.seller_id = ? AND p.availability = 'sold'
          GROUP BY p.id
          ORDER BY p.updated_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$soldItems = mysqli_stmt_get_result($stmt);

// Get sales statistics
$statsQuery = "SELECT 
               COUNT(*) as total_sold,
               SUM(price) as total_earnings,
               AVG(price) as avg_sale_price,
               COUNT(CASE WHEN MONTH(updated_at) = MONTH(CURRENT_DATE()) 
                          AND YEAR(updated_at) = YEAR(CURRENT_DATE()) THEN 1 END) as this_month_sales
               FROM products 
               WHERE seller_id = ? AND availability = 'sold'";
$statsStmt = mysqli_prepare($conn, $statsQuery);
mysqli_stmt_bind_param($statsStmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($statsStmt);
$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($statsStmt));

// Get monthly sales data for chart
$monthlyQuery = "SELECT 
                 DATE_FORMAT(updated_at, '%Y-%m') as month,
                 COUNT(*) as sales_count,
                 SUM(price) as earnings
                 FROM products 
                 WHERE seller_id = ? AND availability = 'sold'
                 AND updated_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                 GROUP BY DATE_FORMAT(updated_at, '%Y-%m')
                 ORDER BY month DESC";
$monthlyStmt = mysqli_prepare($conn, $monthlyQuery);
mysqli_stmt_bind_param($monthlyStmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($monthlyStmt);
$monthlyData = mysqli_stmt_get_result($monthlyStmt);

include("../components/header.php");
?>

<div class="container my-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="text-white">
                    <i class="fas fa-chart-line"></i> My Sales Dashboard
                </h1>
                <div>
                    <a href="my-listings.php" class="btn btn-secondary me-2">
                        <i class="fas fa-list"></i> View All Listings
                    </a>
                    <a href="sell.php" class="btn btn-success">
                        <i class="fas fa-plus"></i> List New Item
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-handshake fa-2x mb-2"></i>
                    <h3 class="mb-1"><?php echo $stats['total_sold'] ?: 0; ?></h3>
                    <small>Items Sold</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-coins fa-2x mb-2"></i>
                    <h3 class="mb-1">R<?php echo number_format($stats['total_earnings'] ?: 0, 2); ?></h3>
                    <small>Total Earnings</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calculator fa-2x mb-2"></i>
                    <h3 class="mb-1">R<?php echo number_format($stats['avg_sale_price'] ?: 0, 2); ?></h3>
                    <small>Average Sale</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm bg-warning text-white h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-month fa-2x mb-2"></i>
                    <h3 class="mb-1"><?php echo $stats['this_month_sales'] ?: 0; ?></h3>
                    <small>This Month</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Performance Chart -->
    <?php if (mysqli_num_rows($monthlyData) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-primary"></i> Sales Performance (Last 6 Months)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-calendar"></i> Month</th>
                                    <th><i class="fas fa-shopping-bag"></i> Items Sold</th>
                                    <th><i class="fas fa-money-bill-wave"></i> Earnings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                mysqli_data_seek($monthlyData, 0);
                                while ($month = mysqli_fetch_assoc($monthlyData)): 
                                ?>
                                <tr>
                                    <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $month['sales_count']; ?></span></td>
                                    <td><strong class="text-success">R<?php echo number_format($month['earnings'], 2); ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sold Items -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="text-white mb-3">
                <i class="fas fa-check-circle"></i> Recently Sold Items
            </h3>
        </div>
    </div>

    <?php if (mysqli_num_rows($soldItems) > 0): ?>
    <div class="row">
        <?php mysqli_data_seek($soldItems, 0); ?>
        <?php while ($item = mysqli_fetch_assoc($soldItems)): ?>
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="<?php echo $item['category_icon']; ?> me-2"></i>
                        <small><?php echo htmlspecialchars($item['category_name']); ?></small>
                    </div>
                    <span class="badge bg-light text-success">
                        <i class="fas fa-check"></i> SOLD
                    </span>
                </div>
                
                <?php if ($item['image']): ?>
                <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                     class="card-img-top" style="height: 200px; object-fit: cover;"
                     alt="<?php echo htmlspecialchars($item['title']); ?>">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                     style="height: 200px;">
                    <i class="<?php echo $item['category_icon']; ?> fa-3x text-muted"></i>
                </div>
                <?php endif; ?>
                
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                    <p class="card-text text-muted small mb-2">
                        <?php echo htmlspecialchars(substr($item['description'], 0, 80)) . '...'; ?>
                    </p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="text-success mb-0">
                            <i class="fas fa-coins"></i> R<?php echo number_format($item['price'], 2); ?>
                        </h4>
                        <small class="text-muted">
                            <i class="fas fa-eye"></i> <?php echo $item['views']; ?> views
                        </small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-calendar-check"></i> 
                            Sold: <?php echo date('M j, Y', strtotime($item['updated_at'])); ?>
                        </small>
                        <?php if ($item['buyer_name']): ?>
                        <small class="text-primary">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($item['buyer_name']); ?>
                        </small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer bg-white border-0">
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-outline-primary btn-sm" 
                                onclick="viewSaleDetails(<?php echo $item['id']; ?>)">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        <button class="btn btn-outline-success btn-sm" 
                                onclick="leaveFeedback(<?php echo $item['id']; ?>)">
                            <i class="fas fa-star"></i> Rate Buyer
                        </button>
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
                    <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">No sales yet</h4>
                    <p class="text-muted mb-4">Start selling your items to see your sales dashboard!</p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="sell.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> List an Item
                        </a>
                        <a href="my-listings.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i> View My Listings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sales Tips -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h5 class="card-title">
                        <i class="fas fa-lightbulb"></i> Tips to Increase Your Sales
                    </h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-camera fa-2x me-3 mt-1"></i>
                                <div>
                                    <h6>Great Photos</h6>
                                    <small>Use natural lighting and show multiple angles of your items</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-tag fa-2x me-3 mt-1"></i>
                                <div>
                                    <h6>Competitive Pricing</h6>
                                    <small>Research similar items and price fairly to attract buyers</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-clock fa-2x me-3 mt-1"></i>
                                <div>
                                    <h6>Quick Response</h6>
                                    <small>Reply to messages promptly to keep buyers interested</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewSaleDetails(itemId) {
    // Navigate to detailed view
    window.open('../categories.php?product=' + itemId, '_blank');
}

function leaveFeedback(itemId) {
    // Future feature for rating system
    alert('Rating system coming soon! This will help build trust in the community.');
}
</script>

<?php include("../components/footer.php"); ?>
