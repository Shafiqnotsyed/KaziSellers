<?php
session_start();

// Simple admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../pages/login.php');
    exit();
}

include '../includes/db.php';

$message = '';

// Handle category addition
if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if (!empty($category_name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $category_name);
        if ($stmt->execute()) {
            $message = "Category added successfully!";
        } else {
            $message = "Error adding category.";
        }
    } else {
        $message = "Category name cannot be empty.";
    }
}

// Handle category deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    
    // Check if category has products
    $check_stmt = $conn->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ?");
    $check_stmt->bind_param("i", $category_id);
    $check_stmt->execute();
    $product_count = $check_stmt->get_result()->fetch_assoc()['product_count'];
    
    if ($product_count > 0) {
        $message = "Error: Cannot delete category with {$product_count} products. Please move or delete the products first.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        if ($stmt->execute()) {
            $message = "Category deleted successfully!";
            $message_type = "success";
        } else {
            $message = "Error deleting category.";
            $message_type = "error";
        }
    }
}

// Get all categories with product count
$categories = $conn->query("
    SELECT c.id, c.name, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id, c.name 
    ORDER BY c.name ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories - Admin Panel</title>
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
            <li><a href="admin.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin_categories.php" class="active"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="admin_messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="content-box">
            <h2><i class="fas fa-tags"></i> Manage Categories</h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo isset($message_type) ? $message_type : ''; ?>">
                    <i class="fas <?php echo isset($message_type) && $message_type === 'error' ? 'fa-exclamation-triangle' : 'fa-check-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="add-form">
                <h3><i class="fas fa-plus-circle"></i> Add New Category</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="category_name"><i class="fas fa-tag"></i> Category Name:</label>
                        <input type="text" id="category_name" name="category_name" placeholder="Enter category name..." required>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </form>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-tag"></i> Category Name</th>
                        <th><i class="fas fa-box"></i> Products</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories->num_rows > 0): ?>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="product-count <?php echo $category['product_count'] > 0 ? 'has-products' : ''; ?>">
                                        <?php echo $category['product_count']; ?> products
                                    </span>
                                </td>
                                <td>
                                    <?php if ($category['product_count'] > 0): ?>
                                        <span class="btn btn-disabled" title="Cannot delete category with products">
                                            <i class="fas fa-lock"></i> Locked
                                        </span>
                                    <?php else: ?>
                                        <a href="?delete=<?php echo $category['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('Are you sure you want to delete the \'<?php echo htmlspecialchars($category['name']); ?>\' category? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <p>No categories found. Add your first category above!</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
