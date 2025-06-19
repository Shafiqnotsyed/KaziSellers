<?php
session_start();

// Simple admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../pages/login.php');
    exit();
}

include '../includes/db.php';

$message = '';

// Handle product deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
    } else {
        $message = "Error deleting product.";
    }
}

// Get all products with seller info
$products = $conn->query("
    SELECT p.id, p.title, p.price, p.created_at, u.username as seller 
    FROM products p 
    JOIN users u ON p.seller_id = u.id 
    ORDER BY p.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products - Admin Panel</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Admin Styles -->
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .price {
            font-weight: 600;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-cogs"></i> Admin Panel</h1>
        <div>
            Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            <span class="admin-badge">ADMIN</span>
            <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <nav class="nav">
        <ul>
            <li><a href="admin.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin_products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="admin_messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="content-box">
            <h2><i class="fas fa-box"></i> Manage Products</h2>
            
            <?php if ($message): ?>
                <div class="message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="searchInput"><i class="fas fa-search"></i> Search Products:</label>
                <input type="text" id="searchInput" placeholder="Search products by title or seller..." onkeyup="searchProducts()">
            </div>
            
            <table id="productsTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-tag"></i> Product Title</th>
                        <th><i class="fas fa-dollar-sign"></i> Price</th>
                        <th><i class="fas fa-user"></i> Seller</th>
                        <th><i class="fas fa-calendar"></i> Date Added</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products->num_rows > 0): ?>
                        <?php while ($product = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($product['title']); ?></strong></td>
                                <td class="price">R<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($product['seller']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                                <td>
                                    <a href="?delete=<?php echo $product['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete \'<?php echo htmlspecialchars($product['title']); ?>\'? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <p>No products found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function searchProducts() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toLowerCase();
            var table = document.getElementById("productsTable");
            var rows = table.getElementsByTagName("tr");

            for (var i = 1; i < rows.length; i++) {
                var title = rows[i].getElementsByTagName("td")[1];
                var seller = rows[i].getElementsByTagName("td")[3];
                
                if (title && seller) {
                    var titleText = title.textContent || title.innerText;
                    var sellerText = seller.textContent || seller.innerText;
                    
                    if (titleText.toLowerCase().indexOf(filter) > -1 || 
                        sellerText.toLowerCase().indexOf(filter) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>
