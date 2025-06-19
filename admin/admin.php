<?php
session_start();

// Simple admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

include '../includes/db.php';

// Get basic stats
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_messages = $conn->query("SELECT COUNT(*) as count FROM messages")->fetch_assoc()['count'];

// Get recent items
$recent_users = $conn->query("SELECT username, email, created_at FROM users ORDER BY created_at DESC LIMIT 3");
$recent_products = $conn->query("SELECT title, price, created_at FROM products ORDER BY created_at DESC LIMIT 3");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - KaziSellers</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Admin Styles -->
    <link rel="stylesheet" href="admin-style.css">
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
            <li><a href="admin.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="admin_messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="admin-welcome">
            <div class="welcome-title">Welcome to KaziSellers Admin</div>
            <div class="welcome-subtitle">Manage your platform with ease - Student project dashboard</div>
        </div>

        <div class="stats">
            <div class="stat-box">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-number"><?php echo $total_products; ?></div>
                <div class="stat-label">Total Products</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                <div class="stat-number"><?php echo $total_messages; ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
        </div>

        <div class="content-box">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="quick-actions">
                <a href="admin_users.php" class="action-btn">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
                <a href="admin_products.php" class="action-btn">
                    <i class="fas fa-box"></i>
                    <span>Manage Products</span>
                </a>
                <a href="admin_categories.php" class="action-btn">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
                <a href="admin_messages.php" class="action-btn">
                    <i class="fas fa-envelope"></i>
                    <span>View Messages</span>
                </a>
            </div>
        </div>

        <div class="grid-2">
            <div class="content-box">
                <h2>Recent Users</h2>
                <?php if ($recent_users->num_rows > 0): ?>
                    <ul class="recent-list">
                        <?php while ($user = $recent_users->fetch_assoc()): ?>
                            <li>
                                <div>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong><br>
                                    <small><?php echo htmlspecialchars($user['email']); ?></small>
                                </div>
                                <div><?php echo date('M j', strtotime($user['created_at'])); ?></div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No recent users</p>
                <?php endif; ?>
            </div>

            <div class="content-box">
                <h2>Recent Products</h2>
                <?php if ($recent_products->num_rows > 0): ?>
                    <ul class="recent-list">
                        <?php while ($product = $recent_products->fetch_assoc()): ?>
                            <li>
                                <div>
                                    <strong><?php echo htmlspecialchars($product['title']); ?></strong><br>
                                    <small>R<?php echo number_format($product['price'], 2); ?></small>
                                </div>
                                <div><?php echo date('M j', strtotime($product['created_at'])); ?></div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No recent products</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
