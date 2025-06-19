<?php
session_start();
include("../includes/db.php");

$isInPages = true; // Add this line to fix navbar paths

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = "Browse Products - KaziSellers";
$cssPath = "../assets/css/styles.css";

// Get filters
$selectedCategory = isset($_GET['category']) ? intval($_GET['category']) : 0;
$searchTerm = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query
$query = "SELECT p.*, c.name as category_name, pi.image_path as image, u.username as seller_name
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
          LEFT JOIN users u ON p.seller_id = u.id
          WHERE p.availability = 'available'";

if ($selectedCategory > 0) {
    $query .= " AND p.category_id = $selectedCategory";
}

if (!empty($searchTerm)) {
    $query .= " AND (p.title LIKE '%$searchTerm%' OR p.description LIKE '%$searchTerm%')";
}

$query .= " ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Get categories
$categoryQuery = "SELECT * FROM categories WHERE is_active = 1 ORDER BY name";
$categories = mysqli_query($conn, $categoryQuery);

include("../components/header.php");
?>

<div class="container my-4">
    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2>Browse Products</h2>
                    
                    <form method="GET" class="row">
                        <div class="col-md-6 mb-3">
                            <label>Search Products</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search products..." 
                                   value="<?php echo htmlspecialchars($searchTerm); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Category</label>
                            <select class="form-control" name="category">
                                <option value="0">All Categories</option>
                                <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($selectedCategory == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Links -->
    <?php if ($selectedCategory == 0 && empty($searchTerm)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4>Shop by Category</h4>
                    <div class="row">
                        <?php 
                        mysqli_data_seek($categories, 0);
                        while ($category = mysqli_fetch_assoc($categories)): 
                        ?>
                        <div class="col-md-3 mb-3">
                            <a href="categories.php?category=<?php echo $category['id']; ?>" class="btn btn-outline-primary w-100">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Results -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4>
                    <?php 
                    $productCount = mysqli_num_rows($result);
                    echo $productCount . " Product" . ($productCount != 1 ? "s" : "") . " Found";
                    ?>
                </h4>
                <div>
                    <a href="sell.php" class="btn btn-success">Sell Something</a>
                    <?php if ($selectedCategory > 0 || !empty($searchTerm)): ?>
                    <a href="categories.php" class="btn btn-secondary">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Products -->
    <?php if (mysqli_num_rows($result) > 0): ?>
    <div class="row">
        <?php while ($product = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <?php 
                $imagePath = 'https://via.placeholder.com/300x200?text=No+Image';
                if (!empty($product['image'])) {
                    if (str_starts_with($product['image'], 'uploads/')) {
                        $imagePath = '../' . $product['image'];
                    } else {
                        $imagePath = '../uploads/' . $product['image'];
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
                    <p class="text-success fw-bold">R<?php echo number_format($product['price'], 2); ?></p>
                    <p class="text-muted small"><?php echo htmlspecialchars($product['category_name']); ?></p>
                    <small class="text-muted">by <?php echo htmlspecialchars($product['seller_name']); ?></small>
                    <br>
                    <a href="product-details.php?id=<?php echo $product['id']; ?>" class="btn btn-primary mt-2">View Details</a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <h4>No products found</h4>
                    <?php if (!empty($searchTerm) || $selectedCategory > 0): ?>
                    <p>Try adjusting your search terms or browse all categories</p>
                    <a href="categories.php" class="btn btn-primary">Browse All Products</a>
                    <?php else: ?>
                    <p>Be the first to list something for sale!</p>
                    <a href="sell.php" class="btn btn-primary">List Your First Item</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include("../components/footer.php"); ?>
